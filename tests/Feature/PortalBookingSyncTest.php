<?php

namespace Tests\Feature;

use App\Actions\CreateBookingLeadAction;
use App\Mail\BookingRequestReceived;
use App\Models\Booking;
use App\Services\HydroxPortalBookingSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalBookingSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.hydrox_portal.url' => 'https://portal.hydrox.au',
            'services.hydrox_portal.token' => 'portal-secret-token-12345',
        ]);
    }

    public function test_successful_portal_sync_creates_lead_uploads_photos_and_finalizes(): void
    {
        Storage::fake('local');

        Http::fake([
            'https://portal.hydrox.au/api/bookings' => Http::response([
                'message' => 'Booking request created.',
                'reference' => 'HYD-PORTAL-001',
                'status' => 'uploading',
            ], 201),
            'https://portal.hydrox.au/api/bookings/HYD-PORTAL-001/photos' => Http::response([
                'message' => 'Photo uploaded.',
                'photo_id' => 999,
                'count' => 1,
            ], 201),
            'https://portal.hydrox.au/api/bookings/HYD-PORTAL-001/finalize' => Http::response([
                'message' => 'Booking request received and awaiting review.',
                'reference' => 'HYD-PORTAL-001',
                'status' => 'processing',
                'photo_count' => 1,
            ], 200),
        ]);

        $booking = Booking::create([
            'reference' => 'HYD-20260914-LOCAL1',
            'source' => 'hydrox.au Website',
            'status' => 'processing',
            'customer_name' => 'John Citizen',
            'email' => 'john@example.com',
            'phone' => '0412 345 678',
            'service' => 'Commercial Office Cleaning',
            'services' => ['Commercial Office Cleaning', 'Window Cleaning'],
            'frequency' => 'fortnightly',
            'schedule_flexible' => true,
            'address' => '456 Queen Street',
            'suburb' => 'Melbourne',
            'postcode' => '3000',
            'notes' => 'Please arrive before 9am',
        ]);

        $photoPath = 'booking-photos/test/sample.jpg';
        Storage::disk('local')->put($photoPath, 'fake-image-binary-data');
        $booking->photos()->create([
            'path' => $photoPath,
            'original_name' => 'sample.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
        ]);

        $service = app(HydroxPortalBookingSyncService::class);
        $result = $service->sync($booking);

        $this->assertTrue($result['success']);
        $this->assertSame('HYD-PORTAL-001', $result['portal_reference']);

        // Check Step 1: POST /api/bookings
        Http::assertSent(function (Request $request) use ($booking) {
            return $request->url() === 'https://portal.hydrox.au/api/bookings'
                && $request->hasHeader('X-Booking-Token', 'portal-secret-token-12345')
                && $request['external_reference'] === $booking->reference
                && $request['customer_name'] === 'John Citizen'
                && $request['email'] === 'john@example.com'
                && $request['phone'] === '0412 345 678'
                && $request['services'] === ['Commercial Office Cleaning', 'Window Cleaning']
                && $request['frequency'] === 'fortnightly'
                && $request['schedule_flexible'] === true
                && $request['address'] === '456 Queen Street'
                && $request['suburb'] === 'Melbourne'
                && $request['postcode'] === '3000';
        });

        // Check Step 2: POST /api/bookings/{reference}/photos
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://portal.hydrox.au/api/bookings/HYD-PORTAL-001/photos'
                && $request->hasHeader('X-Booking-Token', 'portal-secret-token-12345')
                && $request->isMultipart();
        });

        // Check Step 3: POST /api/bookings/{reference}/finalize with email suppression
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://portal.hydrox.au/api/bookings/HYD-PORTAL-001/finalize'
                && $request->hasHeader('X-Booking-Token', 'portal-secret-token-12345')
                && $request['send_customer_email'] === false
                && $request['send_admin_email'] === false
                && $request['send_emails'] === false;
        });

        // Check local database payload updated
        $freshBooking = $booking->fresh();
        $this->assertSame('synced', $freshBooking->payload['portal_sync']['status']);
        $this->assertSame('HYD-PORTAL-001', $freshBooking->payload['portal_sync']['portal_reference']);

        // Ensure local photos were NOT deleted
        $this->assertTrue(Storage::disk('local')->exists($photoPath));
    }

    public function test_portal_sync_is_idempotent_when_already_synced(): void
    {
        Http::fake();

        $booking = Booking::create([
            'reference' => 'HYD-20260914-LOCAL2',
            'customer_name' => 'Jane Citizen',
            'email' => 'jane@example.com',
            'phone' => '0412 999 888',
            'service' => 'House Cleaning',
            'suburb' => 'Richmond',
            'postcode' => '3121',
            'payload' => [
                'portal_sync' => [
                    'status' => 'synced',
                    'portal_reference' => 'HYD-EXISTING-999',
                ],
            ],
        ]);

        $service = app(HydroxPortalBookingSyncService::class);
        $result = $service->sync($booking);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['already_synced']);
        $this->assertSame('HYD-EXISTING-999', $result['portal_reference']);
        Http::assertNothingSent();
    }

    public function test_portal_sync_handles_401_unauthorized_gracefully(): void
    {
        Http::fake([
            'https://portal.hydrox.au/api/bookings' => Http::response(['message' => 'Invalid booking integration token.'], 401),
        ]);

        $booking = Booking::create([
            'reference' => 'HYD-20260914-LOCAL3',
            'customer_name' => 'Alice Test',
            'email' => 'alice@example.com',
            'phone' => '0400 000 001',
            'service' => 'Cleaning',
            'address' => '12 Test Lane',
            'suburb' => 'Docklands',
            'postcode' => '3008',
        ]);

        $service = app(HydroxPortalBookingSyncService::class);
        $result = $service->sync($booking);

        $this->assertFalse($result['success']);
        $this->assertDatabaseHas(Booking::class, ['id' => $booking->id]);
        $this->assertSame('failed', $booking->fresh()->payload['portal_sync']['status']);
        $this->assertSame(401, $booking->fresh()->payload['portal_sync']['http_status']);
    }

    public function test_portal_sync_handles_422_validation_error_gracefully(): void
    {
        Http::fake([
            'https://portal.hydrox.au/api/bookings' => Http::response(['message' => 'Validation error'], 422),
        ]);

        $booking = Booking::create([
            'reference' => 'HYD-20260914-LOCAL4',
            'customer_name' => 'Bob Test',
            'email' => 'bob@example.com',
            'phone' => '0400 000 002',
            'service' => 'Cleaning',
            'address' => '34 Test Way',
            'suburb' => 'Southbank',
            'postcode' => '3006',
        ]);

        $service = app(HydroxPortalBookingSyncService::class);
        $result = $service->sync($booking);

        $this->assertFalse($result['success']);
        $this->assertDatabaseHas(Booking::class, ['id' => $booking->id]);
        $this->assertSame('failed', $booking->fresh()->payload['portal_sync']['status']);
        $this->assertSame(422, $booking->fresh()->payload['portal_sync']['http_status']);
    }

    public function test_portal_sync_handles_500_server_error_gracefully(): void
    {
        Http::fake([
            'https://portal.hydrox.au/api/bookings' => Http::response('Internal Server Error', 500),
        ]);

        $booking = Booking::create([
            'reference' => 'HYD-20260914-LOCAL5',
            'customer_name' => 'Charlie Test',
            'email' => 'charlie@example.com',
            'phone' => '0400 000 003',
            'service' => 'Cleaning',
            'address' => '78 Test Rd',
            'suburb' => 'Carlton',
            'postcode' => '3053',
        ]);

        $service = app(HydroxPortalBookingSyncService::class);
        $result = $service->sync($booking);

        $this->assertFalse($result['success']);
        $this->assertDatabaseHas(Booking::class, ['id' => $booking->id]);
        $this->assertSame('failed', $booking->fresh()->payload['portal_sync']['status']);
    }

    public function test_portal_sync_handles_connection_failure_gracefully(): void
    {
        Http::fake([
            'https://portal.hydrox.au/api/bookings' => fn () => throw new ConnectionException('Connection timed out'),
        ]);

        $booking = Booking::create([
            'reference' => 'HYD-20260914-LOCAL6',
            'customer_name' => 'Dan Test',
            'email' => 'dan@example.com',
            'phone' => '0400 000 004',
            'service' => 'Cleaning',
            'address' => '99 Test St',
            'suburb' => 'Fitzroy',
            'postcode' => '3065',
        ]);

        $service = app(HydroxPortalBookingSyncService::class);
        $result = $service->sync($booking);

        $this->assertFalse($result['success']);
        $this->assertDatabaseHas(Booking::class, ['id' => $booking->id]);
        $this->assertSame('failed', $booking->fresh()->payload['portal_sync']['status']);
    }

    public function test_portal_sync_skips_when_token_or_url_is_missing(): void
    {
        Http::fake();
        config(['services.hydrox_portal.token' => null]);

        $booking = Booking::create([
            'reference' => 'HYD-20260914-LOCAL7',
            'customer_name' => 'Eva Test',
            'email' => 'eva@example.com',
            'phone' => '0400 000 005',
            'service' => 'Cleaning',
            'suburb' => 'Fitzroy',
            'postcode' => '3065',
        ]);

        $service = app(HydroxPortalBookingSyncService::class);
        $result = $service->sync($booking);

        $this->assertFalse($result['success']);
        Http::assertNothingSent();
    }

    public function test_portal_sync_does_not_fabricate_fake_postcode_when_missing(): void
    {
        Http::fake();

        $booking = Booking::create([
            'reference' => 'HYD-20260914-LOCAL8',
            'customer_name' => 'Frank Test',
            'email' => 'frank@example.com',
            'phone' => '0400 000 006',
            'service' => 'Cleaning',
            'address' => 'Some unknown location without digits',
            'suburb' => 'Nowhere',
            'postcode' => null,
        ]);

        $service = app(HydroxPortalBookingSyncService::class);
        $result = $service->sync($booking);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('postcode', $result['error']);
        Http::assertNothingSent();
    }

    public function test_action_saves_local_booking_even_if_portal_sync_fails(): void
    {
        Mail::fake();
        Http::fake([
            'https://portal.hydrox.au/api/bookings' => Http::response('Server Error', 500),
        ]);

        $action = app(CreateBookingLeadAction::class);
        $booking = $action->execute([
            'customer_name' => 'Grace Test',
            'email' => 'grace@example.com',
            'phone' => '0400 111 222',
            'service' => 'Carpet Cleaning',
            'suburb' => 'Hawthorn',
            'postcode' => '3122',
            'address' => '10 Glenferrie Rd',
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertDatabaseHas(Booking::class, [
            'id' => $booking->id,
            'customer_name' => 'Grace Test',
        ]);
        $this->assertSame('failed', $booking->fresh()->payload['portal_sync']['status']);
    }

    public function test_portal_finalize_endpoint_with_email_suppression_prevents_duplicate_customer_email(): void
    {
        Mail::fake();
        config(['services.hydrox_booking.token' => 'test-portal-token']);

        $booking = Booking::create([
            'reference' => 'HYD-20260914-SUPPRESS',
            'status' => 'uploading',
            'customer_name' => 'Henry Test',
            'email' => 'henry@example.com',
            'phone' => '0400 333 444',
            'service' => 'Office Cleaning',
            'services' => ['Office Cleaning'],
            'frequency' => 'one-off',
            'schedule_flexible' => true,
            'address' => '200 Collins St',
            'suburb' => 'Melbourne',
            'postcode' => '3000',
        ]);

        // When called with send_customer_email = false and send_emails = false
        $response = $this->withHeader('X-Booking-Token', 'test-portal-token')
            ->postJson('/api/bookings/'.$booking->reference.'/finalize', [
                'send_customer_email' => false,
                'send_admin_email' => false,
                'send_emails' => false,
            ]);

        $response->assertOk();
        $this->assertSame('processing', $booking->fresh()->status);

        // Assert customer email was NOT sent
        Mail::assertNotSent(BookingRequestReceived::class);
    }
}
