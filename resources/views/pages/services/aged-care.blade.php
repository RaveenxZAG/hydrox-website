@extends('layouts.public')

@section('title', 'Aged Care & Medical Facility Cleaning Melbourne | Hydrox Facility Management')
@section('meta_description', 'High-standard sanitization and infection-control cleaning for medical clinics, allied health facilities, and aged care centers across Victoria.')

@section('content')
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <span class="inline-block px-3.5 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">Clinical & Care Sanitation</span>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">Aged Care & Medical Facility Cleaning</h1>
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                    Protecting vulnerable residents and medical patients requires uncompromising infection prevention. Hydrox delivers hospital-grade hygiene, color-coded microfiber protocols, and clinical surface disinfection across Melbourne.
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('booking.create', ['service' => 'Aged Care & Medical']) }}" class="px-8 py-4 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-xl shadow-sky-600/30 hover:bg-[#006da9] transition">
                        Request Healthcare Scope
                    </a>
                    <a href="tel:0418222477" class="px-6 py-4 rounded-xl bg-white/10 text-white font-bold text-sm border border-white/20 hover:bg-white/15 transition flex items-center gap-2">
                        📞 0418 222 477
                    </a>
                </div>
            </div>
            <div class="lg:col-span-5">
                <img src="{{ asset('images/293D37B7-4B0A-4617-A899-0A9CAA5C9188.png') }}" alt="Aged Care and Medical Cleaning" class="rounded-3xl shadow-2xl border border-white/10 w-full object-cover h-80 sm:h-96">
            </div>
        </div>
    </div>
</section>

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12">
            <div class="lg:col-span-8 space-y-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">Infection Control That Meets National Standards</h2>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                        Healthcare environments demand far more than general surface tidying. Our teams follow stringent Australian infection control principles, preventing cross-contamination between consultation suites, surgical rooms, patient recovery areas, and common resident spaces.
                    </p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Medical & Aged Care Scope</h3>
                    <div class="grid sm:grid-cols-2 gap-4 text-xs sm:text-sm text-slate-700">
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Hospital-grade TGA-approved disinfectants</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Strict color-coded microfiber cloth & mop system</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Clinical examination beds & treatment surface sanitizing</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Waiting room seating, play corners & reception desks</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Accessible ensuite sanitization & slip-prevention floors</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>High-touch sanitization: door hardware, handrails & elevator buttons</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-sky-50 rounded-3xl p-6 sm:p-8 border border-sky-100 sticky top-28">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Healthcare Compliance</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-1 mb-3">Consultation & Quote</h3>
                    <p class="text-xs text-slate-600 mb-6">Schedule an on-site audit to build a tailored cleaning matrix for your facility accreditation.</p>

                    <a href="{{ route('booking.create', ['service' => 'Aged Care & Medical']) }}" class="w-full block text-center py-3.5 px-6 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-md hover:bg-[#006da9] transition mb-3">
                        Request Healthcare Quote
                    </a>
                    <a href="tel:0418222477" class="w-full block text-center py-3.5 px-6 rounded-xl bg-white border border-slate-300 text-slate-800 font-bold text-sm hover:bg-slate-50 transition">
                        Call 0418 222 477
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
