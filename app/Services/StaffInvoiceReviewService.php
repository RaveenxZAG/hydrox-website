<?php

namespace App\Services;

use App\Models\InvoiceSite;
use App\Models\StaffInvoiceArchive;
use App\Models\StaffInvoiceRemittanceDelivery;
use App\Models\StaffInvoiceSubmission;
use App\Models\StaffInvoiceWorkLog;
use App\Models\StaffMember;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class StaffInvoiceReviewService
{
    public function __construct(
        private readonly StaffInvoiceSpreadsheetService $spreadsheets,
        private readonly ServiceM8StaffService $serviceM8,
        private readonly SiteAssignmentNotificationService $siteAssignmentNotifications,
        private readonly RemittanceBreakdownService $remittanceBreakdowns,
    ) {}

    public function activeSites(): Collection
    {
        return InvoiceSite::with(['shifts' => fn ($query) => $query->where('active', true)->orderBy('weekday')->orderBy('label')])
            ->where('active', true)
            ->orderBy('site_code')
            ->orderBy('name')
            ->get();
    }

    public function storeUpload(StaffMember $staff, UploadedFile $file, CarbonInterface $month, string $ip): StaffInvoiceSubmission
    {
        $month = $month->copy()->startOfMonth();
        $existing = StaffInvoiceSubmission::where('staff_member_id', $staff->id)
            ->whereDate('invoice_period', $month->toDateString())
            ->first();

        if ($existing && ! in_array($existing->status, ['correction_required'], true)) {
            throw new RuntimeException('You have already submitted a work log for this month. Please contact support if it needs to be changed.');
        }

        $storedName = $this->storedFilename($staff, $month, $existing?->id);
        $path = $file->storeAs('staff-invoices/'.$staff->id.'/'.$month->format('Y-m'), $storedName, 'local');
        try {
            $rows = $this->spreadsheets->readWorkLogs(Storage::disk('local')->path($path));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        if ($rows === []) {
            Storage::disk('local')->delete($path);
            throw new RuntimeException('The work log spreadsheet does not contain any rows.');
        }

        $isNewInvoice = ! $existing;

        if ($existing) {
            $this->archiveCurrentInvoice($existing, 'Corrected work log uploaded.');
            $invoice = $existing;
            $invoice->workLogs()->delete();
            $invoice->version++;
        } else {
            $invoice = new StaffInvoiceSubmission([
                'staff_member_id' => $staff->id,
                'invoice_period' => $month->toDateString(),
                'version' => 1,
            ]);
        }

        $invoice->fill([
            'total_amount' => 0,
            'approved_total' => null,
            'invoice_reference' => null,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType() ?: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'checksum' => hash_file('sha256', Storage::disk('local')->path($path)),
            'status' => $invoice->exists ? 'resubmitted' : 'pending_review',
            'correction_reason' => null,
            'correction_instructions' => null,
            'correction_due_at' => null,
            'submitted_at' => now(),
            'submitted_ip' => $ip,
            'reviewed_at' => null,
            'ready_for_payment_at' => null,
            'paid_at' => null,
            'payment_reference' => null,
            'remittance_path' => null,
        ])->save();

        $this->importRows($invoice, $rows, $month);
        $invoice->forceFill(['total_amount' => $invoice->workLogs()->sum('amount')])->save();

        if ($isNewInvoice) {
            $finalPath = dirname($path).'/'.$this->storedFilename($staff, $month, $invoice->id);
            Storage::disk('local')->move($path, $finalPath);
            $invoice->forceFill([
                'storage_path' => $finalPath,
                'checksum' => hash_file('sha256', Storage::disk('local')->path($finalPath)),
            ])->save();
        }

        return $invoice->fresh(['workLogs']);
    }

    public function summariesForMonth(CarbonInterface $month): Collection
    {
        $month = $month->copy()->startOfMonth();
        $logs = StaffInvoiceWorkLog::with(['invoice', 'site'])
            ->whereHas('invoice', fn ($query) => $query
                ->whereDate('invoice_period', $month->toDateString())
                ->whereNotIn('status', ['correction_required']))
            ->get();

        return InvoiceSite::orderBy('name')->get()->map(function (InvoiceSite $site) use ($logs, $month): array {
            $siteLogs = $logs->where('invoice_site_id', $site->id);
            $regular = $siteLogs->where('work_type', 'regular');
            $other = $siteLogs->where('work_type', 'other_work');
            $expected = $site->expectedHoursForMonth($month);
            $contract = $site->contractHoursForMonth($month);
            $claimed = round((float) $regular->sum('hours'), 2);
            $unclaimed = max(0, round($expected - $claimed, 2));

            return [
                'site' => $site,
                'contract_hours' => $contract,
                'expected_hours' => $expected,
                'claimed_hours' => $claimed,
                'variance' => round($claimed - $expected, 2),
                'unclaimed_hours' => $unclaimed,
                'free_hours' => $site->freeHoursForMonth($month),
                'regular_amount' => round((float) $regular->sum('amount'), 2),
                'other_amount' => round((float) $other->sum('amount'), 2),
                'flag_count' => $siteLogs->where('status', 'flagged')->count(),
                'pattern_label' => $site->patternLabel(),
                'validation_warning' => $site->needsAnchorDate() ? 'Fortnightly anchor date needed' : null,
            ];
        });
    }

    public function submissionProgressForMonth(CarbonInterface $month): array
    {
        $month = $month->copy()->startOfMonth();
        $enabledStaffCount = StaffMember::where('active', true)
            ->where('staff_status', 'active')
            ->where('portal_access_enabled', true)
            ->where('invoicing_enabled', true)
            ->count();
        $submittedStaffCount = StaffInvoiceSubmission::whereDate('invoice_period', $month->toDateString())
            ->whereNotIn('status', ['correction_required'])
            ->whereHas('staffMember', fn ($query) => $query
                ->where('active', true)
                ->where('staff_status', 'active')
                ->where('portal_access_enabled', true)
                ->where('invoicing_enabled', true))
            ->distinct()
            ->count('staff_member_id');

        return [
            'enabled' => $enabledStaffCount,
            'submitted' => $submittedStaffCount,
            'missing' => max(0, $enabledStaffCount - $submittedStaffCount),
            'complete' => $submittedStaffCount >= $enabledStaffCount,
        ];
    }

    public function importRecurringSites(array $rows): array
    {
        $created = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $query = InvoiceSite::query();
            if (filled($row['site_code'])) {
                $query->where(function ($siteQuery) use ($row): void {
                    $siteQuery
                        ->where('site_code', $row['site_code'])
                        ->orWhere('name', $row['name']);
                });
            } else {
                $query->where('name', $row['name']);
            }

            $site = $query->first();

            $data = [
                'site_code' => $row['site_code'] ?: ($site?->site_code ?: InvoiceSite::nextSiteCode()),
                'name' => $row['name'],
                'active' => true,
                'recurring_pattern' => $row['recurring_pattern'],
                'validation_mode' => $row['validation_mode'],
                'fortnightly_anchor_date' => null,
                'weekly_contract_hours' => collect($row['shifts'] ?? [])->sum(fn (array $shift): float => (float) ($shift['contract_hours'] ?? $shift['hours'] ?? 0)),
                'monday_hours' => $row['monday_hours'],
                'tuesday_hours' => $row['tuesday_hours'],
                'wednesday_hours' => $row['wednesday_hours'],
                'thursday_hours' => $row['thursday_hours'],
                'friday_hours' => $row['friday_hours'],
                'saturday_hours' => $row['saturday_hours'],
                'sunday_hours' => $row['sunday_hours'],
                'notes' => $row['notes'],
            ];

            if ($site) {
                $site->update($data);
                $updated++;
            } else {
                $site = InvoiceSite::create($data);
                $created++;
            }

            $this->syncSiteShifts($site, $row['shifts'] ?? []);
        }

        return compact('created', 'updated');
    }

    public function syncSiteShifts(InvoiceSite $site, array $shifts, bool $notifyChanges = true): array
    {
        $existing = $site->shifts()->with('activeAssignments.staffMember')->get()->keyBy('id');
        $retainedIds = [];
        $notifiedAssignmentIds = [];

        foreach ($shifts as $shift) {
            if (($shift['hours'] ?? 0) <= 0) {
                continue;
            }

            $model = isset($shift['id']) && $shift['id']
                ? $existing->get((int) $shift['id'])
                : $existing->first(fn ($candidate) => ! in_array($candidate->id, $retainedIds, true)
                    && $candidate->weekday === $shift['weekday']
                    && $candidate->label === ($shift['label'] ?: 'Shift'));
            $attributes = [
                'weekday' => $shift['weekday'],
                'label' => $shift['label'] ?: 'Shift',
                'contract_hours' => $shift['hours'],
                'hours' => $shift['hours'],
                'active' => true,
            ];

            if ($model) {
                $changed = $model->weekday !== $attributes['weekday']
                    || $model->label !== $attributes['label']
                    || (float) $model->hours !== (float) $attributes['hours'];
                $model->update($attributes);
                $retainedIds[] = $model->id;

                if ($changed && $notifyChanges) {
                    foreach ($model->activeAssignments as $assignment) {
                        $this->siteAssignmentNotifications->send($assignment, 'updated');
                        $notifiedAssignmentIds[] = $assignment->id;
                    }
                }
            } else {
                $retainedIds[] = $site->shifts()->create($attributes)->id;
            }
        }

        $removed = $existing->reject(fn ($shift) => in_array($shift->id, $retainedIds, true));
        foreach ($removed as $shift) {
            foreach ($shift->activeAssignments as $assignment) {
                $assignment->update(['active' => false, 'unassigned_at' => now()]);
                if ($notifyChanges) {
                    $this->siteAssignmentNotifications->send($assignment, 'removed');
                    $notifiedAssignmentIds[] = $assignment->id;
                }
            }
            $shift->update(['active' => false]);
        }

        return array_values(array_unique($notifiedAssignmentIds));
    }

    public function approveFlag(StaffInvoiceWorkLog $log): void
    {
        $log->update([
            'status' => 'approved',
            'approved_amount' => $log->approved_amount ?? $log->amount,
            'reviewed_at' => now(),
        ]);
    }

    public function markReady(StaffInvoiceSubmission $invoice, bool $overrideFlags = false): void
    {
        if ($invoice->workLogs()->where('status', 'flagged')->exists() && ! $overrideFlags) {
            throw new RuntimeException('This work log still has unresolved items. Request a correction or confirm the admin override.');
        }

        $approvedTotal = $invoice->workLogs()->get()->sum(
            fn (StaffInvoiceWorkLog $log): float => (float) ($log->approved_amount ?? $log->amount)
        );

        $invoice->update([
            'status' => 'ready_for_payment',
            'approved_total' => $approvedTotal,
            'reviewed_at' => now(),
            'ready_for_payment_at' => now(),
        ]);
    }

    public function requestCorrection(StaffInvoiceSubmission $invoice, array $data): void
    {
        $this->archiveCurrentInvoice($invoice, $data['reason']);
        $invoice->update([
            'status' => 'correction_required',
            'correction_reason' => $data['reason'],
            'correction_instructions' => $data['instructions'] ?? null,
            'correction_due_at' => $data['due_at'] ?? null,
            'approved_total' => null,
        ]);

        $this->notifyCorrection($invoice);
    }

    public function markPaid(StaffInvoiceSubmission $invoice, array $data): array
    {
        if ($invoice->status !== 'ready_for_payment') {
            throw new RuntimeException('Only work logs marked ready for payment can be paid.');
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => $data['paid_at'],
            'payment_reference' => $data['payment_reference'] ?? null,
            'approved_total' => $data['approved_total'],
        ]);

        $path = $this->generateRemittance($invoice->fresh(['staffMember', 'workLogs.site']));
        $invoice->update(['remittance_path' => $path]);

        $invoice = $invoice->fresh(['staffMember', 'workLogs.site']);

        return [
            'invoice' => $invoice,
            'delivery' => $this->deliverRemittance($invoice, 'automatic'),
        ];
    }

    public function deliverRemittance(StaffInvoiceSubmission $invoice, string $attemptType = 'manual'): array
    {
        $invoice->loadMissing('staffMember');
        $staff = $invoice->staffMember;
        $results = [];

        if (! $invoice->remittance_path || ! Storage::disk('local')->exists($invoice->remittance_path)) {
            try {
                $path = $this->generateRemittance($invoice->fresh(['staffMember', 'workLogs.site']));
                $invoice->update(['remittance_path' => $path]);
                $invoice->refresh();
            } catch (Throwable $exception) {
                report($exception);

                return [
                    'email' => $this->recordDelivery($invoice, 'email', $staff?->email, 'failed', $attemptType, $exception->getMessage()),
                    'sms' => $this->recordDelivery($invoice, 'sms', $staff?->mobile, 'failed', $attemptType, 'The remittance PDF could not be generated.'),
                ];
            }
        }

        if (! $staff?->email) {
            $results['email'] = $this->recordDelivery($invoice, 'email', null, 'skipped', $attemptType, 'Subcontractor email address is missing.');
        } else {
            try {
                $attachmentUuid = $invoice->remittance_servicem8_attachment_uuid;
                if (! $attachmentUuid) {
                    $filename = $this->remittanceFilename($invoice);
                    $attachmentUuid = $this->serviceM8->uploadStaffAttachment(
                        (string) $staff->servicem8_staff_uuid,
                        $filename,
                        Storage::disk('local')->get($invoice->remittance_path)
                    );
                    $invoice->update(['remittance_servicem8_attachment_uuid' => $attachmentUuid]);
                }

                $business = SystemSetting::businessInformation();
                $html = view('emails.staff-remittance-paid', compact('invoice', 'staff', 'business'))->render();
                $month = $invoice->invoice_period->format('F Y');
                $text = "Hi {$staff->first_name},\n\n"
                    ."Your Hydrox Facility Management payment for {$month} has been completed by Bank Transfer (EFT).\n"
                    .'Total paid: $'.number_format((float) $invoice->approved_total, 2)." AUD\n"
                    .'Payment date: '.$invoice->paid_at?->format('d M Y')."\n\n"
                    ."Your remittance advice is attached and is also available in the subcontractor portal.\n\n"
                    .'Hydrox Facility Management Accounts Department';

                $this->serviceM8->sendEmail(
                    $staff->email,
                    "Payment remittance - {$month}",
                    $text,
                    $html,
                    [$attachmentUuid]
                );
                $results['email'] = $this->recordDelivery($invoice, 'email', $staff->email, 'sent', $attemptType);
            } catch (Throwable $exception) {
                report($exception);
                $results['email'] = $this->recordDelivery($invoice, 'email', $staff->email, 'failed', $attemptType, $exception->getMessage());
            }
        }

        if (! $staff?->mobile) {
            $results['sms'] = $this->recordDelivery($invoice, 'sms', null, 'skipped', $attemptType, 'Subcontractor mobile number is missing.');
        } else {
            try {
                $month = $invoice->invoice_period->format('F Y');
                $emailNote = ($results['email']['status'] ?? null) === 'sent'
                    ? 'Your remittance has been emailed and is also available in the subcontractor portal.'
                    : 'Your remittance is available in the subcontractor portal.';
                $message = "Hi {$staff->first_name}, Hydrox Facility Management has paid $"
                    .number_format((float) $invoice->approved_total, 2)
                    ." for {$month} by bank transfer. {$emailNote}";

                $this->serviceM8->sendSms($staff->mobile, $message);
                $results['sms'] = $this->recordDelivery($invoice, 'sms', $staff->mobile, 'sent', $attemptType);
            } catch (Throwable $exception) {
                report($exception);
                $results['sms'] = $this->recordDelivery($invoice, 'sms', $staff->mobile, 'failed', $attemptType, $exception->getMessage());
            }
        }

        return $results;
    }

    public function deliverySummary(array $delivery): string
    {
        return collect(['email' => 'Email', 'sms' => 'SMS'])
            ->map(function (string $label, string $channel) use ($delivery): string {
                $status = $delivery[$channel]['status'] ?? 'skipped';

                return $label.' '.match ($status) {
                    'sent' => 'sent',
                    'failed' => 'failed',
                    default => 'skipped',
                };
            })
            ->implode(', ').'.';
    }

    private function importRows(StaffInvoiceSubmission $invoice, array $rows, CarbonInterface $month): void
    {
        $sites = $this->siteLookup();
        $seen = [];

        foreach ($rows as $row) {
            $isOtherJob = $row['work_type'] === 'other_work';
            $jobCode = trim((string) ($row['service_m8_job_code'] ?? ''));
            $site = $isOtherJob ? null : $sites->get($this->normaliseSiteKey($row['site']));
            $shiftLabel = trim((string) ($row['shift'] ?? ''));
            $shift = null;
            $hours = $row['hours'];
            $amount = $row['amount'];
            $status = 'ok';
            $reasons = [];

            if (! $row['date'] || ! $row['date']->isSameMonth($month)) {
                $status = 'flagged';
                $reasons[] = 'Date is missing or outside the work log month.';
            }

            if (! $isOtherJob && ! $site) {
                $status = 'flagged';
                $reasons[] = 'Site code was not found in the active site list.';
            }

            if (! $isOtherJob && $site && ($row['shift_template'] ?? false)) {
                if ($shiftLabel === '') {
                    $status = 'flagged';
                    $reasons[] = 'Shift is required for Regular Site rows in the current template.';
                } elseif ($row['date']) {
                    $weekday = strtolower($row['date']->englishDayOfWeek);
                    $shift = $site->shifts()
                        ->where('active', true)
                        ->where('weekday', $weekday)
                        ->whereRaw('LOWER(label) = ?', [Str::lower($shiftLabel)])
                        ->first();

                    if (! $shift) {
                        $status = 'flagged';
                        $reasons[] = 'The selected shift is not available for this site on the work date.';
                    } else {
                        $officialHours = round((float) $shift->hours, 2);
                        if ($hours === null || abs($hours - $officialHours) > 0.009) {
                            $status = 'flagged';
                            $reasons[] = 'Hours did not match the selected shift and were corrected to the admin roster hours.';
                        }
                        $hours = $officialHours;

                        if (($row['hourly_rate'] ?? null) !== null) {
                            $amount = round($officialHours * (float) $row['hourly_rate'], 2);
                        }
                    }
                }
            }

            if ($isOtherJob && $jobCode === '') {
                $status = 'flagged';
                $reasons[] = 'Work reference is required for Other Job rows.';
            }

            if ($hours === null || $hours <= 0) {
                $status = 'flagged';
                $reasons[] = 'Hours must be greater than zero.';
            }

            if ($amount === null || $amount < 0) {
                $status = 'flagged';
                $reasons[] = 'Amount is missing or invalid.';
            }

            if ($isOtherJob) {
                $status = 'flagged';
                $reasons[] = 'Other Job needs manual admin review.';
            }

            if ($site && ! $isOtherJob && $row['date'] && $site->canAutoValidate() && ! $site->hasRosterHoursForDate($row['date'])) {
                $status = 'flagged';
                $reasons[] = $site->recurring_pattern === 'fortnightly'
                    ? 'Regular hours were claimed outside the valid fortnightly roster dates.'
                    : 'Regular hours were claimed on a day this site is not rostered.';
            }

            $key = implode('|', [
                $invoice->staff_member_id,
                $row['date']?->toDateString(),
                $isOtherJob ? Str::lower($jobCode) : Str::lower($row['site']),
                $isOtherJob ? '' : Str::lower($shiftLabel),
                $row['work_type'],
            ]);

            if (isset($seen[$key])) {
                $status = 'flagged';
                $reasons[] = 'Possible duplicate row for the same subcontractor, site, date, and work type.';
            }
            $seen[$key] = true;

            StaffInvoiceWorkLog::create([
                'staff_invoice_submission_id' => $invoice->id,
                'staff_member_id' => $invoice->staff_member_id,
                'invoice_site_id' => $site?->id,
                'invoice_site_shift_id' => $shift?->id,
                'work_date' => $row['date']?->toDateString(),
                'site_name' => $isOtherJob ? $this->otherJobLabel($jobCode) : ($site?->displayName() ?: ($row['site'] ?: 'Missing site code')),
                'shift_label' => $isOtherJob ? null : ($shift?->label ?: ($shiftLabel ?: null)),
                'service_m8_job_code' => $isOtherJob ? ($jobCode ?: null) : null,
                'work_type' => $row['work_type'],
                'hours' => $hours ?? 0,
                'amount' => $amount ?? 0,
                'notes' => $row['notes'],
                'row_number' => $row['row'],
                'status' => $status,
                'flag_reason' => $reasons === [] ? null : implode(' ', array_unique($reasons)),
                'approved_amount' => $status === 'ok' ? ($amount ?? 0) : null,
            ]);
        }
    }

    private function siteLookup(): Collection
    {
        return InvoiceSite::all()
            ->flatMap(function (InvoiceSite $site): array {
                return collect([
                    $site->name,
                    $site->site_code,
                    $site->displayName(),
                ])
                    ->filter()
                    ->mapWithKeys(fn (string $key): array => [$this->normaliseSiteKey($key) => $site])
                    ->all();
            });
    }

    private function normaliseSiteKey(?string $value): string
    {
        return Str::lower(trim((string) $value));
    }

    private function otherJobLabel(string $jobCode): string
    {
        if ($jobCode === '') {
            return 'Missing Work reference';
        }

        $normalisedCode = preg_replace('/^(?:ServiceM8\s+)?Job\s*/i', '', trim($jobCode));

        return 'Work Reference '.$normalisedCode;
    }

    private function archiveCurrentInvoice(StaffInvoiceSubmission $invoice, ?string $reason): void
    {
        StaffInvoiceArchive::create([
            'staff_invoice_submission_id' => $invoice->id,
            'version' => $invoice->version ?: 1,
            'status' => $invoice->status,
            'total_amount' => $invoice->total_amount,
            'original_filename' => $invoice->original_filename,
            'storage_path' => $invoice->storage_path,
            'work_logs' => $invoice->workLogs()->get()->toArray(),
            'reason' => $reason,
            'archived_at' => now(),
        ]);
    }

    private function notifyCorrection(StaffInvoiceSubmission $invoice): void
    {
        $invoice->loadMissing('staffMember');
        $staff = $invoice->staffMember;
        $month = $invoice->invoice_period->format('F Y');
        $due = $invoice->correction_due_at?->format('d M Y') ?: 'the requested due date';
        $message = "Hi {$staff->first_name}, your Hydrox Facility Management work log for {$month} needs correction. Reason: {$invoice->correction_reason}. Please upload the corrected work log by {$due}.";

        try {
            if ($staff->email) {
                $this->serviceM8->sendEmail(
                    $staff->email,
                    "Work log correction required - {$month}",
                    $message,
                    '<p>'.e($message).'</p>'
                );
            }
            if ($staff->mobile) {
                $this->serviceM8->sendSms($staff->mobile, $message);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function generateRemittance(StaffInvoiceSubmission $invoice): string
    {
        $breakdown = $this->remittanceBreakdowns->resolve($invoice);
        $siteAmounts = $invoice->workLogs
            ->groupBy(fn (StaffInvoiceWorkLog $log): string => $this->remittanceWorkCode($log))
            ->map(fn (Collection $logs, string $code): array => [
                'code' => $code,
                'category' => $logs->first()?->work_type === 'other_work' ? 'job' : 'site',
                'amount' => round((float) $logs->sum(fn (StaffInvoiceWorkLog $log) => $log->approved_amount ?? $log->amount), 2),
            ])
            ->values()
            ->sort(function (array $left, array $right): int {
                $categoryOrder = ['site' => 0, 'job' => 1];
                $categoryComparison = ($categoryOrder[$left['category']] ?? 2) <=> ($categoryOrder[$right['category']] ?? 2);

                return $categoryComparison !== 0
                    ? $categoryComparison
                    : strnatcasecmp($left['code'], $right['code']);
            })
            ->values();

        $logoPath = public_path('images/cleaner-the-crow-logo.jpg');
        $logoDataUri = File::exists($logoPath)
            ? 'data:'.File::mimeType($logoPath).';base64,'.base64_encode(File::get($logoPath))
            : null;

        $pdf = Pdf::loadView('pdf.staff-invoice-remittance', [
            'invoice' => $invoice,
            'staff' => $invoice->staffMember,
            'siteAmounts' => $siteAmounts,
            'breakdown' => $breakdown,
            'business' => SystemSetting::businessInformation(),
            'logoDataUri' => $logoDataUri,
        ])->setPaper('a4', 'portrait')->setOption('enable_php', true);

        $path = 'staff-invoice-remittances/'.$invoice->staff_member_id.'/'.$invoice->invoice_period->format('Y-m').'/remittance-'.$invoice->id.'.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    private function recordDelivery(
        StaffInvoiceSubmission $invoice,
        string $channel,
        ?string $recipient,
        string $status,
        string $attemptType,
        ?string $errorMessage = null
    ): array {
        $delivery = StaffInvoiceRemittanceDelivery::create([
            'staff_invoice_submission_id' => $invoice->id,
            'channel' => $channel,
            'recipient' => $recipient,
            'status' => $status,
            'attempt_type' => $attemptType,
            'error_message' => $errorMessage ? Str::limit($errorMessage, 4000) : null,
            'attempted_at' => now(),
        ]);

        return $delivery->only(['channel', 'recipient', 'status', 'error_message', 'attempted_at']);
    }

    private function remittanceFilename(StaffInvoiceSubmission $invoice): string
    {
        return 'Cleaner-The-Crow-Remittance-'.$invoice->invoice_period->format('F-Y').'.pdf';
    }

    private function remittanceWorkCode(StaffInvoiceWorkLog $log): string
    {
        if ($log->work_type === 'other_work') {
            $jobCode = trim((string) $log->service_m8_job_code);
            $jobCode = preg_replace('/^(?:ServiceM8\s+)?Job\s*/i', '', $jobCode);

            return $jobCode !== '' ? 'Job '.$jobCode : 'Other Job';
        }

        if ($log->site?->site_code) {
            return $log->site->site_code;
        }

        preg_match('/\bCTC\d+\b/i', (string) $log->site_name, $matches);

        return strtoupper($matches[0] ?? 'Site');
    }

    private function storedFilename(StaffMember $staff, CarbonInterface $month, ?int $invoiceId): string
    {
        $name = Str::of($staff->fullName())->ascii()->replaceMatches('/[^A-Za-z0-9]+/', '_')->trim('_');
        $id = $invoiceId ? 'INV'.str_pad((string) $invoiceId, 6, '0', STR_PAD_LEFT) : 'pending';

        return "{$name}_{$month->format('Y-m')}_{$id}.xlsx";
    }
}
