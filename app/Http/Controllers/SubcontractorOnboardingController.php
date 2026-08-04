<?php

namespace App\Http\Controllers;

use App\Models\StaffMember;
use App\Models\SubcontractorOnboarding;
use App\Services\StaffPortal\StaffIdentityService;
use App\Services\SubcontractorDocumentService;
use App\Services\SystemNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SubcontractorOnboardingController extends Controller
{
    public function create(): View
    {
        return view('subcontractor-onboardings.create');
    }

    public function store(Request $request, StaffIdentityService $identity, SubcontractorDocumentService $documents): RedirectResponse
    {
        $data = $this->validatedSubmission($request);
        $identity->assertAvailable($data['email'] ?? null, $data['mobile'] ?? null);
        $data['status'] = 'Pending Review';
        $data['submitted_at'] = now();
        $data['full_name'] = trim($data['first_name'].' '.$data['last_name']);
        $data['contact_person'] = ($data['contact_person'] ?? null) ?: $data['full_name'];
        $data['phone'] = $data['mobile'];
        $data['normalized_email'] = $identity->normalizeEmail($data['email'] ?? null);
        $data['normalized_mobile'] = $identity->normalizeMobile($data['mobile'] ?? null);
        $data = $this->withOnboardingDefaults($data);

        foreach (array_keys(SubcontractorOnboarding::DOCUMENT_FIELDS) as $field) {
            unset($data[$field]);
        }

        $data['uploaded_certificates'] = collect($request->file('uploaded_certificates', []))
            ->map(fn ($file) => [
                'path' => $file->store('subcontractor-documents/certificates', 'public'),
                'name' => $file->getClientOriginalName(),
            ])
            ->values()
            ->all();

        $onboarding = DB::transaction(function () use ($data, $identity): SubcontractorOnboarding {
            $onboarding = SubcontractorOnboarding::create($data);
            $identity->createLock($onboarding, $onboarding->email, $onboarding->mobile ?: $onboarding->phone);

            return $onboarding;
        });

        foreach (array_keys(SubcontractorOnboarding::DOCUMENT_FIELDS) as $field) {
            if ($request->hasFile($field)) {
                $version = $documents->addForOnboarding($onboarding, $field, $request->file($field));
                if (array_key_exists($field, SubcontractorOnboarding::MANDATORY_DOCUMENTS)) {
                    $onboarding->forceFill([$field => $version->storage_path])->save();
                }
            }
        }

        $this->notifyAdminOfSubmission($onboarding);

        return redirect()->route('login')->with('onboarding_success', true);
    }

    public function index(): View
    {
        return view('subcontractor-onboardings.index', [
            'onboardings' => SubcontractorOnboarding::latest()->paginate(15),
        ]);
    }

    public function show(SubcontractorOnboarding $subcontractorOnboarding): View
    {
        return view('subcontractor-onboardings.show', [
            'onboarding' => $subcontractorOnboarding->load('documents.versions.reviewer', 'documents.currentVersion'),
            'missingDocuments' => $subcontractorOnboarding->missingMandatoryDocuments(),
        ]);
    }

    public function edit(SubcontractorOnboarding $subcontractorOnboarding): View
    {
        return view('subcontractor-onboardings.edit', [
            'onboarding' => $subcontractorOnboarding,
        ]);
    }

    public function update(Request $request, SubcontractorOnboarding $subcontractorOnboarding, SubcontractorDocumentService $documents): RedirectResponse
    {
        $data = $this->validatedAdminUpdate($request);
        $data['gst_registered'] = (bool) $data['gst_registered'];
        $data['full_name'] = trim($data['first_name'].' '.$data['last_name']);
        $data['contact_person'] = ($data['contact_person'] ?? null) ?: $data['full_name'];
        $data['phone'] = $data['mobile'];
        $data = $this->withOnboardingDefaults($data);

        foreach (array_keys(SubcontractorOnboarding::DOCUMENT_FIELDS) as $field) {
            if ($request->hasFile($field)) {
                $version = $documents->addForOnboarding($subcontractorOnboarding, $field, $request->file($field), Auth::id(), $request->input('replacement_reason'));
                if (array_key_exists($field, SubcontractorOnboarding::MANDATORY_DOCUMENTS)) {
                    $data[$field] = $version->storage_path;
                }
            }
            if (! array_key_exists($field, SubcontractorOnboarding::MANDATORY_DOCUMENTS) || ! $request->hasFile($field)) {
                unset($data[$field]);
            }
        }

        if ($request->hasFile('uploaded_certificates')) {
            foreach ($subcontractorOnboarding->uploaded_certificates ?? [] as $certificate) {
                $path = Arr::get($certificate, 'path');

                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            }

            $data['uploaded_certificates'] = collect($request->file('uploaded_certificates', []))
                ->map(fn ($file) => [
                    'path' => $file->store('subcontractor-documents/certificates', 'public'),
                    'name' => $file->getClientOriginalName(),
                ])
                ->values()
                ->all();
        } else {
            unset($data['uploaded_certificates']);
        }

        $subcontractorOnboarding->update($data);

        return redirect()
            ->route('subcontractor-onboardings.show', $subcontractorOnboarding)
            ->with('status', 'Subcontractor onboarding details updated.');
    }

    public function approve(
        SubcontractorOnboarding $subcontractorOnboarding,
        StaffIdentityService $identity
    ): RedirectResponse {
        $missing = $this->approvalErrors($subcontractorOnboarding);

        if ($missing !== []) {
            $subcontractorOnboarding->forceFill(['status' => 'Documents Required'])->save();
            $subcontractorOnboarding->appendSyncLog('failed', 'Approval blocked: '.implode(', ', $missing));

            return back()->withErrors(['approval' => 'Approval blocked: '.implode(', ', $missing)]);
        }

        try {
            DB::transaction(function () use ($subcontractorOnboarding, $identity): void {
                $subcontractorOnboarding->forceFill([
                    'normalized_email' => $identity->normalizeEmail($subcontractorOnboarding->email),
                    'normalized_mobile' => $identity->normalizeMobile($subcontractorOnboarding->mobile ?: $subcontractorOnboarding->phone),
                    'status' => 'Approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                    'portal_user_id' => null,
                ])->save();

                $staffMember = StaffMember::upsertFromOnboarding($subcontractorOnboarding->fresh());
                $subcontractorOnboarding->documents()->update(['staff_member_id' => $staffMember->id]);
                $identity->transferLock($subcontractorOnboarding, $staffMember, $staffMember->email, $staffMember->mobile);

                $subcontractorOnboarding->forceFill([
                    'status' => 'Active',
                    'staff_member_id' => $staffMember->id,
                ])->save();

                $subcontractorOnboarding->appendSyncLog('success', 'Approved onboarding and created the Hydrox subcontractor profile.');
            });
        } catch (Throwable $exception) {
            report($exception);

            $subcontractorOnboarding->forceFill(['status' => 'Pending Review'])->save();
            $subcontractorOnboarding->appendSyncLog('failed', $exception->getMessage());

            return back()->withErrors(['approval' => $exception->getMessage()]);
        }

        app(SystemNotificationService::class)->markSubjectRead($subcontractorOnboarding);

        return back()->with('status', 'Subcontractor approved and added to the Hydrox portal.');
    }

    public function reject(Request $request, SubcontractorOnboarding $subcontractorOnboarding): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string'],
        ]);

        $subcontractorOnboarding->forceFill([
            'status' => 'Rejected',
            'rejected_at' => now(),
            'rejected_by' => Auth::id(),
            'rejection_reason' => $data['rejection_reason'],
        ])->save();

        $subcontractorOnboarding->appendSyncLog('rejected', $data['rejection_reason']);
        app(SystemNotificationService::class)->markSubjectRead($subcontractorOnboarding);

        return back()->with('status', 'Subcontractor onboarding rejected.');
    }

    public function document(SubcontractorOnboarding $subcontractorOnboarding, string $field): BinaryFileResponse
    {
        [$disk, $path] = $this->documentLocation($subcontractorOnboarding, $field);
        abort_unless($path && Storage::disk($disk)->exists($path), 404);

        return response()->file(Storage::disk($disk)->path($path));
    }

    public function downloadDocument(SubcontractorOnboarding $subcontractorOnboarding, string $field): StreamedResponse
    {
        [$disk, $path, $name] = $this->documentLocation($subcontractorOnboarding, $field);
        abort_unless($path && Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->download($path, $name);
    }

    public function deleteDocument(SubcontractorOnboarding $subcontractorOnboarding, string $field): RedirectResponse
    {
        abort_unless(array_key_exists($field, $subcontractorOnboarding->documentFields()), 404);

        $document = $subcontractorOnboarding->documents()->where('category', $field)->with('currentVersion')->first();
        if ($document?->currentVersion) {
            $document->currentVersion->update(['is_current' => false, 'archived_at' => now()]);
        }

        $subcontractorOnboarding->forceFill([
            $field => null,
            'status' => $subcontractorOnboarding->status === 'Active' ? $subcontractorOnboarding->status : 'Documents Required',
        ])->save();

        $subcontractorOnboarding->appendSyncLog('document_deleted', $subcontractorOnboarding->documentFields()[$field].' was deleted by an administrator.');

        return back()->with('status', 'Document archived and retained in history.');
    }

    private function validatedSubmission(Request $request): array
    {
        $data = $request->validate([
            'legal_business_name' => ['nullable', 'string', 'max:255'],
            'trading_name' => ['nullable', 'string', 'max:255'],
            'abn' => ['required', 'string', 'max:32', 'regex:/^[0-9]+$/'],
            'gst_registered' => ['nullable', 'boolean'],
            'business_structure' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'business_address' => ['required', 'string', 'max:255'],
            'residency_status' => ['required', Rule::in(SubcontractorOnboarding::RESIDENCY_STATUSES)],
            'visa_type' => ['nullable', 'required_if:residency_status,Other Visa Holder', 'string', 'max:255'],
            'visa_expiry_date' => ['nullable', 'required_if:residency_status,Other Visa Holder', 'date'],
            'service_types' => ['nullable', 'array'],
            'service_types.*' => ['nullable', 'string', 'max:120'],
            'preferred_work_areas' => ['nullable', 'array'],
            'preferred_work_areas.*' => ['nullable', 'string', 'max:120'],
            'available_days' => ['nullable', 'array'],
            'available_days.*' => ['string', 'max:32'],
            'available_hours' => ['required', 'string', 'max:2000'],
            'crew_members' => ['nullable', 'integer', 'min:1', 'max:50'],
            'supervisor' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:120'],
            'bank_details' => ['required', 'string'],
            'superannuation' => ['nullable', 'string'],
            'insurance_expiry' => ['nullable', 'date'],
            'experience' => ['nullable', 'string'],
            'cover_letter_text' => ['nullable', 'string', 'max:20000'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'in:'.implode(',', SubcontractorOnboarding::skillOptions())],
            'notes' => ['nullable', 'string'],
            'public_liability_insurance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'workers_compensation_insurance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'police_clearance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'driver_licence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'working_rights' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'uploaded_certificates.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'australian_passport' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'birth_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'citizenship_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'passport' => ['nullable', Rule::requiredIf(in_array($request->input('residency_status'), ['Permanent Resident', 'Other Visa Holder'], true)), 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'permanent_residency_evidence' => ['nullable', 'required_if:residency_status,Permanent Resident', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'visa_evidence' => ['nullable', 'required_if:residency_status,Other Visa Holder', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'cover_letter_attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        if ($request->input('residency_status') === 'Australian Citizen' && ! collect(['australian_passport', 'birth_certificate', 'citizenship_certificate'])->contains(fn (string $field): bool => $request->hasFile($field))) {
            throw ValidationException::withMessages(['citizenship_evidence' => 'Upload an Australian passport, birth certificate, or citizenship certificate.']);
        }

        return $data;
    }

    private function notifyAdminOfSubmission(SubcontractorOnboarding $onboarding): void
    {
        app(SystemNotificationService::class)->notify(
            'subcontractor_onboarding_submitted',
            'New subcontractor onboarding application',
            "A new subcontractor onboarding application has been submitted.\n\nName: {$onboarding->full_name}\nEmail: {$onboarding->email}\nMobile: {$onboarding->mobile}\n\nPlease review it in the Hydrox Portal.",
            route('subcontractor-onboardings.show', $onboarding),
            $onboarding
        );
    }

    private function validatedAdminUpdate(Request $request): array
    {
        return $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', SubcontractorOnboarding::STATUSES)],
            'legal_business_name' => ['nullable', 'string', 'max:255'],
            'trading_name' => ['nullable', 'string', 'max:255'],
            'abn' => ['required', 'string', 'max:32', 'regex:/^[0-9]+$/'],
            'gst_registered' => ['nullable', 'boolean'],
            'business_structure' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'business_address' => ['required', 'string', 'max:255'],
            'residency_status' => ['nullable', Rule::in(SubcontractorOnboarding::RESIDENCY_STATUSES)],
            'visa_type' => ['nullable', 'string', 'max:255'],
            'visa_expiry_date' => ['nullable', 'date'],
            'service_types' => ['nullable', 'array'],
            'service_types.*' => ['nullable', 'string', 'max:120'],
            'preferred_work_areas' => ['nullable', 'array'],
            'preferred_work_areas.*' => ['nullable', 'string', 'max:120'],
            'available_days' => ['nullable', 'array'],
            'available_days.*' => ['string', 'max:32'],
            'available_hours' => ['required', 'string', 'max:2000'],
            'crew_members' => ['nullable', 'integer', 'min:1', 'max:50'],
            'supervisor' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:120'],
            'bank_details' => ['required', 'string'],
            'superannuation' => ['nullable', 'string'],
            'insurance_expiry' => ['nullable', 'date'],
            'experience' => ['nullable', 'string'],
            'cover_letter_text' => ['nullable', 'string', 'max:20000'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'in:'.implode(',', SubcontractorOnboarding::skillOptions())],
            'notes' => ['nullable', 'string'],
            'public_liability_insurance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'workers_compensation_insurance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'police_clearance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'driver_licence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'working_rights' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'australian_passport' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'birth_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'citizenship_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'passport' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'permanent_residency_evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'visa_evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'cover_letter_attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'replacement_reason' => ['nullable', 'string', 'max:1000'],
            'uploaded_certificates.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);
    }

    private function withOnboardingDefaults(array $data): array
    {
        $data['legal_business_name'] = $data['legal_business_name'] ?? null;
        $data['service_types'] = array_values(array_filter(Arr::wrap($data['service_types'] ?? [])));
        $data['preferred_work_areas'] = array_values(array_filter(Arr::wrap($data['preferred_work_areas'] ?? [])));
        $data['skills'] = array_values(array_filter(Arr::wrap($data['skills'] ?? [])));
        $data['available_days'] = array_values(array_filter(Arr::wrap($data['available_days'] ?? [])));
        $data['crew_members'] = $data['crew_members'] ?? null;
        $data['supervisor'] = $data['supervisor'] ?? null;
        $data['payment_method'] = $data['payment_method'] ?? null;

        return $data;
    }

    private function approvalErrors(SubcontractorOnboarding $onboarding): array
    {
        $required = [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'residency_status' => 'Citizenship or residency status',
        ];

        if ($onboarding->residency_status === 'Other Visa Holder') {
            $required += ['visa_type' => 'Visa type', 'visa_expiry_date' => 'Visa expiry date'];
        }

        $missing = collect($required)
            ->filter(fn (string $label, string $field): bool => blank($onboarding->{$field}))
            ->values()
            ->all();

        return array_merge($missing, array_values($onboarding->missingMandatoryDocuments()));
    }

    private function documentLocation(SubcontractorOnboarding $onboarding, string $field): array
    {
        if ($field === 'uploaded_certificates') {
            $path = Arr::get($onboarding->uploaded_certificates, '0.path');

            return ['public', $path, basename((string) $path)];
        }

        abort_unless(array_key_exists($field, $onboarding->documentFields()), 404);

        $version = $onboarding->documents()->where('category', $field)->with('currentVersion')->first()?->currentVersion;
        if ($version) {
            return [$version->storage_disk, $version->storage_path, $version->original_filename];
        }

        $path = $onboarding->{$field};

        return ['public', $path, basename((string) $path)];
    }
}
