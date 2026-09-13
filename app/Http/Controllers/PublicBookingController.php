<?php

namespace App\Http\Controllers;

use App\Actions\CreateBookingLeadAction;
use App\Models\Booking;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicBookingController extends Controller
{
    public function create(Request $request): View
    {
        $business = SystemSetting::businessInformation();
        $selectedService = $request->query('service', '');
        return view('pages.booking', compact('business', 'selectedService'));
    }

    public function store(Request $request, CreateBookingLeadAction $action): RedirectResponse|JsonResponse
    {
        // Honeypot spam trap
        if ($request->filled('booking_guard_field')) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Request received.'], 200)
                : redirect()->route('home');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['required', 'string', 'max:50'],
            'service' => ['nullable', 'string', 'max:191'],
            'services' => ['nullable', 'array', 'max:10'],
            'services.*' => ['string', 'max:191'],
            'suburb' => ['required', 'string', 'max:100'],
            'postcode' => ['nullable', 'regex:/^\d{4}$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'string', 'max:50'],
            'frequency' => ['nullable', 'string', 'in:one-off,weekly,fortnightly,monthly,not-sure'],
            'schedule_flexible' => ['nullable'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['file', 'max:10240', 'mimetypes:image/jpeg,image/png,image/webp,image/heic,image/heif'],
        ]);

        if (empty($validated['services']) && empty($validated['service'])) {
            $validated['services'] = ['General Cleaning'];
        }

        try {
            $photos = $request->file('photos', []);
            $booking = $action->execute($validated, is_array($photos) ? $photos : []);

            // One-time conversion trigger flag stored in session
            $request->session()->put('booking_just_submitted_' . $booking->reference, true);

            $confirmationUrl = route('booking.confirmation', [
                'reference' => $booking->reference,
                'new' => 1,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'reference' => $booking->reference,
                    'redirect' => $confirmationUrl,
                ]);
            }

            return redirect()->to($confirmationUrl);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Public booking submission error: ' . $e->getMessage(), [
                'exception' => $e::class,
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to save your booking at this moment. Please call 0418 222 477.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            return back()->withInput()->with('error', 'We encountered an issue saving your request. Please call 0418 222 477 or email admin@hydrox.au.');
        }
    }

    public function confirmation(Request $request, string $reference): View
    {
        $booking = Booking::where('reference', $reference)->firstOrFail();
        $business = SystemSetting::businessInformation();

        // Safe conversion firing: true upon confirmed booking creation (via session or recent 'new' flag)
        $justSubmittedSession = (bool) $request->session()->pull('booking_just_submitted_' . $booking->reference, false);
        $isRecent = $booking->created_at && $booking->created_at->diffInSeconds(now()) <= 300;
        $fireConversion = $justSubmittedSession || ($request->has('new') && $isRecent);

        return view('pages.confirmation', compact('booking', 'business', 'fireConversion'));
    }
}
