<?php

namespace App\Http\Controllers\StaffPortal;

use App\Http\Controllers\Controller;
use App\Models\StaffInvoiceSubmission;
use App\Models\StaffMember;
use App\Models\StaffProfileChangeRequest;
use App\Models\SubcontractorOnboarding;
use App\Services\StaffInvoiceReviewService;
use App\Services\StaffInvoiceSpreadsheetService;
use App\Services\StaffPortal\InvoicePeriodService;
use App\Services\SystemNotificationService;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, InvoicePeriodService $periods): View|RedirectResponse
    {
        return redirect()->route('staff-portal.invoices');
    }

    public function invoices(Request $request, InvoicePeriodService $periods): View|RedirectResponse
    {
        $staff = $this->staff($request);
        if (! $staff) {
            return redirect()->route('staff-portal.login');
        }

        $window = $periods->currentWindow();
        $currentInvoice = StaffInvoiceSubmission::with('workLogs')
            ->where('staff_member_id', $staff->id)
            ->whereDate('invoice_period', $window['period']->toDateString())
            ->first();

        return view('staff-portal.invoices', [
            'staff' => $staff,
            'window' => $window,
            'currentInvoice' => $currentInvoice,
            'invoices' => $staff->invoices()->latest('invoice_period')->limit(12)->get(),
            'invoicingEnabled' => $staff->invoicing_enabled,
        ]);
    }

    public function profile(Request $request): View|RedirectResponse
    {
        $staff = $this->staff($request);
        if (! $staff) {
            return redirect()->route('staff-portal.login', ['action' => 'profile']);
        }

        return view('staff-portal.profile', [
            'staff' => $staff,
            'pendingProfileChange' => $staff->profileChangeRequests()->where('status', 'pending')->latest()->first(),
            'uploadLimits' => $this->profileUploadLimits(),
        ]);
    }

    public function downloadInvoiceTemplate(Request $request, StaffInvoiceReviewService $review, StaffInvoiceSpreadsheetService $spreadsheets, InvoicePeriodService $periods): Response|RedirectResponse
    {
        $staff = $this->staff($request);
        if (! $staff) {
            return redirect()->route('staff-portal.login');
        }

        if (! $staff->canSubmitInvoices()) {
            return back()->withErrors(['invoice' => 'Work log uploading is not enabled for your subcontractor profile. Please contact administration.']);
        }

        $sites = $review->activeSites();
        if ($sites->isEmpty()) {
            return back()->withErrors(['invoice' => 'No active work log sites are available yet. Please contact administration.']);
        }

        $window = $periods->currentWindow();
        $path = storage_path('app/temp/work-log-template-'.$staff->id.'-'.$window['period']->format('Y-m').'.xlsx');
        $spreadsheets->createTemplate($sites, $path);

        return response()->download($path, 'Cleaner_The_Crow_'.$window['period']->format('Y-m').'_Work_Log_Template.xlsx')->deleteFileAfterSend(true);
    }

    public function uploadInvoice(Request $request, InvoicePeriodService $periods, SystemNotificationService $notifications, StaffInvoiceReviewService $review): RedirectResponse
    {
        $staff = $this->staff($request);
        if (! $staff) {
            return redirect()->route('staff-portal.login');
        }

        if (! $staff->canSubmitInvoices()) {
            return back()->withErrors(['invoice' => 'Work log uploading is not enabled for your subcontractor profile. Please contact administration.']);
        }

        $window = $periods->currentWindow();
        if (! $window['is_open']) {
            return back()->withErrors(['invoice' => 'Work log uploading is currently closed. Please contact administration.']);
        }

        $request->validate([
            'invoice_file' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
            'confirm' => ['accepted'],
        ]);

        try {
            $invoice = $review->storeUpload($staff, $request->file('invoice_file'), $window['period'], $request->ip());
        } catch (\Throwable $exception) {
            return back()->withErrors(['invoice' => $exception->getMessage()]);
        }

        $notifications->notify(
            'staff_invoice_submitted',
            'New subcontractor work log submitted',
            "{$staff->fullName()} submitted a work log for {$invoice->invoice_period->format('F Y')}.\n\nClaimed amount: {$invoice->total_amount}",
            route('staff-invoices.index', ['month' => $invoice->invoice_period->format('Y-m')]),
            $invoice
        );

        return back()->with('status', 'Work log submitted successfully.');
    }

    public function downloadInvoice(Request $request, StaffInvoiceSubmission $invoice)
    {
        $staff = $this->staff($request);
        if (! $staff || $invoice->staff_member_id !== $staff->id) {
            abort(403);
        }

        return Storage::disk('local')->download($invoice->storage_path, $invoice->storedFilename());
    }

    public function downloadRemittance(Request $request, StaffInvoiceSubmission $invoice)
    {
        $staff = $this->staff($request);
        if (! $staff || $invoice->staff_member_id !== $staff->id || ! $invoice->remittance_path) {
            abort(403);
        }

        return Storage::disk('local')->download($invoice->remittance_path, 'remittance-'.$invoice->invoice_period->format('Y-m').'.pdf');
    }

    public function requestProfileUpdate(Request $request): RedirectResponse
    {
        $staff = $this->staff($request);
        if (! $staff) {
            return redirect()->route('staff-portal.login');
        }

        $uploadLimits = $this->profileUploadLimits();
        foreach ($staff->documentFields() as $field => $label) {
            $file = $request->file($field);

            if ($file && ! $file->isValid()) {
                return back()
                    ->withInput($request->except(array_keys($staff->documentFields())))
                    ->withErrors([
                        $field => "{$label} could not be uploaded. Please keep the file under {$uploadLimits['file_label']} and use PDF, Word, JPG, PNG, or WebP.",
                    ]);
            }
        }

        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:50'],
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
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['skills'] = array_values(array_filter(Arr::wrap($data['skills'] ?? [])));
        $data['gst_registered'] = (bool) ($data['gst_registered'] ?? false);

        $changes = [];
        foreach (array_diff(array_keys($data), array_keys($staff->documentFields())) as $field) {
            $requestedValue = $data[$field] ?? null;
            $currentValue = $staff->{$field};

            if ($field === 'insurance_expiry') {
                $requestedValue = filled($requestedValue) ? (string) $requestedValue : null;
                $currentValue = $staff->insurance_expiry?->format('Y-m-d');
            } elseif ($field === 'gst_registered') {
                $requestedValue = (bool) $requestedValue;
                $currentValue = (bool) $currentValue;
            } elseif ($field === 'skills') {
                $requestedValue = collect($requestedValue)->map(fn ($value) => trim((string) $value))->filter()->sort()->values()->all();
                $currentValue = collect(Arr::wrap($currentValue))->map(fn ($value) => trim((string) $value))->filter()->sort()->values()->all();
            } else {
                $requestedValue = filled($requestedValue) ? trim((string) $requestedValue) : null;
                $currentValue = filled($currentValue) ? trim((string) $currentValue) : null;
            }

            if ($requestedValue !== $currentValue) {
                $changes[$field] = $requestedValue;
            }
        }

        foreach ($staff->documentFields() as $field => $label) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $changes[$field] = [
                    'path' => $file->store('staff-profile-change-documents/'.$staff->id, 'local'),
                    'original_name' => $file->getClientOriginalName(),
                ];
            }
        }

        if ($changes === []) {
            return back()->withErrors(['profile' => 'Please enter at least one profile change before submitting.']);
        }

        $profileChange = StaffProfileChangeRequest::create([
            'staff_member_id' => $staff->id,
            'changes' => $changes,
            'submitted_at' => now(),
        ]);

        app(SystemNotificationService::class)->notify(
            'staff_profile_update_submitted',
            'Subcontractor profile update submitted',
            "{$staff->fullName()} submitted a profile update request.\n\nPlease review and approve or reject the changes in the Hydrox Portal.",
            route('staff-profile-changes.index'),
            $profileChange
        );

        return redirect()
            ->route('staff-portal.profile')
            ->with('status', 'Profile update submitted for admin review.');
    }

    private function staff(Request $request): ?StaffMember
    {
        $id = $request->session()->get('staff_member_id');

        if (! $id) {
            return null;
        }

        $staff = StaffMember::find($id);

        return $staff?->canAccessPortal() ? $staff : null;
    }

    private function profileUploadLimits(): array
    {
        $appFileBytes = 10 * 1024 * 1024;
        $serverFileBytes = $this->phpSizeToBytes(ini_get('upload_max_filesize')) ?: $appFileBytes;
        $serverPostBytes = $this->phpSizeToBytes(ini_get('post_max_size')) ?: $appFileBytes;
        $fileBytes = min($appFileBytes, $serverFileBytes);
        $postBytes = min(50 * 1024 * 1024, $serverPostBytes);

        return [
            'file_bytes' => $fileBytes,
            'post_bytes' => $postBytes,
            'file_label' => $this->formatBytes($fileBytes),
            'post_label' => $this->formatBytes($postBytes),
        ];
    }

    private function phpSizeToBytes(string|false $size): ?int
    {
        if ($size === false || trim($size) === '') {
            return null;
        }

        $size = trim($size);
        $unit = strtolower(substr($size, -1));
        $number = (float) $size;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return rtrim(rtrim(number_format($bytes / 1024 / 1024, 1), '0'), '.').' MB';
        }

        return rtrim(rtrim(number_format($bytes / 1024, 1), '0'), '.').' KB';
    }
}
