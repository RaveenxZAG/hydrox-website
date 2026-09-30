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
        $response->assertDontSee('b0SgCPbm6-0cENuI0NNE');
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
        $followUp->assertDontSee('b0SgCPbm6-0cENuI0NNE');
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
        $home->assertDontSee('b0SgCPbm6-0cENuI0NNE');
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
        $confirmationResponse->assertSee("gtag('event', 'conversion', {'send_to': 'AW-18428986459/b0SgCPbm6-0cENuI0NNE'})", false);
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
        $response->assertDontSee('b0SgCPbm6-0cENuI0NNE');
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
        $confirmation->assertSee("gtag('event', 'conversion', {'send_to': 'AW-18428986459/b0SgCPbm6-0cENuI0NNE'})", false);
    }

    public function test_residential_cleaning_page_displays_specialized_services_and_images(): void
    {
        $response = $this->get(route('services.residential'));
        $response->assertOk();

        // Check for sections and images
        $response->assertSee('Carpet & Upholstery Cleaning', false);
        $response->assertSee('Pressure Washing & Surface Cleaning', false);
        $response->assertSee('Residential Window Cleaning', false);

        $response->assertSee('images/residential-carpet-cleaning.jpg', false);
        $response->assertSee('images/residential-pressure-washing.jpg', false);
        $response->assertSee('images/residential-window-cleaning.jpg', false);

        // Check for booking links with preselected service query parameters
        $response->assertSee('service=Carpet%20Cleaning', false);
        $response->assertSee('service=Upholstery%20Cleaning', false);
        $response->assertSee('service=Pressure%20Washing', false);
        $response->assertSee('service=Window%20Cleaning', false);
    }

    public function test_booking_form_displays_carpet_pressure_and_window_cleaning_options(): void
    {
        $response = $this->get(route('booking.create'));
        $response->assertOk();

        $response->assertSee('value="Carpet Cleaning"', false);
        $response->assertSee('value="Upholstery Cleaning"', false);
        $response->assertSee('value="Pressure Washing"', false);
        $response->assertSee('value="Window Cleaning"', false);
    }

    public function test_booking_submission_succeeds_with_new_specialized_services(): void
    {
        foreach (['Carpet Cleaning', 'Upholstery Cleaning', 'Pressure Washing', 'Window Cleaning'] as $service) {
            $response = $this->postJson('/booking', [
                'customer_name' => "Customer for {$service}",
                'email' => 'client@example.com',
                'phone' => '0412 999 888',
                'service' => $service,
                'suburb' => 'Brighton',
                'postcode' => '3186',
            ]);

            $response->assertOk();
            $response->assertJsonPath('success', true);

            $this->assertDatabaseHas('bookings', [
                'customer_name' => "Customer for {$service}",
                'service' => $service,
            ]);
        }
    }

    public function test_duplicate_submission_reuses_booking_and_does_not_fire_google_ads_conversion(): void
    {
        $payload = [
            'customer_name' => 'Michael Scott',
            'email' => 'michael@dundermifflin.com',
            'phone' => '0412 111 222',
            'service' => 'Commercial Cleaning',
            'suburb' => 'Scranton',
            'postcode' => '3000',
            'address' => '1725 Slough Avenue',
        ];

        // 1. Initial submission
        $firstResponse = $this->post('/booking', $payload);
        $this->assertDatabaseCount(Booking::class, 1);

        $booking = Booking::firstOrFail();
        $firstUrl = route('booking.confirmation', [
            'reference' => $booking->reference,
            'new' => 1,
        ]);
        $firstResponse->assertRedirect($firstUrl);

        // Follow first redirect: conversion script MUST be present
        $firstConfirmation = $this->get($firstUrl);
        $firstConfirmation->assertOk();
        $firstConfirmation->assertSee("gtag('event', 'conversion', {'send_to': 'AW-18428986459/b0SgCPbm6-0cENuI0NNE'})", false);

        // 2. Second rapid/duplicate submission with the same customer details
        $secondResponse = $this->post('/booking', $payload);

        // DB count must still be 1 (no duplicate booking created)
        $this->assertDatabaseCount(Booking::class, 1);

        // Redirect URL must NOT contain 'new=1'
        $secondUrl = route('booking.confirmation', [
            'reference' => $booking->reference,
        ]);
        $secondResponse->assertRedirect($secondUrl);

        // Follow second redirect: conversion script MUST NOT be present
        $secondConfirmation = $this->get($secondUrl);
        $secondConfirmation->assertOk();
        $secondConfirmation->assertDontSee("gtag('event', 'conversion', {'send_to': 'AW-18428986459/b0SgCPbm6-0cENuI0NNE'})", false);
    }

    public function test_duplicate_ajax_submission_returns_existing_reference_without_new_param(): void
    {
        $payload = [
            'customer_name' => 'Dwight Schrute',
            'email' => 'dwight@beetfarm.com',
            'phone' => '0412 333 444',
            'service' => 'Residential Cleaning',
            'suburb' => 'Honesdale',
            'postcode' => '3000',
        ];

        // First AJAX post
        $res1 = $this->postJson('/booking', $payload);
        $res1->assertOk();
        $res1->assertJsonPath('success', true);
        $ref1 = $res1->json('reference');

        // Second AJAX post
        $res2 = $this->postJson('/booking', $payload);
        $res2->assertOk();
        $res2->assertJsonPath('success', true);
        $ref2 = $res2->json('reference');

        $this->assertSame($ref1, $ref2);
        $this->assertDatabaseCount(Booking::class, 1);

        // First redirect URL had new=1, second must not
        $this->assertStringContainsString('new=1', $res1->json('redirect'));
        $this->assertStringNotContainsString('new=1', $res2->json('redirect'));
    }
}

