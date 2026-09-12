@extends('layouts.public')

@section('title', 'Quote Request Received | Hydrox Facility Management')
@section('meta_description', 'Your cleaning quote request has been received by Hydrox Facility Management. Our Melbourne team will review your details shortly.')

@section('content')
<div class="py-16 sm:py-24 bg-slate-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl p-8 sm:p-12 shadow-xl border border-slate-200 text-center space-y-6">
            <!-- Success Icon -->
            <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-3xl font-black shadow-inner">
                ✓
            </div>

            <div>
                <span class="inline-block px-3.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-wider mb-2">Request Safely Received</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Thank You, {{ $booking->customer_name }}!</h1>
                <p class="text-slate-600 text-sm mt-2 max-w-md mx-auto">
                    We have received your quote request and assigned it to our operations team for review.
                </p>
            </div>

            <!-- Booking Reference Badge -->
            <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 inline-block text-left w-full max-w-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#0082c9]">Your Request Reference</p>
                        <p class="text-xl font-black text-slate-900 mt-0.5 tracking-tight font-mono">{{ $booking->reference }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-sky-200 text-[#0082c9] text-xs font-bold">
                        Under Review
                    </span>
                </div>
            </div>

            <!-- Summary Details -->
            <div class="text-left border-t border-b border-slate-100 py-4 text-xs space-y-2 text-slate-600 max-w-md mx-auto">
                <div class="flex justify-between">
                    <span class="font-bold text-slate-700">Service:</span>
                    <span>{{ $booking->service }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-bold text-slate-700">Location:</span>
                    <span>{{ $booking->suburb }} {{ $booking->postcode }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-bold text-slate-700">Timing:</span>
                    <span>{{ $booking->schedule_flexible ? 'Flexible schedule' : ($booking->preferred_date?->format('d M Y') . ' (' . $booking->preferred_time . ')') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-bold text-slate-700">Email Receipt:</span>
                    <span>Sent to {{ $booking->email }}</span>
                </div>
            </div>

            <div class="bg-slate-50 rounded-2xl p-4 text-xs text-slate-600 text-left space-y-2 max-w-md mx-auto">
                <p class="font-bold text-slate-800">What happens next?</p>
                <p>1. Our operations team reviews your facility scope and schedule availability.</p>
                <p>2. We contact you via phone (<strong>{{ $booking->phone }}</strong>) or email with your transparent price estimate.</p>
                <p>3. Once approved, we lock in your service date with our verified cleaners.</p>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4">
                <a href="{{ route('home') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-300 font-bold text-xs text-slate-700 hover:bg-slate-50 transition">
                    Return to Homepage
                </a>
                <a href="tel:0418222477" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#0082c9] text-white font-bold text-xs shadow-md shadow-sky-600/20 hover:bg-[#006da9] transition">
                    Call 0418 222 477
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if ($fireConversion)
    <!-- Google Ads Conversion Tracking (Fired only once on genuine backend acceptance) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof gtag === 'function') {
                gtag('event', 'conversion', {
                    'send_to': 'AW-18428986459/bOSgCPbm6-0cENu10NNE',
                    'value': 1.0,
                    'currency': 'AUD'
                });
                console.log('Google Ads conversion event sent for {{ $booking->reference }}');
            }
        });
    </script>
@endif
@endpush
