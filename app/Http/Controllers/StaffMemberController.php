<?php

namespace App\Http\Controllers;

use App\Models\StaffMember;
use App\Models\SubcontractorOnboarding;
use App\Services\StaffPortal\StaffIdentityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
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
            'staffMember' => $staffMember->load('onboarding'),
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

    public function update(Request $request, StaffMember $staffMember, StaffIdentityService $identity): RedirectResponse
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
            'show_on_schedule' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        foreach ($staffMember->documentFields() as $field => $label) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $data[$field] = $file->store('staff-documents/'.$staffMember->id, 'local');
                $data[$field.'_name'] = $file->getClientOriginalName();
            }
        }

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
        $path = $this->documentPath($staffMember, $field);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    public function downloadDocument(StaffMember $staffMember, string $field): StreamedResponse
    {
        $path = $this->documentPath($staffMember, $field);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $staffMember->documentName($field));
    }

    public function deleteDocument(StaffMember $staffMember, string $field): RedirectResponse
    {
        $path = $this->documentPath($staffMember, $field);
        abort_unless($path, 404);

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        $staffMember->forceFill([
            $field => null,
            $field.'_name' => null,
        ])->save();

        return back()->with('status', $staffMember->documentFields()[$field].' deleted.');
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

    private function documentPath(StaffMember $staffMember, string $field): ?string
    {
        abort_unless(array_key_exists($field, $staffMember->documentFields()), 404);

        return $staffMember->{$field};
    }
}
