<?php

namespace App\Services;

use App\Models\SystemNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramNotificationService
{
    public function send(SystemNotification $notification): bool
    {
        $token = trim((string) config('services.telegram.bot_token'));
        $chatId = trim((string) config('services.telegram.chat_id'));

        if ($token === '' || $chatId === '') {
            return false;
        }

        try {
            $response = Http::asJson()->timeout(8)->post(
                rtrim((string) config('services.telegram.api_url', 'https://api.telegram.org'), '/').'/bot'.$token.'/sendMessage',
                [
                    'chat_id' => $chatId,
                    'text' => $this->message($notification),
                    'disable_web_page_preview' => true,
                ]
            );

            if (! $response->successful() || ! $response->json('ok')) {
                $notification->forceFill([
                    'telegram_error' => 'Telegram rejected the notification (HTTP '.$response->status().').',
                ])->save();

                return false;
            }

            $notification->forceFill([
                'telegram_sent_at' => now(),
                'telegram_error' => null,
            ])->save();

            return true;
        } catch (Throwable $exception) {
            Log::warning('Telegram notification delivery failed.', [
                'notification_id' => $notification->id,
                'exception' => $exception::class,
            ]);
            $notification->forceFill(['telegram_error' => 'Telegram delivery failed.'])->save();

            return false;
        }
    }

    private function message(SystemNotification $notification): string
    {
        $parts = ["🔔 {$notification->title}"];
        if (filled($notification->message)) {
            $parts[] = trim((string) $notification->message);
        }
        if (filled($notification->action_url)) {
            $parts[] = "Open in the Hydrox Portal:\n{$notification->action_url}";
        }

        return mb_strimwidth(implode("\n\n", $parts), 0, 4096, '…');
    }
}
