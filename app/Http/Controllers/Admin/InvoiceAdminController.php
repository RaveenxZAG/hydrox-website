<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSite;
use App\Models\StaffInvoiceSubmission;
use App\Models\StaffInvoiceWorkLog;
use App\Models\StaffMember;
use App\Services\StaffInvoiceReviewService;
use App\Services\SiteAssignmentNotificationService;
use App\Services\StaffPortal\InvoicePeriodService;
use App\Services\SystemNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InvoiceAdminController extends Controller
{
    public function index(Request $request, InvoicePeriodService $periods, StaffInvoiceReviewService $review): View
    {
        $month = $this->selectedMonth($request);
        $monthDate = Carbon::createFromFormat('Y-m', $month, 'Australia/Darwin')->startOfMonth();
        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));
        $contractorId = $request->integer('contractor');
        $siteId = $request->integer('site');

        $missingTables = $this->missingInvoiceTables();

        if ($missingTables !== []) {
            return view('admin.invoices.index', [
                'invoices' => collect(),
                'window' => $periods->currentWindow(),
                'month' => $month,
                'monthDate' => $monthDate,
                'monthRows' => collect(),
                'totalAmount' => 0,
                'approvedAmount' => 0,
                'status' => $status,
                'search' => $search,
                'contractorId' => $contractorId,
                'siteId' => $siteId,
                'contractors' => collect(),
                'sites' => collect(),
                'siteSummaries' => collect(),
                'missingTables' => $missingTables,
                'nextSiteCode' => null,
            ]);
        }

        $monthRows = StaffInvoiceSubmission::query()
            ->selectRaw('invoice_period, COUNT(*) as invoice_count, SUM(total_amount) as total_amount')
            ->groupBy('invoice_period')
            ->orderByDesc('invoice_period')
            ->get()
            ->groupBy(fn ($row) => $row->invoice_period->format('Y'));
        $query = StaffInvoiceSubmission::with(['staffMember', 'workLogs.site'])
            ->whereDate('invoice_period', $monthDate->toDateString())
            ->latest('submitted_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($contractorId > 0) {
            $query->where('staff_member_id', $contractorId);
        }

        if ($siteId > 0) {
            $query->whereHas('workLogs', fn ($workLogQuery) => $workLogQuery->where('invoice_site_id', $siteId));
        }

        if ($search !== '') {
            $query->where(function ($invoiceQuery) use ($search): void {
                $invoiceQuery
                    ->where('invoice_reference', 'like', '%'.$search.'%')
                    ->orWhereHas('staffMember', fn ($staffQuery) => $staffQuery
                        ->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%'));
            });
        }

        $invoices = $query->get();
        $siteSummaries = $review->summariesForMonth($monthDate);
        $submissionProgress = $review->submissionProgressForMonth($monthDate);
        $activeInvoiceCount = StaffInvoiceSubmission::whereDate('invoice_period', $monthDate->toDateString())
            ->whereNotIn('status', ['correction_required'])
            ->count();
        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'window' => $periods->currentWindow(),
            'month' => $month,
            'monthDate' => $monthDate,
            'monthRows' => $monthRows,
            'totalAmount' => $invoices->sum('total_amount'),
            'approvedAmount' => $invoices->sum('approved_total'),
            'status' => $status,
            'search' => $search,
            'contractorId' => $contractorId,
            'siteId' => $siteId,
            'contractors' => StaffMember::where('active', true)->where('invoicing_enabled', true)->orderBy('first_name')->orderBy('last_name')->get(),
            'sites' => InvoiceSite::with('shifts')->orderBy('site_code')->orderBy('name')->get(),
            'siteSummaries' => $siteSummaries,
            'totalInvoiceCount' => StaffInvoiceSubmission::whereDate('invoice_period', $monthDate->toDateString())->count(),
            'pendingCount' => StaffInvoiceSubmission::whereDate('invoice_period', $monthDate->toDateString())->whereIn('status', ['pending_review', 'resubmitted'])->count(),
            'readyCount' => StaffInvoiceSubmission::whereDate('invoice_period', $monthDate->toDateString())->where('status', 'ready_for_payment')->count(),
            'paidCount' => StaffInvoiceSubmission::whereDate('invoice_period', $monthDate->toDateString())->where('status', 'paid')->count(),
            'correctionCount' => StaffInvoiceSubmission::whereDate('invoice_period', $monthDate->toDateString())->where('status', 'correction_required')->count(),
            'unclaimedHours' => $siteSummaries->sum(fn (array $row): float => (float) ($row['unclaimed_hours'] ?? 0)),
            'missingInvoiceCount' => $submissionProgress['missing'],
            'allInvoicesSubmitted' => $submissionProgress['complete'],
            'activeInvoiceCount' => $activeInvoiceCount,
            'nextSiteCode' => InvoiceSite::nextSiteCode(),
        ]);
    }

    public function showReview(StaffInvoiceSubmission $invoice, StaffInvoiceReviewService $review): View
    {
        $invoice->load(['staffMember', 'workLogs.site', 'workLogs.shift', 'archives', 'remittanceDeliveries' => fn ($query) => $query->latest('attempted_at')]);

        $logs = $invoice->workLogs;
        $regularLogs = $logs->where('work_type', 'regular');
        $otherLogs = $logs->where('work_type', 'other_work');
        $monthlySiteSummaries = $review->summariesForMonth($invoice->invoice_period)
            ->keyBy(fn (array $row): string => (string) $row['site']->id);
        $submissionProgress = $review->submissionProgressForMonth($invoice->invoice_period);
        $regularRows = $regularLogs
            ->groupBy(fn (StaffInvoiceWorkLog $log): string => $log->invoice_site_id ? 'site-'.$log->invoice_site_id : 'name-'.$log->site_name)
            ->map(function ($siteLogs) use ($invoice, $monthlySiteSummaries, $submissionProgress): array {
                $first = $siteLogs->first();
                $monthlySummary = $first?->invoice_site_id ? $monthlySiteSummaries->get((string) $first->invoice_site_id) : null;
                $expected = (float) ($monthlySummary['expected_hours'] ?? ($first?->site ? $first->site->expectedHoursForMonth($invoice->invoice_period) : 0));
                $staffClaimed = (float) $siteLogs->sum('hours');
                $allStaffClaimed = (float) ($monthlySummary['claimed_hours'] ?? $staffClaimed);
                $variance = round($allStaffClaimed - $expected, 2);
                $isManual = ! $first?->site || $first->site->validation_mode === 'manual';
                $status = match (true) {
                    $isManual => 'manual_review',
                    $variance > 0.009 => 'extra',
                    $variance < -0.009 && ! $submissionProgress['complete'] => 'missing_invoices',
                    $variance < -0.009 => 'unclaimed',
                    default => 'matched',
                };

                return [
                    'site' => $first?->site_name ?: 'Unknown site',
                    'pattern_label' => $first?->site?->patternLabel() ?? 'Manual',
                    'validation_warning' => $first?->site?->needsAnchorDate() ? 'Fortnightly anchor date needed' : null,
                    'expected' => $expected,
                    'staff_claimed' => $staffClaimed,
                    'all_staff_claimed' => $allStaffClaimed,
                    'variance' => $variance,
                    'status' => $status,
                    'amount' => (float) $siteLogs->sum('amount'),
                    'flagged' => $siteLogs->where('status', 'flagged')->count(),
                ];
            })
            ->values();
        $paymentRows = $logs
            ->groupBy('site_name')
            ->map(fn ($siteLogs, $site): array => [
                'site' => $site,
                'amount' => (float) $siteLogs->sum(fn (StaffInvoiceWorkLog $log): float => (float) ($log->approved_amount ?? $log->amount)),
            ])
            ->values();

        return view('admin.invoices.review', [
            'invoice' => $invoice,
            'regularRows' => $regularRows,
            'regularLogs' => $regularLogs->sortBy('work_date')->values(),
            'submissionProgress' => $submissionProgress,
            'otherLogs' => $otherLogs,
            'paymentRows' => $paymentRows,
            'regularIssueCount' => $regularRows->filter(fn (array $row): bool => in_array($row['status'], ['extra', 'missing_invoices', 'manual_review'], true) || $row['flagged'] > 0)->count(),
            'approvedTotal' => $invoice->approved_total ?? $paymentRows->sum('amount'),
            'backMonth' => $invoice->invoice_period->format('Y-m'),
        ]);
    }

    public function storeSite(Request $request, StaffInvoiceReviewService $review): RedirectResponse
    {
        $site = InvoiceSite::create($this->siteData($request));
        $review->syncSiteShifts($site, $this->shiftData($request));

        return back()->with('status', 'Work log site created.');
    }

    public function updateSite(Request $request, InvoiceSite $site, StaffInvoiceReviewService $review, SiteAssignmentNotificationService $notifications): RedirectResponse
    {
        $oldIdentity = [$site->site_code, $site->name];
        $assignmentsBefore = $site->assignments()->where('active', true)->with('staffMember', 'shift.site')->get();
        $site->update($this->siteData($request, true));
        $alreadyNotified = $review->syncSiteShifts($site, $this->shiftData($request), $site->active);

        if (! $site->active) {
            foreach ($assignmentsBefore as $assignment) {
                $assignment->update(['active' => false, 'unassigned_at' => now()]);
                $notifications->send($assignment, 'removed');
            }
        } elseif ($oldIdentity !== [$site->site_code, $site->name]) {
            $site->load('shifts.activeAssignments.staffMember');
            foreach ($site->shifts->where('active', true) as $shift) {
                foreach ($shift->activeAssignments as $assignment) {
                    if (! in_array($assignment->id, $alreadyNotified, true)) {
                        $notifications->send($assignment, 'updated');
                    }
                }
            }
        }

        return back()->with('status', 'Work log site updated.');
    }

    public function destroySite(InvoiceSite $site, SiteAssignmentNotificationService $notifications): RedirectResponse
    {
        $site->load('shifts.activeAssignments.staffMember');
        foreach ($site->shifts as $shift) {
            foreach ($shift->activeAssignments as $assignment) {
                $assignment->update(['active' => false, 'unassigned_at' => now()]);
                $notifications->send($assignment, 'removed');
            }
            $shift->update(['active' => false]);
        }
        $site->update(['active' => false]);

        return back()->with('status', 'Work log site deactivated and assigned subcontractors notified.');
    }

    public function download(StaffInvoiceSubmission $invoice)
    {
        return Storage::disk('local')->download($invoice->storage_path, $invoice->storedFilename());
    }

    public function downloadRemittance(StaffInvoiceSubmission $invoice)
    {
        abort_unless($invoice->remittance_path, 404);

        return Storage::disk('local')->download($invoice->remittance_path, 'remittance-'.$invoice->invoice_period->format('Y-m').'.pdf');
    }

    public function destroy(StaffInvoiceSubmission $invoice): RedirectResponse
    {
        $month = $invoice->invoice_period?->format('Y-m') ?: now('Australia/Darwin')->subMonthNoOverflow()->format('Y-m');

        Storage::disk('local')->delete($invoice->storage_path);
        $invoice->delete();

        return redirect()
            ->route('staff-invoices.index', ['month' => $month])
            ->with('status', 'Subcontractor work log deleted.');
    }

    public function approveWorkLog(StaffInvoiceWorkLog $workLog, StaffInvoiceReviewService $review): RedirectResponse
    {
        $review->approveFlag($workLog);
        $invoice = $workLog->invoice;
        if ($invoice) {
            app(SystemNotificationService::class)->markSubjectRead($invoice);
        }

        if (! $invoice) {
            return back()->with('status', 'Flagged work row approved.');
        }

        return redirect()
            ->to(route('staff-invoices.review', $invoice).'#work-log-'.$workLog->id)
            ->with('status', 'Flagged work row approved.');
    }

    public function requestCorrection(Request $request, StaffInvoiceSubmission $invoice, StaffInvoiceReviewService $review): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'due_at' => ['nullable', 'date'],
        ]);

        $review->requestCorrection($invoice, $data);
        app(SystemNotificationService::class)->markSubjectRead($invoice);

        return redirect()->route('staff-invoices.review', $invoice)
            ->with('status', 'Correction request sent.');
    }

    public function markReady(Request $request, StaffInvoiceSubmission $invoice, StaffInvoiceReviewService $review): RedirectResponse
    {
        try {
            $review->markReady($invoice, $request->boolean('override_flags'));
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
        app(SystemNotificationService::class)->markSubjectRead($invoice);

        return back()->with('status', 'Work log marked ready for payment.');
    }

    public function markPaid(Request $request, StaffInvoiceSubmission $invoice, StaffInvoiceReviewService $review): RedirectResponse
    {
        $data = $request->validate([
            'paid_at' => ['required', 'date'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'approved_total' => ['required', 'numeric', 'min:0', 'max:999999'],
        ]);

        try {
            $result = $review->markPaid($invoice, $data);
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
        app(SystemNotificationService::class)->markSubjectRead($invoice);

        return back()->with('status', 'Work log marked paid. '.$review->deliverySummary($result['delivery']));
    }

    public function sendRemittance(StaffInvoiceSubmission $invoice, StaffInvoiceReviewService $review): RedirectResponse
    {
        if ($invoice->status !== 'paid') {
            return back()->with('error', 'Only paid work logs can send a remittance.');
        }

        $delivery = $review->deliverRemittance($invoice, 'manual');

        return back()->with('status', 'Remittance delivery attempted. '.$review->deliverySummary($delivery));
    }

    private function selectedMonth(Request $request): string
    {
        $month = (string) $request->query('month');

        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            return $month;
        }

        return now('Australia/Darwin')->subMonthNoOverflow()->format('Y-m');
    }

    private function missingInvoiceTables(): array
    {
        return collect([
            'staff_members',
            'staff_invoice_submissions',
            'invoice_sites',
            'invoice_site_shifts',
            'staff_invoice_work_logs',
            'staff_invoice_archives',
        ])
            ->reject(fn (string $table): bool => Schema::hasTable($table))
            ->values()
            ->all();
    }

    private function siteData(Request $request, bool $updating = false): array
    {
        $siteId = $request->route('site')?->id;
        $data = $request->validate([
            'site_code' => ['nullable', 'string', 'max:20', 'regex:/^CTC\d+$/i', 'unique:invoice_sites,site_code'.($siteId ? ','.$siteId : '')],
            'name' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'recurring_pattern' => ['nullable', 'in:weekly,fortnightly'],
            'validation_mode' => ['nullable', 'in:auto,manual'],
            'fortnightly_anchor_date' => ['nullable', 'date'],
            'weekly_contract_hours' => [$updating ? 'sometimes' : 'required', 'numeric', 'min:0', 'max:9999'],
            'monday_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'tuesday_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'wednesday_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'thursday_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'friday_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'saturday_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'sunday_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]) + ['active' => false];

        $data['site_code'] = strtoupper($data['site_code'] ?: InvoiceSite::nextSiteCode());
        $data['recurring_pattern'] = $data['recurring_pattern'] ?? 'weekly';
        $data['validation_mode'] = $data['validation_mode'] ?? 'auto';
        $data['weekly_contract_hours'] = round((float) ($data['weekly_contract_hours'] ?? 0), 2);

        if ($data['recurring_pattern'] === 'fortnightly' && blank($data['fortnightly_anchor_date'])) {
            $data['validation_mode'] = 'manual';
        }

        if ($data['recurring_pattern'] !== 'fortnightly') {
            $data['fortnightly_anchor_date'] = null;
        }

        return $data;
    }

    private function shiftData(Request $request): array
    {
        $request->validate([
            'shift_rows' => ['nullable', 'array'],
            'shift_rows.*.weekday' => ['nullable', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'shift_rows.*.id' => ['nullable', 'integer', 'exists:invoice_site_shifts,id'],
            'shift_rows.*.label' => ['nullable', 'string', 'max:80'],
            'shift_rows.*.hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'roster_days' => ['nullable', 'array'],
            'roster_days.*.weekday' => ['nullable', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'roster_days.*.shifts' => ['nullable', 'array'],
            'roster_days.*.shifts.*.id' => ['nullable', 'integer', 'exists:invoice_site_shifts,id'],
            'roster_days.*.shifts.*.label' => ['nullable', 'string', 'max:80'],
            'roster_days.*.shifts.*.hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
        ]);

        $rows = collect($request->input('roster_days', []))
            ->flatMap(function (array $day): array {
                $weekday = $day['weekday'] ?? null;

                return collect($day['shifts'] ?? [])
                    ->map(fn (array $shift): array => [
                        'id' => $shift['id'] ?? null,
                        'weekday' => $weekday,
                        'label' => $shift['label'] ?? '',
                        'hours' => $shift['hours'] ?? null,
                    ])
                    ->all();
            });

        if ($rows->isEmpty()) {
            $rows = collect($request->input('shift_rows', []));
        }

        return $rows
            ->map(function (array $row): ?array {
                $weekday = $row['weekday'] ?? null;
                $label = trim((string) ($row['label'] ?? ''));
                $hours = round((float) ($row['hours'] ?? 0), 2);

                if (! $weekday || $hours <= 0) {
                    return null;
                }

                return [
                    'id' => isset($row['id']) ? (int) $row['id'] : null,
                    'weekday' => $weekday,
                    'label' => $label !== '' ? $label : 'Shift',
                    'hours' => $hours,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
