@extends('layouts.public')

@section('title', 'Contact Us | Hydrox Facility Management Melbourne')
@section('meta_description', 'Get in touch with Hydrox Facility Management. Call 0418 222 477 or email admin@hydrox.au for commercial and domestic cleaning enquiries in Melbourne.')

@section('content')
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3">
        <span class="inline-block px-3.5 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">Get In Touch</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">We Would Love to Hear From You</h1>
        <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto">
            Have a question about our cleaning capabilities, request a tender proposal, or need immediate assistance? Our Melbourne team is ready to help.
        </p>
    </div>
</section>

<section class="py-10 lg:py-14 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-8 lg:gap-10 items-stretch">
            <!-- Left: Contact Details & Support Photo Card -->
            <div class="lg:col-span-5 flex flex-col justify-between bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-lg">
                <div>
                    <!-- Photo Header -->
                    <div class="relative rounded-2xl overflow-hidden mb-6 border border-slate-100 shadow-sm">
                        <img src="{{ asset('images/BADC4CCA-FF3A-4FB0-86C9-FDF62C04F6F0.png') }}" alt="Hydrox Customer Support Representative" class="w-full h-44 sm:h-48 object-cover object-center">
                        <div class="absolute bottom-2.5 left-2.5 right-2.5 px-3 py-1.5 rounded-lg bg-slate-900/85 backdrop-blur-sm text-white text-[11px] font-medium flex items-center justify-between shadow-sm">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Melbourne Support Desk
                            </span>
                            <span class="text-slate-300 text-[10px]">Mon – Sun 7am – 8pm</span>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Reach Out Directly</h2>
                        <p class="text-xs text-slate-500 mt-1">Available 7 days across Melbourne and Victorian regions.</p>
                    </div>

                    <div class="space-y-3">
                        <a href="tel:0418222477" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-slate-50 hover:bg-sky-50/60 border border-slate-200/80 hover:border-sky-200 transition group">
                            <div class="w-10 h-10 rounded-lg bg-white text-[#0082c9] shadow-sm flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition border border-slate-100">📞</div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Direct Phone</p>
                                <p class="text-sm font-bold text-slate-900 group-hover:text-[#0082c9] transition">0418 222 477</p>
                            </div>
                        </a>

                        <a href="mailto:admin@hydrox.au" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-slate-50 hover:bg-sky-50/60 border border-slate-200/80 hover:border-sky-200 transition group">
                            <div class="w-10 h-10 rounded-lg bg-white text-[#0082c9] shadow-sm flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition border border-slate-100">✉️</div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email Enquiries</p>
                                <p class="text-sm font-bold text-slate-900 group-hover:text-[#0082c9] transition">admin@hydrox.au</p>
                            </div>
                        </a>

                        <div class="flex items-center gap-3.5 p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                            <div class="w-10 h-10 rounded-lg bg-white text-[#0082c9] shadow-sm flex items-center justify-center text-lg flex-shrink-0 border border-slate-100">📍</div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Head Office</p>
                                <p class="text-sm font-bold text-slate-900">Melbourne, Victoria, Australia</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fast Quote Callout at bottom -->
                <div class="mt-6 p-4 rounded-2xl bg-sky-50/80 border border-sky-100 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-900">Need immediate pricing?</p>
                        <p class="text-[11px] text-slate-500">Get a scoped estimate in minutes</p>
                    </div>
                    <a href="{{ route('booking.create') }}" class="px-4 py-2 rounded-xl bg-[#0082c9] hover:bg-[#006da9] text-white text-xs font-bold shadow-sm shadow-sky-600/20 transition whitespace-nowrap">
                        Request Quote →
                    </a>
                </div>
            </div>

            <!-- Right: Contact Form Card -->
            <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 lg:p-10 border border-slate-200 shadow-lg flex flex-col justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-1">Send Us a Message</h3>
                    <p class="text-xs text-slate-500 mb-6">Fill in your contact details and our team will get back to you promptly.</p>

                    @if (session('success'))
                        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">
                            ✓ {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                            <ul class="list-disc pl-5 space-y-1 text-xs">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="text" name="contact_guard_field" class="hidden" tabindex="-1" autocomplete="off">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Your Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. David Miller" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Company Name</label>
                                <input type="text" name="company" value="{{ old('company') }}" placeholder="e.g. Apex Logistics Pty Ltd" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Phone Number *</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="04XX XXX XXX" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Email Address *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required placeholder="name@company.com.au" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">How Can We Help? *</label>
                            <textarea name="message" rows="4" required placeholder="Tell us about your facility, cleaning requirements, or questions..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="w-full py-3.5 px-8 rounded-xl bg-[#0082c9] hover:bg-[#006da9] text-white font-bold text-sm shadow-lg shadow-sky-600/25 transition">
                            Send Enquiry
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
