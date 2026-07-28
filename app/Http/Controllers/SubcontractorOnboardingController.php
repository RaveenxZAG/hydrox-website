<?php

namespace App\Http\Controllers;

use App\Models\SubcontractorOnboarding;
use App\Models\StaffMember;
use App\Services\StaffPortal\StaffIdentityService;
use App\Services\SystemNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubcontractorOnboardingController extends Controller
{
    public function create(): View
    {
        return view('subcontractor-onboardings.create');
    }

    public function store(Request $request, StaffIdentityService $identity): RedirectResponse
    {
        $data = $this->validatedSubmission($request);
        $identity->assertAvailable($data['email'] ?? null, $data['mobile'] ?? null);
        $data['status'] = 'Pending Review';
        $data['submitted_at'] = now();
        $data['full_name'] = trim($data['first_name'].' '.$data['last_name']);
        $data['contact_person'] = $data['contact_person'] ?: $data['full_name'];
        $data['phone'] = $data['mobile'];
        $data['normalized_email'] = $identity->normalizeEmail($data['email'] ?? null);
        $data['normalized_mobile'] = $identity->normalizeMobile($data['mobile'] ?? null);
        $data = $this->withOnboardingDefaults($data);

        foreach (array_keys(SubcontractorOnboarding::MANDATORY_DOCUMENTS) as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('subcontractor-documents', 'public');
            }
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
            'onboarding' => $subcontractorOnboarding,
            'missingDocuments' => $subcontractorOnboarding->missingMandatoryDocuments(),
        ]);
    }

    public function edit(SubcontractorOnboarding $subcontractorOnboarding): View
    {
        return view('subcontractor-onboardings.edit', [
            'onboarding' => $subcontractorOnboarding,
        ]);
    }

    public function update(Request $request, SubcontractorOnboarding $subcontractorOnboarding): RedirectResponse
    {
        $data = $this->validatedAdminUpdate($request);
        $data['gst_registered'] = (bool) $data['gst_registered'];
        $data['full_name'] = trim($data['first_name'].' '.$data['last_name']);
        $data['contact_person'] = $data['contact_person'] ?: $data['full_name'];
        $data['phone'] = $data['mobile'];
        $data = $this->withOnboardingDefaults($data);

        foreach (array_keys(SubcontractorOnboarding::MANDATORY_DOCUMENTS) as $field) {
            if ($request->hasFile($field)) {
                if ($subcontractorOnboarding->{$field}) {
                    Storage::disk('public')->delete($subcontractorOnboarding->{$field});
                }

                $data[$field] = $request->file($field)->store('subcontractor-documents', 'public');
            } else {
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
    ): RedirectResponse
    {
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
        $path = $this->documentPath($subcontractorOnboarding, $field);
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return response()->file(Storage::disk('public')->path($path));
    }

    public function downloadDocument(SubcontractorOnboarding $subcontractorOnboarding, string $field): BinaryFileResponse
    {
        $path = $this->documentPath($subcontractorOnboarding, $field);
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path);
    }

    public function deleteDocument(SubcontractorOnboarding $subcontractorOnboarding, string $field): RedirectResponse
    {
        abort_unless(array_key_exists($field, $subcontractorOnboarding->documentFields()), 404);

        $path = $subcontractorOnboarding->{$field};

        if ($path) {
            Storage::disk('public')->delete($path);
        }

        $subcontractorOnboarding->forceFill([
            $field => null,
            'status' => $subcontractorOnboarding->status === 'Active' ? $subcontractorOnboarding->status : 'Documents Required',
        ])->save();

        $subcontractorOnboarding->appendSyncLog('document_deleted', $subcontractorOnboarding->documentFields()[$field].' was deleted by an administrator.');

        return back()->with('status', 'Document deleted.');
    }

    private function validatedSubmission(Request $request): array
    {
        return $request->validate([
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
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'in:'.implode(',', SubcontractorOnboarding::skillOptions())],
            'notes' => ['nullable', 'string'],
            'public_liability_insurance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'workers_compensation_insurance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'police_clearance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'driver_licence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'working_rights' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'uploaded_certificates.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);
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
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'in:'.implode(',', SubcontractorOnboarding::skillOptions())],
            'notes' => ['nullable', 'string'],
            'public_liability_insurance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'workers_compensation_insurance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'police_clearance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'driver_licence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'working_rights' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
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
        ];

        $missing = collect($required)
            ->filter(fn (string $label, string $field): bool => blank($onboarding->{$field}))
            ->values()
            ->all();

        return array_merge($missing, array_values($onboarding->missingMandatoryDocuments()));
    }

    private function documentPath(SubcontractorOnboarding $onboarding, string $field): ?string
    {
        if ($field === 'uploaded_certificates') {
            return Arr::get($onboarding->uploaded_certificates, '0.path');
        }

        abort_unless(array_key_exists($field, $onboarding->documentFields()), 404);

        return $onboarding->{$field};
    }
}
