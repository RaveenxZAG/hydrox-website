<?php

namespace App\Services;

use App\Models\SiteShiftAssignment;
use Illuminate\Support\Str;
use Throwable;

class SiteAssignmentNotificationService
{
    public function __construct(private readonly ServiceM8StaffService $serviceM8) {}

    public function send(SiteShiftAssignment $assignment, string $eventType): array
    {
        $assignment->loadMissing('staffMember', 'shift.site');
        $staff = $assignment->staffMember;
        $subject = $this->subject($assignment, $eventType);
        $message = $this->message($assignment, $eventType);
        $summary = ['sent' => 0, 'skipped' => 0, 'failed' => 0];

        foreach (['email' => $staff?->email, 'sms' => $staff?->mobile] as $channel => $recipient) {
            if (blank($recipient)) {
                $summary['skipped']++;
                $this->record($assignment, $eventType, $channel, null, 'skipped', $message, 'Contact detail is missing.');
                continue;
            }

            try {
                if ($channel === 'email') {
                    $this->serviceM8->sendEmail($recipient, $subject, $message, $this->htmlBody($message));
                } else {
                    $this->serviceM8->sendSms($recipient, $message);
                }

                $summary['sent']++;
                $this->record($assignment, $eventType, $channel, $recipient, 'sent', $message);
            } catch (Throwable $exception) {
                report($exception);
                $summary['failed']++;
                $this->record($assignment, $eventType, $channel, $recipient, 'failed', $message, $exception->getMessage());
            }
        }

        return $summary;
    }

    private function subject(SiteShiftAssignment $assignment, string $eventType): string
    {
        $site = $assignment->shift->site;
        $action = match ($eventType) {
            'removed' => 'removed',
            'updated' => 'updated',
            default => 'confirmed',
        };

        return "Hydrox Facility Management site assignment {$action}: {$site->site_code} {$site->name}";
    }

    private function message(SiteShiftAssignment $assignment, string $eventType): string
    {
        $staff = $assignment->staffMember;
        $shift = $assignment->shift;
        $site = $shift->site;
        $intro = match ($eventType) {
            'removed' => 'You have been removed from this site shift:',
            'updated' => 'Your site shift details have been updated:',
            default => 'You have been assigned to this site shift:',
        };

        return "Hi {$staff->first_name},\n\n{$intro}\n"
            ."Site: {$site->name}\n"
            ."Site code: {$site->site_code}\n"
            .'Shift: '.ucfirst($shift->weekday)." - {$shift->label}\n"
            .'Hours: '.number_format((float) $shift->hours, 2)."\n\n"
            .'Hydrox Facility Management';
    }

    private function record(SiteShiftAssignment $assignment, string $eventType, string $channel, ?string $recipient, string $status, string $message, ?string $error = null): void
    {
        $assignment->deliveries()->create([
            'event_type' => $eventType,
            'channel' => $channel,
            'recipient' => $recipient,
            'status' => $status,
            'message' => $message,
            'error_message' => $error ? Str::limit($error, 4000) : null,
            'attempted_at' => now(),
            'sent_at' => $status === 'sent' ? now() : null,
        ]);
    }

    private function htmlBody(string $message): string
    {
        $body = collect(preg_split('/\R/', $message) ?: [])->map(fn (string $line): string => e($line))->implode('<br>');

        return '<div style="font-family:Arial,sans-serif;background:#f4f8fb;padding:24px;color:#0f172a"><div style="max-width:640px;margin:auto;background:#fff;border:1px solid #d9e7f1;border-radius:14px;padding:24px"><p style="margin:0 0 12px;font-size:12px;font-weight:bold;text-transform:uppercase;color:#0082c9">Hydrox Facility Management Site Assignment</p><p style="margin:0;line-height:1.65">'.$body.'</p></div></div>';
    }
}
