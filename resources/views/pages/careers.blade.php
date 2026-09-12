@extends('layouts.public')

@section('title', 'Careers & Subcontractor Opportunities | Hydrox Facility Management')
@section('meta_description', 'Join the Hydrox Facility Management contractor network. Competitive rates, timely payments, and flexible shifts across Melbourne.')

@section('content')
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <span class="inline-block px-3.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold uppercase tracking-wider">Subcontractor Network</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">Grow Your Cleaning Business With Hydrox</h1>
        <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto">
            We partner with reliable, quality-driven cleaning contractors, sole traders, and independent operators across Melbourne. Enjoy guaranteed timely payments and steady shift allocations.
        </p>
        <div class="pt-4">
            <a href="{{ route('subcontractor-onboardings.create') }}" class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-emerald-500 text-slate-950 font-extrabold text-sm shadow-xl shadow-emerald-500/20 hover:bg-emerald-400 transition transform hover:-translate-y-0.5">
                Apply for Subcontractor Onboarding →
            </a>
        </div>
    </div>
</section>

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Why Join Hydrox</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">A Partnership That Supports Your Success</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="w-12 h-12 rounded-xl bg-sky-100 text-[#0082c9] flex items-center justify-center text-xl font-bold">💳</div>
                <h3 class="text-lg font-bold text-slate-900">Guaranteed On-Time Payments</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Submit your shifts directly through our digital Staff Portal and enjoy predictable, verified payment cycles with clear remittance slips.
                </p>
            </div>

            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl font-bold">📍</div>
                <h3 class="text-lg font-bold text-slate-900">Sites Near Your Suburb</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    We match facility contracts with contractors based on geographic proximity, minimizing unpaid travel time and maximizing your billable hours.
                </p>
            </div>

            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="w-12 h-12 rounded-xl bg-sky-100 text-[#0082c9] flex items-center justify-center text-xl font-bold">📱</div>
                <h3 class="text-lg font-bold text-slate-900">Modern Portal & Clear Scopes</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    No messy paperwork. Access site scopes, access codes, timesheets, and invoices directly from your mobile device via the Hydrox Staff Portal.
                </p>
            </div>
        </div>

        <!-- Requirements -->
        <div class="mt-16 bg-gradient-to-r from-slate-900 to-[#061b35] rounded-3xl p-8 sm:p-12 text-white">
            <div class="grid lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8 space-y-4">
                    <h3 class="text-2xl font-bold">What You Need To Onboard</h3>
                    <p class="text-xs sm:text-sm text-slate-300">
                        To ensure client compliance across Melbourne, all subcontractors must provide:
                    </p>
                    <div class="grid sm:grid-cols-2 gap-3 text-xs text-slate-200 pt-2">
                        <div class="flex items-center gap-2"><span>✓</span> Australian Business Number (ABN)</div>
                        <div class="flex items-center gap-2"><span>✓</span> \$10M+ Public Liability Insurance</div>
                        <div class="flex items-center gap-2"><span>✓</span> Current National Police Check (under 12m)</div>
                        <div class="flex items-center gap-2"><span>✓</span> Photo ID (Driver Licence or Passport)</div>
                        <div class="flex items-center gap-2"><span>✓</span> Commercial cleaning equipment & transport</div>
                        <div class="flex items-center gap-2"><span>✓</span> Working With Children Check (optional/preferred)</div>
                    </div>
                </div>
                <div class="lg:col-span-4 text-center lg:text-right">
                    <a href="{{ route('subcontractor-onboardings.create') }}" class="inline-block px-8 py-4 rounded-xl bg-sky-400 text-slate-950 font-bold text-sm hover:bg-sky-300 shadow-xl transition">
                        Start Digital Onboarding
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
