<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                'service' => 'Regular house cleaning',
                'preferred_date' => now()->addWeek()->toDateString(),
            ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'new');

        $this->assertDatabaseHas(Booking::class, [
            'customer_name' => 'Hydrox Test Customer',
            'source' => 'hydrox.au',
            'status' => 'new',
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
}
