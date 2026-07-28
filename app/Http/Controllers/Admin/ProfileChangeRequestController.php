<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfileChangeRequest;
use App\Services\StaffPortal\StaffIdentityService;
use App\Services\SystemNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileChangeRequestController extends Controller
{
    public function index(): View
    {
        return view('admin.profile-changes.index', [
            'requests' => StaffProfileChangeRequest::with('staffMember')
                ->where('status', 'pending')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function approve(StaffProfileChangeRequest $profileChange, StaffIdentityService $identity): RedirectResponse
    {
        $staff = $profileChange->staffMember;
        $changes = $profileChange->changes ?? [];

        abort_unless($staff, 404);

        $identity->assertAvailable(
            array_key_exists('email', $changes) ? $changes['email'] : null,
            array_key_exists('mobile', $changes) ? $changes['mobile'] : null,
            $staff::class,
            $staff->id
        );

        $staff->fill(array_intersect_key($changes, array_flip([
            'email',
            'mobile',
            'address',
            'emergency_contact',
            'legal_business_name',
            'trading_name',
            'abn',
            'gst_registered',
            'business_structure',
            'contact_person',
            'business_address',
            'availability',
            'skills',
            'experience',
            'bank_details',
            'superannuation',
            'insurance_expiry',
            'notes',
        ])));

        foreach ($staff->documentFields() as $field => $label) {
            $file = $changes[$field] ?? null;

            if (is_array($file) && filled($file['path'] ?? null)) {
                $currentPath = $staff->{$field};

                if (
                    filled($currentPath)
                    && $currentPath !== $file['path']
                    && Storage::disk('local')->exists($currentPath)
                ) {
                    Storage::disk('local')->delete($currentPath);
                }

                $staff->{$field} = $file['path'];
                $staff->{$field.'_name'} = $file['original_name'] ?? basename((string) $file['path']);
            }
        }

        $staff->normalized_email = $identity->normalizeEmail($staff->email);
        $staff->normalized_mobile = $identity->normalizeMobile($staff->mobile);
        $staff->save();
        $identity->updateLock($staff, $staff->email, $staff->mobile);

        $profileChange->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);
        app(SystemNotificationService::class)->markSubjectRead($profileChange);

        return back()->with('status', 'Subcontractor profile update approved.');
    }

    public function reject(Request $request, StaffProfileChangeRequest $profileChange): RedirectResponse
    {
        $data = $request->validate(['review_note' => ['nullable', 'string', 'max:1000']]);

        $this->deletePendingDocuments($profileChange);

        $profileChange->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_note' => $data['review_note'] ?? null,
        ]);
        app(SystemNotificationService::class)->markSubjectRead($profileChange);

        return back()->with('status', 'Subcontractor profile update rejected.');
    }

    public function document(StaffProfileChangeRequest $profileChange, string $field): BinaryFileResponse
    {
        $file = $this->pendingDocument($profileChange, $field);

        return response()->file(Storage::disk('local')->path($file['path']));
    }

    public function downloadDocument(StaffProfileChangeRequest $profileChange, string $field): StreamedResponse
    {
        $file = $this->pendingDocument($profileChange, $field);

        return Storage::disk('local')->download(
            $file['path'],
            $file['original_name'] ?? basename((string) $file['path'])
        );
    }

    private function pendingDocument(StaffProfileChangeRequest $profileChange, string $field): array
    {
        $staff = $profileChange->staffMember;

        abort_unless($staff && array_key_exists($field, $staff->documentFields()), 404);

        $file = ($profileChange->changes ?? [])[$field] ?? null;

        abort_unless(is_array($file) && filled($file['path'] ?? null), 404);
        abort_unless(Storage::disk('local')->exists($file['path']), 404);

        return $file;
    }

    private function deletePendingDocuments(StaffProfileChangeRequest $profileChange): void
    {
        $staff = $profileChange->staffMember;

        if (! $staff) {
            return;
        }

        foreach ($staff->documentFields() as $field => $label) {
            $file = ($profileChange->changes ?? [])[$field] ?? null;

            if (is_array($file) && filled($file['path'] ?? null) && Storage::disk('local')->exists($file['path'])) {
                Storage::disk('local')->delete($file['path']);
            }
        }
    }
}
