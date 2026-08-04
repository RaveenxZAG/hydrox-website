<?php

namespace Tests\Feature;

use App\Services\SystemNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_system_notification_is_sent_to_the_configured_telegram_chat(): void
    {
        config()->set('services.telegram.bot_token', 'test-token');
        config()->set('services.telegram.chat_id', '-100123456');
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]])]);

        $notification = app(SystemNotificationService::class)->notify(
            'booking_request',
            'New booking request BK-100',
            'Alex requested office cleaning.',
            'https://portal.example/bookings/1',
            null,
            false
        );

        $this->assertNotNull($notification->fresh()->telegram_sent_at);
        $this->assertNull($notification->fresh()->telegram_error);
        Http::assertSent(function (Request $request): bool {
            return str_ends_with($request->url(), '/bottest-token/sendMessage')
                && $request['chat_id'] === '-100123456'
                && str_contains($request['text'], 'New booking request BK-100')
                && str_contains($request['text'], 'Alex requested office cleaning.')
                && str_contains($request['text'], 'https://portal.example/bookings/1');
        });
    }

    public function test_notifications_continue_when_telegram_is_not_configured(): void
    {
        config()->set('services.telegram.bot_token', null);
        config()->set('services.telegram.chat_id', null);
        Http::fake();

        $notification = app(SystemNotificationService::class)->notify('test', 'Portal event', sendEmail: false);

        $this->assertDatabaseHas('system_notifications', ['id' => $notification->id]);
        Http::assertNothingSent();
    }
}
