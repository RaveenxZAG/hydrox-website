<?php

namespace App\Actions;

use App\Models\Booking;
use App\Models\BookingPhoto;
use App\Services\BookingEmailService;
use App\Services\HydroxPortalBookingSyncService;
use App\Services\SystemNotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CreateBookingLeadAction
{
    public function __construct(
        private readonly BookingEmailService $bookingEmailService,
        private readonly SystemNotificationService $systemNotificationService,
        private readonly HydroxPortalBookingSyncService $portalSyncService
    ) {}

    /**
     * Create a booking or quote lead from user input.
     *
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile> $photos
     */
    public function execute(array $data, array $photos = []): Booking
    {
        $services = $data['services'] ?? [];
        if (empty($services) && ! empty($data['service'])) {
            $services = [$data['service']];
        }

        $serviceString = ! empty($services) ? implode(', ', (array) $services) : ($data['service'] ?? 'General Cleaning');
        $frequency = ! empty($data['frequency']) ? $data['frequency'] : 'one-off';
        $flexible = isset($data['schedule_flexible']) ? (bool) $data['schedule_flexible'] : true;

        $suburb = trim((string) ($data['suburb'] ?? ''));
        $postcode = trim((string) ($data['postcode'] ?? ''));
        $address = trim((string) ($data['address'] ?? ''));

        if ($address === '' && ($suburb !== '' || $postcode !== '')) {
            $address = trim("{$suburb} {$postcode}");
        }

        $booking = Booking::create([
            'reference' => $this->newReference(),
            'source' => $data['source'] ?? 'hydrox.au Website',
            'external_reference' => $data['external_reference'] ?? ('WEB-' . Str::upper(Str::random(8))),
            'status' => 'processing',
            'customer_name' => trim((string) ($data['customer_name'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'service' => $serviceString,
            'services' => (array) $services,
            'extras' => $data['extras'] ?? [],
            'frequency' => $frequency,
            'schedule_flexible' => $flexible,
            'preferred_date' => ! empty($data['preferred_date']) ? $data['preferred_date'] : null,
            'preferred_time' => ! empty($data['preferred_time']) ? $data['preferred_time'] : null,
            'address' => $address ?: null,
            'suburb' => $suburb ?: null,
            'postcode' => $postcode ?: null,
            'notes' => ! empty($data['notes']) ? trim((string) $data['notes']) : null,
            'payload' => $data,
            'finalized_at' => now(),
        ]);

        foreach ($photos as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = $file->store("booking-photos/{$booking->id}", 'local');
                $booking->photos()->create([
                    'path' => $path,
                    'original_name' => Str::limit($file->getClientOriginalName(), 240, ''),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Send email notifications
        try {
            $this->bookingEmailService->send($booking->fresh('photos'));
        } catch (\Throwable $exception) {
            report($exception);
            $booking->forceFill(['email_error' => $exception->getMessage()])->save();
        }

        // Send admin internal system notification
        try {
            $this->systemNotificationService->notify(
                'booking_request',
                "New Quote/Booking Request: {$booking->reference}",
                "Name: {$booking->customer_name}\n"
                    ."Email: {$booking->email}\n"
                    ."Phone: {$booking->phone}\n"
                    ."Services: {$booking->service}\n"
                    .'Frequency: '.($booking->frequency ?: 'Not specified')."\n"
                    .'Location: '.trim(implode(', ', array_filter([$booking->address, $booking->suburb, $booking->postcode])))."\n"
                    .'Notes: '.($booking->notes ?: 'None')."\n"
                    .'Photos: '.$booking->photos()->count(),
                route('bookings.show', $booking),
                $booking,
                false
            );
        } catch (\Throwable $exception) {
            report($exception);
        }

        // Sync to Hydrox Portal
        try {
            $this->portalSyncService->sync($booking->fresh('photos'));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $booking;
    }

    private function newReference(): string
    {
        do {
            $reference = 'HYD-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (Booking::where('reference', $reference)->exists());

        return $reference;
    }
}
