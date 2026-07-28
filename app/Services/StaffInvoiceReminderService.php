<?php

namespace App\Services;

use App\Models\StaffInvoiceSubmission;
use App\Models\StaffMember;
use App\Services\StaffPortal\InvoicePeriodService;
use Illuminate\Support\Collection;
use Throwable;

class StaffInvoiceReminderService
{
    public function __construct(
        private readonly InvoicePeriodService $periods,
        private readonly ServiceM8StaffService $serviceM8,
        private readonly SystemNotificationService $notifications
    ) {}

    public function send(array $staffIds = [], bool $force = false, bool $dryRun = false): array
    {
        $window = $this->periods->currentWindow();
        $summary = $this->emptySummary($window, $force, $dryRun);
        $staffMembers = $this->staffMembers($staffIds);

        if (! $window['is_open'] && ! $force) {
            $summary['failures'][] = 'Work log upload window is currently closed. Use --force for testing.';

            return $summary;
        }

        $staffMembers->each(function (StaffMember $staff) use (&$summary, $window, $force, $dryRun): void {
            $summary['checked']++;

            if (! $staff->canAccessPortal()) {
                $summary['skipped'][] = $this->staffLabel($staff).' skipped: subcontractor portal access is not active.';

                return;
            }

            if (! $staff->invoicing_enabled) {
                $summary['skipped'][] = $this->staffLabel($staff).' skipped: invoicing is disabled.';

                return;
            }

            if (! $force && $this->hasSubmittedInvoice($staff, $window)) {
                $summary['skipped'][] = $this->staffLabel($staff).' skipped: work log already submitted.';

                return;
            }

            $emailBody = $this->emailBody($staff, $window);
            $smsBody = $this->smsBody($staff, $window);

            if ($staff->email) {
                if ($dryRun) {
                    $summary['emails'][] = $this->staffLabel($staff).' <'.$staff->email.'>';
                } else {
                    try {
                        $this->serviceM8->sendEmail(
                            $staff->email,
                            'Hydrox Facility Management work log reminder - '.$window['label'],
                            $emailBody,
                            $this->htmlBody($emailBody)
                        );
                        $summary['emails'][] = $this->staffLabel($staff).' <'.$staff->email.'>';
                    } catch (Throwable $exception) {
                        report($exception);
                        $summary['failures'][] = $this->staffLabel($staff).' email failed: '.$exception->getMessage();
                    }
                }
            } else {
                $summary['skipped'][] = $this->staffLabel($staff).' email skipped: email is missing.';
            }

            if ($staff->mobile) {
                if ($dryRun) {
                    $summary['sms'][] = $this->staffLabel($staff).' <'.$staff->mobile.'>';
                } else {
                    try {
                        $this->serviceM8->sendSms($staff->mobile, $smsBody);
                        $summary['sms'][] = $this->staffLabel($staff).' <'.$staff->mobile.'>';
                    } catch (Throwable $exception) {
                        report($exception);
                        $summary['failures'][] = $this->staffLabel($staff).' SMS failed: '.$exception->getMessage();
                    }
                }
            } else {
                $summary['skipped'][] = $this->staffLabel($staff).' SMS skipped: mobile is missing.';
            }
        });

        if (! $dryRun) {
            $this->notifyAdmin($summary);
        }

        return $summary;
    }

    private function staffMembers(array $staffIds): Collection
    {
        return StaffMember::query()
            ->when($staffIds !== [], fn ($query) => $query->whereIn('id', $staffIds))
            ->when($staffIds === [], fn ($query) => $query->where('invoicing_enabled', true))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    private function hasSubmittedInvoice(StaffMember $staff, array $window): bool
    {
        return StaffInvoiceSubmission::query()
            ->where('staff_member_id', $staff->id)
            ->whereDate('invoice_period', $window['period']->toDateString())
            ->exists();
    }

    private function emailBody(StaffMember $staff, array $window): string
    {
        return "Hi {$this->firstName($staff)},\n\n"
            ."Your Hydrox Facility Management work log upload is now open for {$window['label']}.\n\n"
            .'Please upload your work log before '.$window['closes_at']->format('d M Y').".\n\n"
            ."Open the subcontractor portal here:\n"
            .route('staff-portal.login', ['action' => 'invoice'])."\n\n"
            ."Thank you,\n"
            .'Hydrox Facility Management';
    }

    private function smsBody(StaffMember $staff, array $window): string
    {
        return "Hi {$this->firstName($staff)}, your Hydrox Facility Management work log upload is open for {$window['label']}. "
            .'Please upload before '.$window['closes_at']->format('d M Y').': '
            .route('staff-portal.login', ['action' => 'invoice']);
    }

    private function htmlBody(string $body): string
    {
        $lines = collect(preg_split('/\R/', $body) ?: [])
            ->map(fn (string $line): string => e($line))
            ->implode('<br>');

        return '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f8fb;padding:24px;color:#0f172a;">'
            .'<div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #d9e7f1;border-radius:14px;padding:24px;">'
            .'<p style="margin:0 0 8px;font-size:12px;font-weight:bold;letter-spacing:.08em;text-transform:uppercase;color:#0082c9;">Hydrox Facility Management Work Log Reminder</p>'
            .'<p style="margin:0;font-size:15px;line-height:1.65;color:#334155;">'.$lines.'</p>'
            .'</div>'
            .'</div>';
    }

    private function notifyAdmin(array $summary): void
    {
        $message = "Work log reminder run for {$summary['invoice_period']}.\n\n"
            ."Subcontractors checked: {$summary['checked']}\n"
            .'Emails sent: '.count($summary['emails'])."\n"
            .'SMS sent: '.count($summary['sms'])."\n"
            .'Skipped: '.count($summary['skipped'])."\n"
            .'Failures: '.count($summary['failures']);

        if ($summary['skipped']) {
            $message .= "\n\nSkipped:\n- ".implode("\n- ", $summary['skipped']);
        }

        if ($summary['failures']) {
            $message .= "\n\nFailures:\n- ".implode("\n- ", $summary['failures']);
        }

        $this->notifications->notify(
            'staff_invoice_reminders_sent',
            'Subcontractor work log reminders sent',
            $message,
            route('staff-invoices.index', ['month' => $summary['invoice_period_key']])
        );
    }

    private function emptySummary(array $window, bool $force, bool $dryRun): array
    {
        return [
            'invoice_period' => $window['label'],
            'invoice_period_key' => $window['period']->format('Y-m'),
            'opens_at' => $window['opens_at']->toDateTimeString(),
            'closes_at' => $window['closes_at']->toDateTimeString(),
            'is_open' => $window['is_open'],
            'force' => $force,
            'dry_run' => $dryRun,
            'checked' => 0,
            'emails' => [],
            'sms' => [],
            'skipped' => [],
            'failures' => [],
        ];
    }

    private function firstName(StaffMember $staff): string
    {
        return $staff->first_name ?: $staff->fullName();
    }

    private function staffLabel(StaffMember $staff): string
    {
        return "#{$staff->id} {$staff->fullName()}";
    }
}
