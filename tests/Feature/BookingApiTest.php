<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Mail\BookingRequestReceived;
use App\Mail\NewBookingRequest;
use App\Services\MicrosoftGraphMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_wordpress_can_submit_a_booking_with_the_integration_token(): void
    {
        config(['services.hydrox_booking.token' => 'test-token']);

        $response = $this->withHeader('X-Booking-Token', 'test-token')
            ->postJson('/api/bookings', [
                'customer_name' => 'Hydrox Test Customer',
                'email' => 'customer@example.com',
                'phone' => '0418 000 000',
                'services' => ['Regular home cleaning', 'Carpet cleaning'],
                'extras' => ['Oven cleaning'],
                'frequency' => 'fortnightly',
                'schedule_flexible' => true,
                'address' => '1 Test Street',
                'suburb' => 'Melbourne',
                'postcode' => '3000',
                'preferred_date' => now()->addWeek()->toDateString(),
            ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'uploading');

        $this->assertDatabaseHas(Booking::class, [
            'customer_name' => 'Hydrox Test Customer',
            'source' => 'hydrox.au',
            'status' => 'uploading',
        ]);
    }

    public function test_booking_submission_rejects_an_invalid_token(): void
    {
        config(['services.hydrox_booking.token' => 'test-token']);

        $this->withHeader('X-Booking-Token', 'wrong-token')
            ->postJson('/api/bookings', [
                'customer_name' => 'Hydrox Test Customer',
                'email' => 'customer@example.com',
                'phone' => '0418 000 000',
                'service' => 'Regular house cleaning',
            ])
            ->assertUnauthorized();
    }

    public function test_booking_submission_is_unavailable_until_a_token_is_configured(): void
    {
        config(['services.hydrox_booking.token' => null]);

        $this->postJson('/api/bookings', [
            'customer_name' => 'Hydrox Test Customer',
            'email' => 'customer@example.com',
            'phone' => '0418 000 000',
            'service' => 'Regular house cleaning',
        ])->assertServiceUnavailable();

        $this->assertDatabaseCount(Booking::class, 0);
    }

    public function test_wordpress_can_upload_a_photo_and_finalize_the_request(): void
    {
        Storage::fake('local');
        Mail::fake();
        config(['services.hydrox_booking.token' => 'test-token']);
        config([
            'services.microsoft_graph.tenant_id' => null,
            'services.microsoft_graph.client_id' => null,
            'services.microsoft_graph.client_secret' => null,
            'services.microsoft_graph.sender' => null,
        ]);

        $booking = Booking::create([
            'reference' => 'HYD-20260728-ABCDE',
            'source' => 'hydrox.au WordPress',
            'status' => 'uploading',
            'customer_name' => 'Hydrox Test Customer',
            'email' => 'customer@example.com',
            'phone' => '0418 000 000',
            'service' => 'Regular home cleaning',
            'services' => ['Regular home cleaning'],
            'extras' => [],
            'frequency' => 'one-off',
            'schedule_flexible' => true,
            'address' => '1 Test Street',
            'suburb' => 'Melbourne',
            'postcode' => '3000',
        ]);

        $this->withHeader('X-Booking-Token', 'test-token')
            ->post('/api/bookings/'.$booking->reference.'/photos', [
                'photo' => UploadedFile::fake()->create('room.jpg', 500, 'image/jpeg'),
            ])
            ->assertCreated()
            ->assertJsonPath('count', 1);

        $this->withHeader('X-Booking-Token', 'test-token')
            ->postJson('/api/bookings/'.$booking->reference.'/finalize')
            ->assertOk()
            ->assertJsonPath('status', 'processing')
            ->assertJsonPath('photo_count', 1);

        $this->assertSame('processing', $booking->fresh()->status);
        Mail::assertSent(BookingRequestReceived::class, fn ($mail) => $mail->hasTo('customer@example.com'));
        Mail::assertSent(NewBookingRequest::class, 1);
    }

    public function test_microsoft_graph_can_send_portal_email_with_application_credentials(): void
    {
        Cache::forget('microsoft-graph-mail-token');
        config([
            'services.microsoft_graph.tenant_id' => 'tenant-id',
            'services.microsoft_graph.client_id' => 'client-id',
            'services.microsoft_graph.client_secret' => 'client-secret',
            'services.microsoft_graph.sender' => 'admin@hydrox.au',
        ]);

        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 3600,
            ]),
            'https://graph.microsoft.com/*' => Http::response(null, 202),
        ]);

        app(MicrosoftGraphMailService::class)->send(
            'customer@example.com',
            'Hydrox test email',
            '<p>Hydrox test message</p>'
        );

        Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMail')
            && $request->hasHeader('Authorization', 'Bearer test-access-token')
            && $request['message']['toRecipients'][0]['emailAddress']['address'] === 'customer@example.com');
    }

    public function test_a_twenty_first_photo_is_rejected(): void
    {
        Storage::fake('local');
        config(['services.hydrox_booking.token' => 'test-token']);
        $booking = Booking::create([
            'reference' => 'HYD-20260728-LIMIT',
            'source' => 'hydrox.au WordPress',
            'status' => 'uploading',
            'customer_name' => 'Hydrox Test Customer',
            'email' => 'customer@example.com',
            'phone' => '0418 000 000',
            'service' => 'Regular home cleaning',
            'services' => ['Regular home cleaning'],
            'frequency' => 'one-off',
            'schedule_flexible' => true,
            'address' => '1 Test Street',
            'suburb' => 'Melbourne',
            'postcode' => '3000',
        ]);

        for ($index = 1; $index <= 19; $index++) {
            $booking->photos()->create([
                'path' => "booking-photos/{$booking->id}/{$index}.jpg",
                'original_name' => "{$index}.jpg",
                'mime_type' => 'image/jpeg',
                'size' => 100,
            ]);
        }

        $this->withHeader('X-Booking-Token', 'test-token')
            ->post('/api/bookings/'.$booking->reference.'/photos', [
                'photo' => UploadedFile::fake()->create('twentieth.jpg', 500, 'image/jpeg'),
            ])
            ->assertCreated()
            ->assertJsonPath('count', 20);

        $this->withHeader('X-Booking-Token', 'test-token')
            ->post('/api/bookings/'.$booking->reference.'/photos', [
                'photo' => UploadedFile::fake()->create('extra.jpg', 500, 'image/jpeg'),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'A maximum of 20 photos is allowed.');
    }

    public function test_a_photo_over_ten_megabytes_is_rejected(): void
    {
        Storage::fake('local');
        config(['services.hydrox_booking.token' => 'test-token']);
        $booking = Booking::create([
            'reference' => 'HYD-20260728-SIZE1',
            'source' => 'hydrox.au WordPress',
            'status' => 'uploading',
            'customer_name' => 'Hydrox Test Customer',
            'email' => 'customer@example.com',
            'phone' => '0418 000 000',
            'service' => 'Regular home cleaning',
            'services' => ['Regular home cleaning'],
            'frequency' => 'one-off',
            'schedule_flexible' => true,
            'address' => '1 Test Street',
            'suburb' => 'Melbourne',
            'postcode' => '3000',
        ]);

        $this->withHeaders(['X-Booking-Token' => 'test-token', 'Accept' => 'application/json'])
            ->post('/api/bookings/'.$booking->reference.'/photos', [
                'photo' => UploadedFile::fake()->create('too-large.jpg', 10241, 'image/jpeg'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('photo');
    }
}
