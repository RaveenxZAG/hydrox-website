<?php

namespace App\Http\Controllers;

use App\Models\StaffMember;
use App\Models\SubcontractorOnboarding;
use App\Services\StaffPortal\StaffIdentityService;
use App\Services\SubcontractorDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffMemberController extends Controller
{
    public function index(Request $request): View
    {
        $roleNames = $this->loadRoleNames();
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');

        $staffMembers = StaffMember::query()
            ->with('onboarding')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%");
            }))
            ->when($status, fn ($query) => $query->where('staff_status', $status))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('staff-members.index', [
            'staffMembers' => $staffMembers,
            'roleNames' => $roleNames,
            'filters' => compact('search', 'status'),
        ]);
    }

    public function show(StaffMember $staffMember): View
    {
        return view('staff-members.show', [
            'staffMember' => $staffMember->load('onboarding.documents.versions.reviewer', 'documents.versions.reviewer', 'documents.currentVersion'),
            'roleNames' => $this->loadRoleNames(),
        ]);
    }

    public function edit(StaffMember $staffMember): View
    {
        return view('staff-members.edit', [
            'staffMember' => $staffMember->load('onboarding'),
            'roleNames' => $this->loadRoleNames(),
        ]);
    }

    public function update(Request $request, StaffMember $staffMember, StaffIdentityService $identity, SubcontractorDocumentService $documents): RedirectResponse
    {
        $request->merge([
            'abn' => filled($request->input('abn'))
                ? preg_replace('/[^0-9]/', '', (string) $request->input('abn'))
                : null,
            'labour_rate' => filled($request->input('labour_rate')) ? $request->input('labour_rate') : null,
            'insurance_expiry' => filled($request->input('insurance_expiry')) ? $request->input('insurance_expiry') : null,
            'staff_status' => $request->input('staff_status') ?: ($staffMember->staff_status ?: 'active'),
        ]);

        $data = $request->validate([
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'security_role' => ['nullable', 'string', 'max:255'],
            'schedule_colour' => ['nullable', 'string', 'max:32'],
            'labour_rate' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'active' => ['nullable', 'boolean'],
            'staff_status' => ['required', 'string', 'in:active,inactive,archived'],
            'portal_access_enabled' => ['nullable', 'boolean'],
            'invoicing_enabled' => ['nullable', 'boolean'],
            'address' => ['nullable', 'string', 'max:255'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'legal_business_name' => ['nullable', 'string', 'max:255'],
            'trading_name' => ['nullable', 'string', 'max:255'],
            'abn' => ['nullable', 'string', 'max:50', 'regex:/^[0-9]+$/'],
            'gst_registered' => ['nullable', 'boolean'],
            'business_structure' => ['nullable', 'string', 'max:120'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'availability' => ['nullable', 'string'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'in:'.implode(',', SubcontractorOnboarding::skillOptions())],
            'experience' => ['nullable', 'string', 'max:5000'],
            'bank_details' => ['nullable', 'string'],
            'superannuation' => ['nullable', 'string'],
            'insurance_expiry' => ['nullable', 'date'],
            'public_liability_insurance' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'workers_compensation_insurance' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'police_clearance' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'driver_licence' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'working_rights' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'australian_passport' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'birth_certificate' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'citizenship_certificate' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'passport' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'permanent_residency_evidence' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'visa_evidence' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'resume' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx'],
            'cover_letter_attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx'],
            'replacement_reason' => ['nullable', 'string', 'max:1000'],
            'show_on_schedule' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        foreach ($staffMember->documentFields() as $field => $label) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $version = $documents->addForStaff($staffMember, $field, $file, auth()->id(), $request->input('replacement_reason'));
                if (in_array($field, ['public_liability_insurance', 'workers_compensation_insurance', 'police_clearance', 'driver_licence', 'working_rights'], true)) {
                    $data[$field] = $version->storage_path;
                    $data[$field.'_name'] = $version->original_filename;
                } else {
                    unset($data[$field]);
                }
            }
        }
        unset($data['replacement_reason']);

        $normalizedEmail = $identity->normalizeEmail($data['email'] ?? null);
        $normalizedMobile = $identity->normalizeMobile($data['mobile'] ?? null);
        $emailChanged = $normalizedEmail !== $identity->normalizeEmail($staffMember->email);
        $mobileChanged = $normalizedMobile !== $identity->normalizeMobile($staffMember->mobile);

        $identity->assertAvailable(
            $emailChanged ? ($data['email'] ?? null) : null,
            $mobileChanged ? ($data['mobile'] ?? null) : null,
            StaffMember::class,
            $staffMember->id
        );

        $data['normalized_email'] = $normalizedEmail;
        $data['normalized_mobile'] = $normalizedMobile;
        $data['active'] = (bool) ($data['active'] ?? false);
        $data['gst_registered'] = (bool) ($data['gst_registered'] ?? false);
        $data['portal_access_enabled'] = (bool) ($data['portal_access_enabled'] ?? false);
        $data['invoicing_enabled'] = (bool) ($data['invoicing_enabled'] ?? false);
        $data['skills'] = array_values(array_filter(Arr::wrap($data['skills'] ?? [])));
        $data['show_on_schedule'] = (bool) ($data['show_on_schedule'] ?? false);
        $data['archived_at'] = $data['staff_status'] === 'archived' ? ($staffMember->archived_at ?: now()) : null;

        $staffMember->update($data);
        $identity->updateLock($staffMember, $staffMember->email, $staffMember->mobile);

        return redirect()->route('staff-members.show', $staffMember)->with('status', 'Subcontractor updated.');
    }

    public function document(StaffMember $staffMember, string $field): BinaryFileResponse
    {
        [$disk, $path] = $this->documentLocation($staffMember, $field);
        abort_unless($path && Storage::disk($disk)->exists($path), 404);

        return response()->file(Storage::disk($disk)->path($path));
    }

    public function downloadDocument(StaffMember $staffMember, string $field): StreamedResponse
    {
        [$disk, $path, $name] = $this->documentLocation($staffMember, $field);
        abort_unless($path && Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->download($path, $name);
    }

    public function deleteDocument(StaffMember $staffMember, string $field): RedirectResponse
    {
        $document = $staffMember->currentDocumentsByCategory()->get($field);
        abort_unless($document?->currentVersion, 404);
        $document->currentVersion->update(['is_current' => false, 'archived_at' => now()]);

        if (in_array($field, ['public_liability_insurance', 'workers_compensation_insurance', 'police_clearance', 'driver_licence', 'working_rights'], true)) {
            $staffMember->forceFill([$field => null, $field.'_name' => null])->save();
        }

        return back()->with('status', $staffMember->documentFields()[$field].' archived and retained in history.');
    }

    public function destroy(StaffMember $staffMember): RedirectResponse
    {
        $this->purgeLocalStaffData($staffMember);
        $staffMember->delete();

        return redirect()->route('staff-members.index')->with('status', 'Subcontractor removed.');
    }

    private function purgeLocalStaffData(StaffMember $staffMember): void
    {
        $staffMember->loadMissing(['invoices', 'profileChangeRequests']);

        foreach ($staffMember->documentFields() as $field => $label) {
            $path = $staffMember->{$field};

            if ($path && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        }

        foreach ($staffMember->invoices as $invoice) {
            if ($invoice->storage_path && Storage::disk('local')->exists($invoice->storage_path)) {
                Storage::disk('local')->delete($invoice->storage_path);
            }
        }

        foreach ($staffMember->profileChangeRequests as $profileChange) {
            foreach ($staffMember->documentFields() as $field => $label) {
                $file = ($profileChange->changes ?? [])[$field] ?? null;

                if (is_array($file) && filled($file['path'] ?? null) && Storage::disk('local')->exists($file['path'])) {
                    Storage::disk('local')->delete($file['path']);
                }
            }
        }

        Storage::disk('local')->deleteDirectory('staff-documents/'.$staffMember->id);
        Storage::disk('local')->deleteDirectory('staff-invoices/'.$staffMember->id);
        Storage::disk('local')->deleteDirectory('staff-profile-change-documents/'.$staffMember->id);

        DB::table('staff_identity_locks')
            ->where('owner_type', StaffMember::class)
            ->where('owner_id', $staffMember->id)
            ->delete();
    }

    private function loadRoleNames(): array
    {
        return StaffMember::query()
            ->whereNotNull('security_role')
            ->where('security_role', '!=', '')
            ->pluck('security_role', 'security_role')
            ->all();
    }

    private function documentLocation(StaffMember $staffMember, string $field): array
    {
        abort_unless(array_key_exists($field, $staffMember->documentFields()), 404);

        $version = $staffMember->currentDocumentsByCategory()->get($field)?->currentVersion;
        if ($version) {
            return [$version->storage_disk, $version->storage_path, $version->original_filename];
        }

        $path = $staffMember->{$field};

        return ['local', $path, $staffMember->documentName($field) ?: basename((string) $path)];
    }
}
