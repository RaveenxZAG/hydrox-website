<?php

namespace App\Services;

use App\Models\SystemNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SystemNotificationService
{
    public const ADMIN_EMAIL = 'admin@hydrox.au';

    public function __construct(private readonly TelegramNotificationService $telegram) {}

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
            $body = trim((string) $notification->message);

            if ($notification->action_url) {
                $body .= "\n\nOpen in the Hydrox Portal:\n".$notification->action_url;
            }

            Mail::raw($body ?: $notification->title, function ($message) use ($notification): void {
                $message->to(config('app.company_email', self::ADMIN_EMAIL))
                    ->subject($notification->title);
            });

            $notification->forceFill(['emailed_at' => now()])->save();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
