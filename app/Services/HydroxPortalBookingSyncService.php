<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class HydroxPortalBookingSyncService
{
    /**
     * Synchronize a website booking to the Hydrox Portal API.
     *
     * @return array{success: bool, portal_reference?: string|null, error?: string|null, already_synced?: bool}
     */
    public function sync(Booking $booking): array
    {
        $baseUrl = rtrim((string) config('services.hydrox_portal.url', 'https://portal.hydrox.au'), '/');
        $token = (string) config('services.hydrox_portal.token', '');

        if ($baseUrl === '' || $token === '') {
            Log::info("Hydrox Portal booking sync skipped for {$booking->reference}: Portal URL or token is not configured.");
            return [
                'success' => false,
                'error' => 'Portal integration is not configured.',
            ];
        }

        $payload = is_array($booking->payload) ? $booking->payload : [];
        $existingSync = $payload['portal_sync'] ?? null;

        // Idempotency check: if already fully synced, do not duplicate
        if (is_array($existingSync) && ($existingSync['status'] ?? null) === 'synced') {
            return [
                'success' => true,
                'portal_reference' => $existingSync['portal_reference'] ?? null,
                'already_synced' => true,
            ];
        }

        try {
            // Derive valid address, suburb, and 4-digit postcode strictly from customer input
            $addressData = $this->deriveAddressDetails($booking);
            if (! $addressData['valid']) {
                $errorMessage = "Cannot sync booking {$booking->reference} to Portal: {$addressData['reason']}";
                Log::warning($errorMessage);

                $payload['portal_sync'] = [
                    'status' => 'failed',
                    'error' => $addressData['reason'],
                    'attempted_at' => now()->toIso8601String(),
                ];
                $booking->forceFill(['payload' => $payload])->save();

                return [
                    'success' => false,
                    'error' => $addressData['reason'],
                ];
            }

            $portalReference = $existingSync['portal_reference'] ?? null;

            // Step 1: Create booking on Portal if not already created
            if (! $portalReference) {
                $postData = [
                    'source' => Str::limit($booking->source ?: 'hydrox.au Website', 100, ''),
                    'external_reference' => Str::limit($booking->reference, 191, ''),
                    'customer_name' => Str::limit(trim((string) $booking->customer_name), 191, ''),
                    'email' => Str::limit(trim((string) $booking->email), 191, ''),
                    'phone' => Str::limit(trim((string) $booking->phone), 50, ''),
                    'services' => $this->deriveServices($booking),
                    'extras' => is_array($booking->extras) ? array_values(array_slice($booking->extras, 0, 20)) : [],
                    'frequency' => $this->normalizeFrequency($booking->frequency),
                    'schedule_flexible' => (bool) $booking->schedule_flexible,
                    'preferred_date' => $booking->preferred_date ? $booking->preferred_date->format('Y-m-d') : null,
                    'preferred_time' => $booking->preferred_time ? Str::limit((string) $booking->preferred_time, 50, '') : null,
                    'address' => Str::limit($addressData['address'], 500, ''),
                    'suburb' => Str::limit($addressData['suburb'], 100, ''),
                    'postcode' => $addressData['postcode'],
                    'notes' => $booking->notes ? Str::limit((string) $booking->notes, 5000, '') : null,
                ];

                $response = Http::withHeaders([
                    'X-Booking-Token' => $token,
                    'Accept' => 'application/json',
                ])
                    ->timeout(15)
                    ->connectTimeout(5)
                    ->post("{$baseUrl}/api/bookings", $postData);

                if (! $response->successful()) {
                    $status = $response->status();
                    $errorText = Str::limit($response->body(), 200, '');
                    Log::warning("Portal booking creation failed for {$booking->reference} (HTTP {$status}): {$errorText}");

                    $payload['portal_sync'] = [
                        'status' => 'failed',
                        'http_status' => $status,
                        'error' => "HTTP {$status}",
                        'attempted_at' => now()->toIso8601String(),
                    ];
                    $booking->forceFill(['payload' => $payload])->save();

                    return [
                        'success' => false,
                        'error' => "Portal returned HTTP {$status}",
                    ];
                }

                $portalReference = $response->json('reference');
                if (! $portalReference) {
                    throw new \RuntimeException('Portal response did not contain a booking reference.');
                }

                $payload['portal_sync'] = [
                    'status' => 'uploading',
                    'portal_reference' => $portalReference,
                    'attempted_at' => now()->toIso8601String(),
                ];
                $booking->forceFill(['payload' => $payload])->save();
            }

            // Step 2: Upload photos if any
            $photos = $booking->photos()->take(20)->get();
            foreach ($photos as $photo) {
                if (Storage::disk('local')->exists($photo->path)) {
                    $fileContent = Storage::disk('local')->get($photo->path);
                    $filename = $photo->original_name ?: basename($photo->path);
                    $mimeType = $photo->mime_type ?: 'image/jpeg';

                    Http::withHeaders([
                        'X-Booking-Token' => $token,
                        'Accept' => 'application/json',
                    ])
                        ->timeout(15)
                        ->connectTimeout(5)
                        ->attach('photo', $fileContent, $filename, ['Content-Type' => $mimeType])
                        ->post("{$baseUrl}/api/bookings/{$portalReference}/photos");
                }
            }

            // Step 3: Finalize booking on Portal with email suppression
            $finalizeResponse = Http::withHeaders([
                'X-Booking-Token' => $token,
                'Accept' => 'application/json',
            ])
                ->timeout(15)
                ->connectTimeout(5)
                ->post("{$baseUrl}/api/bookings/{$portalReference}/finalize", [
                    'send_customer_email' => false,
                    'send_admin_email' => false,
                    'send_emails' => false,
                ]);

            if (! $finalizeResponse->successful()) {
                $status = $finalizeResponse->status();
                Log::warning("Portal booking finalization failed for {$booking->reference} (HTTP {$status})");

                $payload['portal_sync'] = [
                    'status' => 'partially_synced',
                    'portal_reference' => $portalReference,
                    'http_status' => $status,
                    'error' => "Finalize HTTP {$status}",
                    'attempted_at' => now()->toIso8601String(),
                ];
                $booking->forceFill(['payload' => $payload])->save();

                return [
                    'success' => false,
                    'portal_reference' => $portalReference,
                    'error' => "Portal finalize returned HTTP {$status}",
                ];
            }

            // Successfully finalized
            $payload['portal_sync'] = [
                'status' => 'synced',
                'portal_reference' => $portalReference,
                'synced_at' => now()->toIso8601String(),
            ];
            $booking->forceFill(['payload' => $payload])->save();

            return [
                'success' => true,
                'portal_reference' => $portalReference,
            ];
        } catch (Throwable $e) {
            Log::warning("Portal booking sync exception for {$booking->reference}: {$e->getMessage()}");

            $payload['portal_sync'] = [
                'status' => 'failed',
                'error' => Str::limit($e->getMessage(), 200, ''),
                'attempted_at' => now()->toIso8601String(),
            ];
            $booking->forceFill(['payload' => $payload])->save();

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Derive valid address, suburb, and 4-digit postcode strictly from customer input.
     *
     * @return array{valid: bool, address?: string, suburb?: string, postcode?: string, reason?: string}
     */
    private function deriveAddressDetails(Booking $booking): array
    {
        $address = trim((string) ($booking->address ?? ''));
        $suburb = trim((string) ($booking->suburb ?? ''));
        $postcode = trim((string) ($booking->postcode ?? ''));

        // If postcode not set directly, attempt regex extraction from address string
        if (! preg_match('/^\d{4}$/', $postcode)) {
            if ($address !== '' && preg_match('/\b(\d{4})\b/', $address, $matches)) {
                $postcode = $matches[1];
            }
        }

        if (! preg_match('/^\d{4}$/', $postcode)) {
            return [
                'valid' => false,
                'reason' => 'A valid 4-digit postcode could not be derived from customer input.',
            ];
        }

        // If suburb not set directly, attempt to derive it from address
        if ($suburb === '') {
            if ($address !== '') {
                // Check for Australian pattern e.g. "Suburb VIC 3000" or "Suburb, 3000"
                if (preg_match('/(?:,\s*|\s+)([A-Za-z\s]+?)\s+(?:VIC|NSW|QLD|WA|SA|TAS|ACT|NT)\s+\d{4}/i', $address, $subMatches)) {
                    $suburb = trim($subMatches[1]);
                } elseif (preg_match('/(?:,\s*|\s+)([A-Za-z\s]+?)\s+\d{4}/i', $address, $subMatches)) {
                    $suburb = trim($subMatches[1]);
                }
            }
        }

        if ($suburb === '') {
            return [
                'valid' => false,
                'reason' => 'A valid suburb could not be derived from customer input.',
            ];
        }

        // If address is empty, combine suburb and postcode if available
        if ($address === '') {
            $address = trim("{$suburb} {$postcode}");
        }

        if ($address === '') {
            return [
                'valid' => false,
                'reason' => 'An address could not be derived from customer input.',
            ];
        }

        return [
            'valid' => true,
            'address' => $address,
            'suburb' => $suburb,
            'postcode' => $postcode,
        ];
    }

    /**
     * Derive a valid non-empty array of services.
     *
     * @return array<int, string>
     */
    private function deriveServices(Booking $booking): array
    {
        $services = is_array($booking->services) ? $booking->services : [];

        if (empty($services) && ! empty($booking->service)) {
            $services = array_filter(array_map('trim', explode(',', $booking->service)));
        }

        if (empty($services)) {
            $services = ['General Cleaning'];
        }

        $unique = array_values(array_unique(array_filter(array_map(fn ($s) => Str::limit(trim((string) $s), 191, ''), $services))));

        return array_slice($unique, 0, 12);
    }

    /**
     * Normalize frequency to Portal-accepted values:
     * one-off, weekly, fortnightly, monthly, not-sure
     */
    private function normalizeFrequency(?string $frequency): string
    {
        $normalized = Str::lower(trim((string) $frequency));

        return match ($normalized) {
            'weekly' => 'weekly',
            'fortnightly', 'biweekly' => 'fortnightly',
            'monthly' => 'monthly',
            'not-sure', 'not_sure', 'flexible' => 'not-sure',
            default => 'one-off',
        };
    }
}
