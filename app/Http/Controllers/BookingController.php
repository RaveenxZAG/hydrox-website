<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
        return view('bookings.show', compact('booking'));
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'contacted', 'quoted', 'confirmed', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $booking->update($validated);

        return back()->with('success', 'Booking updated.');
    }

    public function storeFromWebsite(Request $request)
    {
        $configuredToken = (string) config('services.hydrox_booking.token');
        if ($configuredToken !== '' && ! hash_equals($configuredToken, (string) $request->header('X-Booking-Token'))) {
            return response()->json(['message' => 'Invalid booking integration token.'], 401);
        }

        $validated = $request->validate([
            'source' => ['nullable', 'string', 'max:100'],
            'external_reference' => ['nullable', 'string', 'max:191'],
            'customer_name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['required', 'string', 'max:50'],
            'service' => ['required', 'string', 'max:191'],
            'preferred_date' => ['nullable', 'date'],
            'preferred_time' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'suburb' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $booking = Booking::create([
            ...$validated,
            'reference' => 'HYD-'.now()->format('ymd').'-'.Str::upper(Str::random(5)),
            'source' => $validated['source'] ?? 'hydrox.au',
            'status' => 'new',
            'payload' => $request->except(['password', 'token']),
        ]);

        return response()->json([
            'message' => 'Booking received.',
            'reference' => $booking->reference,
            'status' => $booking->status,
        ], 201);
    }
}
