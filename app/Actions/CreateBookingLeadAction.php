<?php

namespace App\Actions;

use App\Models\Booking;
use App\Models\BookingPhoto;
use App\Services\BookingEmailService;
use App\Services\HydroxPortalBookingSyncService;
use App\Services\SystemNotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $customerName = trim((string) ($data['customer_name'] ?? ''));
        $email = Str::lower(trim((string) ($data['email'] ?? '')));
        $phone = trim((string) ($data['phone'] ?? ''));
        $digitsOnlyPhone = preg_replace('/\D+/', '', $phone);

        // Deduplication & atomic lock key based on normalized customer identifiers and service
        $lockKey = 'booking_lead_' . md5($email . '|' . $digitsOnlyPhone . '|' . Str::lower($serviceString) . '|' . Str::lower($suburb));

        $executeCreation = function () use (
            $data, $photos, $services, $serviceString, $frequency, $flexible,
            $address, $suburb, $postcode, $customerName, $email, $phone, $digitsOnlyPhone
        ) {
            // Check for duplicate submission within the last 5 minutes
            $recentDuplicate = $this->findRecentDuplicate($email, $phone, $digitsOnlyPhone, $serviceString, $suburb);

            if ($recentDuplicate) {
                Log::warning('Duplicate booking prevented; reusing existing reference', [
                    'existing_reference' => $recentDuplicate->reference,
                    'customer_name' => $customerName,
                    'email' => $email,
                    'phone' => $phone,
                    'service' => $serviceString,
                ]);

                $recentDuplicate->wasRecentlyCreated = false;
                $recentDuplicate->is_duplicate = true;

                return $recentDuplicate;
            }

            $booking = DB::transaction(function () use ($data, $photos, $services, $serviceString, $frequency, $flexible, $address, $suburb, $postcode, $customerName, $email, $phone) {
                $booking = Booking::create([
                    'reference' => $this->newReference(),
                    'source' => $data['source'] ?? 'hydrox.au Website',
                    'external_reference' => $data['external_reference'] ?? ('WEB-' . Str::upper(Str::random(8))),
                    'status' => 'processing',
                    'customer_name' => $customerName,
                    'email' => $email,
                    'phone' => $phone,
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

                return $booking;
            }, 5);

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
        };

        try {
            return Cache::lock($lockKey, 10)->block(6, $executeCreation);
        } catch (\Throwable $lockException) {
            Log::info('Cache lock bypassed during booking submission: ' . $lockException->getMessage());
            return $executeCreation();
        }
    }

    /**
     * Search for a recent matching booking from the same customer within the duplicate window (5 minutes).
     */
    private function findRecentDuplicate(string $email, string $phone, string $digitsOnlyPhone, string $serviceString, string $suburb): ?Booking
    {
        if (empty($email) && empty($phone) && empty($digitsOnlyPhone)) {
            return null;
        }

        $window = now()->subMinutes(5);

        return Booking::where(function ($query) use ($email, $phone, $digitsOnlyPhone) {
                if (! empty($email)) {
                    $query->whereRaw('LOWER(email) = ?', [$email]);
                }
                if (! empty($phone)) {
                    if (! empty($email)) {
                        $query->orWhere('phone', $phone);
                    } else {
                        $query->where('phone', $phone);
                    }
                }
                if (! empty($digitsOnlyPhone) && strlen($digitsOnlyPhone) >= 8) {
                    $query->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', '') = ?", [$digitsOnlyPhone]);
                }
            })
            ->whereRaw('LOWER(service) = ?', [Str::lower($serviceString)])
            ->where('created_at', '>=', $window)
            ->latest('id')
            ->first();
    }

    private function newReference(): string
    {
        do {
            $reference = 'HYD-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (Booking::where('reference', $reference)->exists());

        return $reference;
    }
}
