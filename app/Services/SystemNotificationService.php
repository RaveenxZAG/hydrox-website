<?php

namespace App\Services;

use App\Mail\AdminSystemNotificationMail;
use App\Models\SystemNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SystemNotificationService
{
    public const ADMIN_EMAIL = 'admin@hydrox.au';

    public function __construct(
        private readonly TelegramNotificationService $telegram,
        private readonly MicrosoftGraphMailService $graph
    ) {}

    public function notify(
        string $type,
        string $title,
        ?string $message = null,
        ?string $actionUrl = null,
        ?Model $subject = null,
        bool $sendEmail = true
    ): SystemNotification {
        $notification = SystemNotification::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
        ]);

        if ($sendEmail) {
            $this->sendEmail($notification);
        }

        $this->telegram->send($notification);

        return $notification;
    }

    public function notifyMailable(string $recipientEmail, Mailable $mailable): bool
    {
        if (blank($recipientEmail)) {
            return false;
        }

        try {
            if ($this->graph->configured()) {
                $this->graph->send(
                    $recipientEmail,
                    (string) $mailable->envelope()->subject,
                    $mailable->render()
                );
            } else {
                Mail::to($recipientEmail)->send($mailable);
            }

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    public function markSubjectRead(Model $subject): int
    {
        return SystemNotification::unread()
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->update(['read_at' => now()]);
    }

    private function sendEmail(SystemNotification $notification): void
    {
        try {
            $adminEmail = config('app.company_email', self::ADMIN_EMAIL);
            $mailable = new AdminSystemNotificationMail($notification);

            if ($this->graph->configured()) {
                $this->graph->send(
                    $adminEmail,
                    (string) $mailable->envelope()->subject,
                    $mailable->render()
                );
            } else {
                Mail::to($adminEmail)->send($mailable);
            }

            $notification->forceFill(['emailed_at' => now()])->save();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}

