<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingPhoto;
use App\Services\BookingEmailService;
use App\Services\SystemNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('reference', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('service', 'like', $term);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $booking->load('photos');

        return view('bookings.show', compact('booking'));
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['processing', 'contacted', 'quoted', 'confirmed', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $booking->update($validated);

        return back()->with('success', 'Booking updated.');
    }

    public function storeFromWebsite(Request $request): JsonResponse
    {
        if ($failure = $this->integrationFailure($request)) {
            return $failure;
        }

        if (! $request->has('services') && $request->filled('service')) {
            $request->merge(['services' => [$request->string('service')->toString()]]);
        }

        $validated = $request->validate([
            'source' => ['nullable', 'string', 'max:100'],
            'external_reference' => ['nullable', 'string', 'max:191'],
            'customer_name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['required', 'string', 'max:50'],
            'services' => ['required', 'array', 'min:1', 'max:12'],
            'services.*' => ['required', 'string', 'distinct', 'max:191'],
            'extras' => ['nullable', 'array', 'max:20'],
            'extras.*' => ['required', 'string', 'distinct', 'max:191'],
            'frequency' => ['required', Rule::in(['one-off', 'weekly', 'fortnightly', 'monthly', 'not-sure'])],
            'schedule_flexible' => ['required', 'boolean'],
            'preferred_date' => ['nullable', 'date'],
            'preferred_time' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'suburb' => ['required', 'string', 'max:100'],
            'postcode' => ['required', 'regex:/^\d{4}$/'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $booking = Booking::create([
            ...$validated,
            'reference' => $this->newReference(),
            'service' => implode(', ', $validated['services']),
            'source' => $validated['source'] ?? 'hydrox.au',
            'status' => 'uploading',
            'payload' => $request->except(['password', 'token']),
        ]);

        return response()->json([
            'message' => 'Booking request created.',
            'reference' => $booking->reference,
            'status' => 'uploading',
        ], 201);
    }

    public function uploadPhoto(Request $request, Booking $booking): JsonResponse
    {
        if ($failure = $this->integrationFailure($request)) {
            return $failure;
        }

        if (! in_array($booking->status, ['uploading', 'processing'], true)) {
            return response()->json(['message' => 'This booking request no longer accepts photos.'], 409);
        }

        if ($booking->photos()->count() >= 20) {
            return response()->json(['message' => 'A maximum of 20 photos is allowed.'], 422);
        }

        $validated = $request->validate([
            'photo' => [
                'required',
                'file',
                'max:10240',
                'mimetypes:image/jpeg,image/png,image/webp,image/heic,image/heif',
            ],
        ]);

        $file = $validated['photo'];
        $path = $file->store("booking-photos/{$booking->id}", 'local');
        $photo = $booking->photos()->create([
            'path' => $path,
            'original_name' => Str::limit($file->getClientOriginalName(), 240, ''),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'message' => 'Photo uploaded.',
            'photo_id' => $photo->id,
            'count' => $booking->photos()->count(),
        ], 201);
    }

    public function finalize(Request $request, Booking $booking, BookingEmailService $emails): JsonResponse
    {
        if ($failure = $this->integrationFailure($request)) {
            return $failure;
        }

        if ($booking->status === 'uploading') {
            $booking->update(['status' => 'processing', 'finalized_at' => now()]);

            $sendEmails = $request->boolean('send_emails', true);
            $sendCustomer = $sendEmails && $request->boolean('send_customer_email', true);
            $sendAdmin = $sendEmails && $request->boolean('send_admin_email', true);

            if ($sendCustomer || $sendAdmin) {
                $emails->send($booking->fresh('photos'), $sendCustomer, $sendAdmin);
            }

            app(SystemNotificationService::class)->notify(
                'booking_request',
                "New booking request {$booking->reference}",
                "Name: {$booking->customer_name}\n"
                    ."Email: {$booking->email}\n"
                    ."Phone: {$booking->phone}\n"
                    .'Services: '.implode(', ', $booking->services ?? [$booking->service])."\n"
                    .'Extras: '.(implode(', ', $booking->extras ?? []) ?: 'None')."\n"
                    .'Frequency: '.($booking->frequency ?: 'Not specified')."\n"
                    .'Preferred date: '.($booking->preferred_date?->format('d M Y') ?: 'Flexible')."\n"
                    .'Preferred time: '.($booking->preferred_time ?: 'Flexible')."\n"
                    .'Address: '.trim(implode(', ', array_filter([$booking->address, $booking->suburb, $booking->postcode])))."\n"
                    .'Notes: '.($booking->notes ?: 'None')."\n"
                    .'Photos: '.$booking->photos()->count(),
                route('bookings.show', $booking),
                $booking,
                false
            );
        }

        return response()->json([
            'message' => 'Booking request received and awaiting review.',
            'reference' => $booking->reference,
            'status' => 'processing',
            'photo_count' => $booking->photos()->count(),
        ]);
    }

    public function photo(Booking $booking, BookingPhoto $photo): StreamedResponse
    {
        abort_unless($photo->booking_id === $booking->id && Storage::disk('local')->exists($photo->path), 404);

        return Storage::disk('local')->response(
            $photo->path,
            $photo->original_name,
            ['Content-Type' => $photo->mime_type, 'Content-Disposition' => 'inline']
        );
    }

    public function resendEmails(Booking $booking, BookingEmailService $emails): RedirectResponse
    {
        $emails->send($booking->fresh('photos'));

        return back()->with(
            $booking->fresh()->email_error ? 'error' : 'success',
            $booking->fresh()->email_error ? 'The request was saved, but one or more emails still failed.' : 'Booking emails sent.'
        );
    }

    private function integrationFailure(Request $request): ?JsonResponse
    {
        $configuredToken = (string) config('services.hydrox_booking.token');
        if ($configuredToken === '') {
            return response()->json(['message' => 'Booking integration is not configured.'], 503);
        }

        if (! hash_equals($configuredToken, (string) $request->header('X-Booking-Token'))) {
            return response()->json(['message' => 'Invalid booking integration token.'], 401);
        }

        return null;
    }

    private function newReference(): string
    {
        do {
            $reference = 'HYD-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (Booking::where('reference', $reference)->exists());

        return $reference;
    }
}
