@extends('layouts.public')

@section('title', 'About Us | Hydrox Facility Management Melbourne')
@section('meta_description', 'Learn about Hydrox Facility Management Cleaning Services Pty. Ltd. Our mission, safety credentials, fully vetted staff, and Victoria-wide service commitment.')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <span class="inline-block px-3.5 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">About Hydrox</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">A Cleaning Partner Built on Trust & Excellence</h1>
        <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto">
            Professional facility management and commercial cleaning solutions throughout Melbourne and Victoria, backed by rigorous standards and dedicated management.
        </p>
    </div>
</section>

<!-- Company Overview -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 space-y-6">
                <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Who We Are</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 leading-tight">Elevating Facility Cleanliness Across Victoria</h2>
                <p class="text-base sm:text-lg text-slate-600 leading-relaxed">
                    Hydrox Facility Management Cleaning Services Pty. Ltd (ABN: 35 670 676 785) was founded with a clear objective: to provide businesses, educational campuses, medical institutions, and residential clients with reliable, honest, and spotless facility care.
                </p>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                    We believe that a facility provider shouldn't just be an occasional service; we are an essential partner in your daily operational health, safety, and brand presentation.
                </p>

                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                    <div>
                        <p class="text-3xl font-black text-[#0082c9]">100%</p>
                        <p class="text-xs text-slate-500 mt-1 font-semibold">Satisfaction Guaranteed</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-[#0082c9]">Full</p>
                        <p class="text-xs text-slate-500 mt-1 font-semibold">Public Liability Cover</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-[#0082c9]">100%</p>
                        <p class="text-xs text-slate-500 mt-1 font-semibold">Police-Checked Teams</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-[#0082c9]">24/7</p>
                        <p class="text-xs text-slate-500 mt-1 font-semibold">Client Support & Care</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6">
                <img src="{{ asset('images/E1C8726B-1C50-45B9-883D-A37A6B850FFF.png') }}" alt="Hydrox Facility Management Team" class="rounded-3xl shadow-xl border border-slate-200 w-full h-80 sm:h-96 object-cover">
            </div>
        </div>
    </div>
</section>

<!-- Values -->
<section class="py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Our Core Principles</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">What Sets Hydrox Apart</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-[#0082c9] flex items-center justify-center text-xl font-bold">🛡️</div>
                <h3 class="text-lg font-bold text-slate-900">Accountability & Verification</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Every shift is logged and audited. We utilize photographic shift completion reporting so facility managers and business owners have complete visibility into the work done.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">🌿</div>
                <h3 class="text-lg font-bold text-slate-900">Health & Eco-Conscious</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    We select cleaning chemicals that eliminate pathogens without generating pungent fumes or harmful VOC residues, protecting asthma sufferers, children, and pets.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">🤝</div>
                <h3 class="text-lg font-bold text-slate-900">Trained, Fairly Paid Crews</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Quality starts with happy, respected cleaners. All Hydrox team members and subcontractors are thoroughly onboarded, insured, and supported with ongoing skill development.
                </p>
            </div>
        </div>

        <!-- Accreditations Grid on About Us -->
        <div class="mt-20 pt-16 border-t border-slate-200">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Verified Standards</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2">Accreditations & Industry Compliance</h2>
                <p class="text-xs sm:text-sm text-slate-600 mt-2">Meeting and exceeding Australian workplace, safety, and government requirements.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-5 items-stretch text-left">
                <!-- 1. WorkCover -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold">🛡️</div>
                        <h3 class="font-bold text-base text-slate-900">WorkCover Covered</h3>
                        <p class="text-[11px] font-semibold text-amber-600">Workplace Safety</p>
                        <p class="text-xs text-slate-600 leading-relaxed">Protected under WorkCover insurance, ensuring safe and compliant delivery across all facilities.</p>
                    </div>
                    <div class="pt-4 mt-4 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-emerald-600">✓ Safety Protected</span>
                    </div>
                </div>

                <!-- 2. Labour Hire -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold">⚖️</div>
                        <h3 class="font-bold text-base text-slate-900">Labour Hire Compliant</h3>
                        <p class="text-[11px] font-semibold text-indigo-600">Industry Standards</p>
                        <p class="text-xs text-slate-600 leading-relaxed">Operating in accordance with Australian labour hire requirements, ensuring ethical, vetted staffing.</p>
                    </div>
                    <div class="pt-4 mt-4 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-emerald-600">✓ Industry Compliant</span>
                    </div>
                </div>

                <!-- 3. Cm3 Prequalified -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-[#0082c9] flex items-center justify-center text-lg font-bold">📋</div>
                        <h3 class="font-bold text-base text-slate-900">Cm3 Prequalified</h3>
                        <p class="text-[11px] font-semibold text-[#0082c9]">WHS Compliance</p>
                        <p class="text-xs text-slate-600 leading-relaxed">Demonstrating verified contractor WHS safety documentation and operational readiness.</p>
                    </div>
                    <div class="pt-4 mt-4 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-emerald-600">✓ Cm3 Compliant</span>
                    </div>
                </div>

                <!-- 4. NDIS Provider -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold">💜</div>
                        <h3 class="font-bold text-base text-slate-900">NDIS Provider</h3>
                        <p class="text-[11px] font-semibold text-purple-600">Disability Support</p>
                        <p class="text-xs text-slate-600 leading-relaxed">Supporting NDIS participants with reliable, respectful, and tailored domestic and commercial cleaning.</p>
                    </div>
                    <div class="pt-4 mt-4 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-emerald-600">✓ NDIS Services</span>
                    </div>
                </div>

                <!-- 5. DVA Provider -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">🎖️</div>
                        <h3 class="font-bold text-base text-slate-900">DVA Provider</h3>
                        <p class="text-[11px] font-semibold text-emerald-600">Veteran Support</p>
                        <p class="text-xs text-slate-600 leading-relaxed">Dependable cleaning support for eligible veterans through Department of Veterans' Affairs programs.</p>
                    </div>
                    <div class="pt-4 mt-4 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-emerald-600">✓ DVA Services</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-16 text-center">
            <a href="{{ route('booking.create') }}" class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-lg hover:bg-[#006da9] transition">
                Partner With Hydrox Today
            </a>
        </div>
    </div>
</section>
@endsection
