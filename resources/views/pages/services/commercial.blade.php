@extends('layouts.public')

@section('title', 'Commercial Cleaning Services Melbourne | Hydrox Facility Management')
@section('meta_description', 'Professional commercial and office cleaning services in Melbourne. Custom schedules, police-checked cleaners, and high-standard hygiene.')

@section('content')
<!-- Service Hero -->
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <span class="inline-block px-3.5 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">Commercial Excellence</span>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">Commercial Cleaning Services in Melbourne</h1>
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                    Keep your workplace pristine, healthy, and welcoming. Hydrox Facility Management provides reliable daily, weekly, or after-hours commercial cleaning customized to your business schedule.
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('booking.create', ['service' => 'Commercial Cleaning']) }}" class="px-8 py-4 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-xl shadow-sky-600/30 hover:bg-[#006da9] transition">
                        Get Commercial Quote
                    </a>
                    <a href="tel:0418222477" class="px-6 py-4 rounded-xl bg-white/10 text-white font-bold text-sm border border-white/20 hover:bg-white/15 transition flex items-center gap-2">
                        📞 0418 222 477
                    </a>
                </div>
            </div>
            <div class="lg:col-span-5">
                <img src="{{ asset('images/08DAF5B0-3CEE-464C-B334-84A930649E27.png') }}" alt="Commercial Cleaning" class="rounded-3xl shadow-2xl border border-white/10 w-full object-cover h-80 sm:h-96">
            </div>
        </div>
    </div>
</section>

<!-- Content & Scope -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12">
            <div class="lg:col-span-8 space-y-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">Spotless Workplaces That Inspire Productivity</h2>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                        A clean office does more than create a great first impression for visiting clients; it protects employee health, prevents the spread of workplace illness, and fosters focus and pride. Our trained commercial cleaning specialists handle everything from executive offices to high-traffic retail spaces.
                    </p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">What Our Commercial Cleaning Includes</h3>
                    <div class="grid sm:grid-cols-2 gap-4 text-xs sm:text-sm text-slate-700">
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Daily workstation and desk surface sanitization</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Kitchen, kitchenette, and breakroom hygiene</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Restroom deep cleaning, stocking & disinfection</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Carpet vacuuming & steam cleaning</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Hard floor mopping, buffing & maintenance</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Waste disposal, bin liners & recycling sorting</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Internal glass partitions & entry doors</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>High-touch touchpoint sanitizing (handles, switches)</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Sectors & Facilities We Serve</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs font-semibold text-slate-700">
                        <div class="p-3 bg-white border border-slate-200 rounded-xl">Corporate Offices</div>
                        <div class="p-3 bg-white border border-slate-200 rounded-xl">Retail Stores & Showrooms</div>
                        <div class="p-3 bg-white border border-slate-200 rounded-xl">Medical & Allied Health</div>
                        <div class="p-3 bg-white border border-slate-200 rounded-xl">Real Estate & Display Suites</div>
                        <div class="p-3 bg-white border border-slate-200 rounded-xl">Gyms & Fitness Centres</div>
                        <div class="p-3 bg-white border border-slate-200 rounded-xl">Co-Working Facilities</div>
                    </div>
                </div>
            </div>

            <!-- Sidebar CTA Box -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-sky-50 rounded-3xl p-6 sm:p-8 border border-sky-100 sticky top-28">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Quick Commercial Quote</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-1 mb-3">Custom Cleaning Plan For Your Workplace</h3>
                    <p class="text-xs text-slate-600 mb-6">Receive an itemized quote tailored to your floorplan, frequency, and after-hours specifications.</p>

                    <a href="{{ route('booking.create', ['service' => 'Commercial Cleaning']) }}" class="w-full block text-center py-3.5 px-6 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-md hover:bg-[#006da9] transition mb-3">
                        Request Commercial Quote
                    </a>
                    <a href="tel:0418222477" class="w-full block text-center py-3.5 px-6 rounded-xl bg-white border border-slate-300 text-slate-800 font-bold text-sm hover:bg-slate-50 transition">
                        Call 0418 222 477
                    </a>

                    <div class="mt-6 pt-6 border-t border-sky-200/60 space-y-2 text-xs text-slate-600">
                        <p class="flex items-center gap-2"><span>🛡️</span> Comprehensive Public Liability Insurance</p>
                        <p class="flex items-center gap-2"><span>👮</span> 100% Police-Checked Staff</p>
                        <p class="flex items-center gap-2"><span>🕒</span> Flexible After-Hours Schedules</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
