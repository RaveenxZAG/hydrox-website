@extends('layouts.public')

@section('title', 'Get a Free Cleaning Quote | Hydrox Facility Management')
@section('meta_description', 'Request a fast, no-obligation cleaning quote from Hydrox Facility Management. Commercial, residential, NDIS, and specialized cleaning services across Melbourne.')

@section('content')
<div class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-12 border-b border-slate-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3">
        <span class="inline-block px-3 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">Fast & Free Quote</span>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">Request Your Cleaning Quote</h1>
        <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto">
            Tell us about your requirements. Our Melbourne operations team will review availability and send you a transparent, custom estimate.
        </p>
    </div>
</div>

<div class="py-12 sm:py-16 bg-slate-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-xl border border-slate-200">
            @if (session('error'))
                <div class="mb-6 rounded-2xl bg-rose-50 p-4 border border-rose-200 text-rose-800 text-sm">
                    <p class="font-bold mb-1">{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl bg-rose-50 p-4 border border-rose-200 text-rose-800 text-sm">
                    <p class="font-bold mb-1">Please review the following:</p>
                    <ul class="list-disc pl-5 space-y-1 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('booking.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8" x-data="{
                selectedService: '{{ old('service', $selectedService ?? 'Commercial Cleaning') }}',
                flexibleSchedule: true,
                photoCount: 0
            }">
                @csrf
                <input type="text" name="booking_guard_field" class="hidden" tabindex="-1" autocomplete="off">

                <!-- Step 1: Service Selection -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-8 h-8 rounded-full bg-sky-100 text-[#0082c9] font-black text-sm flex items-center justify-center">1</span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">What service do you need?</h2>
                            <p class="text-xs text-slate-500">Choose the primary service you are enquiring about.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @php
                            $servicesList = [
                                ['id' => 'Commercial Cleaning', 'label' => 'Commercial Cleaning', 'icon' => '🏢'],
                                ['id' => 'Residential Cleaning', 'label' => 'Residential Cleaning', 'icon' => '🏡'],
                                ['id' => 'NDIS Cleaning', 'label' => 'NDIS & DVA Cleaning', 'icon' => '🤝'],
                                ['id' => 'Aged Care & Medical', 'label' => 'Aged Care / Medical', 'icon' => '🏥'],
                                ['id' => 'Industrial & Warehouse', 'label' => 'Industrial / Warehouse', 'icon' => '🏭'],
                                ['id' => 'School Cleaning', 'label' => 'School & Childcare', 'icon' => '🏫'],
                                ['id' => 'Lawn Care & Gardening', 'label' => 'Lawn & Gardening', 'icon' => '🌿'],
                                ['id' => 'Concreting', 'label' => 'Concreting & Pressure', 'icon' => '🧱'],
                                ['id' => 'Other Facility Request', 'label' => 'Other Request', 'icon' => '✨'],
                            ];
                        @endphp

                        @foreach ($servicesList as $s)
                            <label class="relative flex flex-col items-center justify-center text-center p-3 sm:p-4 rounded-2xl border cursor-pointer transition select-none"
                                   :class="selectedService === '{{ $s['id'] }}' ? 'border-[#0082c9] bg-sky-50 text-[#0082c9] ring-2 ring-[#0082c9]/20' : 'border-slate-200 hover:border-slate-300 text-slate-700 bg-white'">
                                <input type="radio" name="service" value="{{ $s['id'] }}" x-model="selectedService" class="sr-only">
                                <span class="text-2xl mb-1">{{ $s['icon'] }}</span>
                                <span class="text-xs font-bold">{{ $s['label'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <!-- Frequency Selector -->
                    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-slate-600 mr-2">Frequency:</span>
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-medium cursor-pointer transition">
                            <input type="radio" name="frequency" value="one-off" checked class="text-[#0082c9] focus:ring-[#0082c9]">
                            <span>One-off Clean</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-medium cursor-pointer transition">
                            <input type="radio" name="frequency" value="weekly" class="text-[#0082c9] focus:ring-[#0082c9]">
                            <span>Weekly</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-medium cursor-pointer transition">
                            <input type="radio" name="frequency" value="fortnightly" class="text-[#0082c9] focus:ring-[#0082c9]">
                            <span>Fortnightly</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-medium cursor-pointer transition">
                            <input type="radio" name="frequency" value="not-sure" class="text-[#0082c9] focus:ring-[#0082c9]">
                            <span>Not sure yet</span>
                        </label>
                    </div>
                </div>

                <!-- Step 2: Location & Timing -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-8 h-8 rounded-full bg-sky-100 text-[#0082c9] font-black text-sm flex items-center justify-center">2</span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Where and when is it needed?</h2>
                            <p class="text-xs text-slate-500">Service address or suburb in Victoria.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        <div class="sm:col-span-8">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Suburb *</label>
                            <input type="text" name="suburb" value="{{ old('suburb') }}" required placeholder="e.g. Richmond, Melbourne, Dandenong" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Postcode</label>
                            <input type="text" name="postcode" value="{{ old('postcode') }}" maxlength="4" placeholder="e.g. 3121" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                        </div>
                        <div class="sm:col-span-12">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Street Address <span class="text-slate-400 font-normal lowercase">(optional for initial quote)</span></label>
                            <input type="text" name="address" value="{{ old('address') }}" placeholder="Street number and name" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                        </div>
                    </div>

                    <!-- Timing & Date -->
                    <div class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="schedule_flexible" value="1" x-model="flexibleSchedule" checked class="rounded text-[#0082c9] focus:ring-[#0082c9]">
                            <span class="text-sm font-semibold text-slate-800">My date and time are flexible</span>
                        </label>

                        <div x-show="!flexibleSchedule" x-transition class="grid sm:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Preferred Date</label>
                                <input type="date" name="preferred_date" min="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Preferred Time of Day</label>
                                <select name="preferred_time" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                                    <option value="Morning">Morning (8am - 12pm)</option>
                                    <option value="Afternoon">Afternoon (12pm - 5pm)</option>
                                    <option value="Evening / After Hours">Evening / After Hours</option>
                                    <option value="Any time">Any time</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Contact Details & Notes -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-8 h-8 rounded-full bg-sky-100 text-[#0082c9] font-black text-sm flex items-center justify-center">3</span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Your Contact Details</h2>
                            <p class="text-xs text-slate-500">We will use this to send you the quote and confirm details.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Full Name *</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" required placeholder="e.g. Sarah Jenkins" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Phone Number *</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="04XX XXX XXX" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="name@domain.com.au" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Additional Notes <span class="text-slate-400 font-normal lowercase">(optional)</span></label>
                            <textarea name="notes" rows="3" placeholder="Tell us about size, specific rooms, key priorities, or access instructions..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <!-- Photos Upload (Optional) -->
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Add Photos <span class="text-slate-400 font-normal lowercase">(optional, up to 10 photos)</span></label>
                        <div class="relative border-2 border-dashed border-slate-300 hover:border-sky-400 rounded-2xl p-4 text-center cursor-pointer transition bg-slate-50/50">
                            <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp,image/heic,image/heif"
                                   @change="photoCount = $event.target.files.length"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="space-y-1">
                                <span class="text-2xl">📸</span>
                                <p class="text-xs font-bold text-slate-700" x-text="photoCount > 0 ? photoCount + ' photos selected' : 'Upload photos from phone or computer'"></p>
                                <p class="text-[11px] text-slate-400">JPG, PNG, WebP or phone photos up to 10MB each</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-200">
                    <button type="submit" class="w-full py-4 px-8 rounded-2xl bg-[#0082c9] hover:bg-[#006da9] text-white font-extrabold text-base shadow-xl shadow-sky-600/30 transition transform hover:-translate-y-0.5">
                        Get Free Quote Now
                    </button>
                    <div class="mt-3 flex items-center justify-center gap-4 text-xs text-slate-500 text-center">
                        <span>🔒 100% Secure & Confidential</span>
                        <span>•</span>
                        <span>⚡ Rapid Melbourne Response</span>
                        <span>•</span>
                        <span>🛡️ Zero Obligation</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
