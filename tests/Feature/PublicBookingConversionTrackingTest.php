<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Services\HydroxPortalBookingSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicBookingConversionTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Http::fake();
    }

    public function test_booking_form_page_does_not_fire_conversion_event(): void
    {
        $response = $this->get('/booking');

        $response->assertOk();
        // Base Google tag should be present
        $response->assertSee('https://www.googletagmanager.com/gtag/js?id=AW-18428986459', false);
        $response->assertSee("gtag('config', 'AW-18428986459')", false);
        // Conversion snippet must NOT be present on page load
        $response->assertDontSee('bOSgCPbm6-0cENu10NNE');
    }

    public function test_validation_errors_do_not_fire_conversion_event(): void
    {
        $response = $this->post('/booking', [
            'customer_name' => '', // missing required name
            'email' => 'invalid-email',
            'phone' => '',
            'suburb' => '',
        ]);

        $response->assertSessionHasErrors(['customer_name', 'email', 'phone', 'suburb']);
        $this->assertDatabaseCount(Booking::class, 0);

        // Follow redirect back to booking form
        $followUp = $this->get('/booking');
        $followUp->assertDontSee('bOSgCPbm6-0cENu10NNE');
    }

    public function test_honeypot_trap_does_not_create_booking_or_fire_conversion(): void
    {
        $response = $this->post('/booking', [
            'booking_guard_field' => 'bot-filled-value',
            'customer_name' => 'Spam Bot',
            'email' => 'bot@example.com',
            'phone' => '0400000000',
            'suburb' => 'Melbourne',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseCount(Booking::class, 0);

        $home = $this->get('/');
        $home->assertDontSee('bOSgCPbm6-0cENu10NNE');
    }

    public function test_successful_booking_submission_redirects_and_fires_conversion_once(): void
    {
        $response = $this->post('/booking', [
            'customer_name' => 'Sarah Connor',
            'email' => 'sarah@example.com',
            'phone' => '0412 345 678',
            'service' => 'Commercial Cleaning',
            'suburb' => 'Richmond',
            'postcode' => '3121',
            'address' => '123 Bridge Road',
            'frequency' => 'one-off',
            'notes' => 'Please provide detailed quote',
        ]);

        $booking = Booking::firstOrFail();
        $this->assertSame('Sarah Connor', $booking->customer_name);

        $expectedUrl = route('booking.confirmation', [
            'reference' => $booking->reference,
            'new' => 1,
        ]);

        $response->assertRedirect($expectedUrl);

        // Follow redirect to confirmation page
        $confirmationResponse = $this->get($expectedUrl);
        $confirmationResponse->assertOk();

        // Exactly one base Google tag script in layout
        $content = $confirmationResponse->getContent();
        $this->assertSame(1, substr_count($content, 'https://www.googletagmanager.com/gtag/js?id=AW-18428986459'));

        // Conversion event script is rendered with exact send_to payload
        $confirmationResponse->assertSee("gtag('event', 'conversion', {'send_to': 'AW-18428986459/bOSgCPbm6-0cENu10NNE'})", false);
        $confirmationResponse->assertSee('hydrox_gads_conv_' . $booking->reference, false);
    }

    public function test_confirmation_page_does_not_fire_conversion_on_direct_or_later_visit(): void
    {
        $booking = Booking::create([
            'reference' => 'HYD-20260914-EXIST',
            'source' => 'hydrox.au Website',
            'status' => 'processing',
            'customer_name' => 'Old Customer',
            'email' => 'old@example.com',
            'phone' => '0400 000 111',
            'service' => 'General Cleaning',
            'suburb' => 'Melbourne',
            'postcode' => '3000',
            'created_at' => now()->subHours(2),
        ]);

        // Direct visit without 'new' query and without session flag
        $response = $this->get(route('booking.confirmation', $booking->reference));

        $response->assertOk();
        $response->assertDontSee('bOSgCPbm6-0cENu10NNE');
    }

    public function test_ajax_booking_submission_returns_confirmation_redirect(): void
    {
        $response = $this->postJson('/booking', [
            'customer_name' => 'AJAX Customer',
            'email' => 'ajax@example.com',
            'phone' => '0400 222 333',
            'service' => 'Residential Cleaning',
            'suburb' => 'South Yarra',
            'postcode' => '3141',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $booking = Booking::where('customer_name', 'AJAX Customer')->firstOrFail();
        $expectedUrl = route('booking.confirmation', [
            'reference' => $booking->reference,
            'new' => 1,
        ]);
        $response->assertJsonPath('redirect', $expectedUrl);

        // Confirmation page contains conversion tag
        $confirmation = $this->get($expectedUrl);
        $confirmation->assertOk();
        $confirmation->assertSee("gtag('event', 'conversion', {'send_to': 'AW-18428986459/bOSgCPbm6-0cENu10NNE'})", false);
    }
}
