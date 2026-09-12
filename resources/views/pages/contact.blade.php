@extends('layouts.public')

@section('title', 'Contact Us | Hydrox Facility Management Melbourne')
@section('meta_description', 'Get in touch with Hydrox Facility Management. Call 0418 222 477 or email admin@hydrox.au for commercial and domestic cleaning enquiries in Melbourne.')

@section('content')
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <span class="inline-block px-3.5 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">Get In Touch</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">We Would Love to Hear From You</h1>
        <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto">
            Have a question about our cleaning capabilities, request a tender proposal, or need immediate assistance? Our Melbourne team is ready to help.
        </p>
    </div>
</section>

<section class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12">
            <!-- Contact Info -->
            <div class="lg:col-span-5 space-y-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-2">Reach Out Directly</h2>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Whether you need regular workplace facility maintenance or a one-off deep clean, we are available 7 days a week.
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="flex items-start gap-4 p-5 bg-white rounded-2xl border border-slate-200 shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-sky-50 text-[#0082c9] flex items-center justify-center text-xl flex-shrink-0">📞</div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Phone</p>
                            <a href="tel:0418222477" class="text-lg font-bold text-slate-900 hover:text-[#0082c9] transition">0418 222 477</a>
                            <p class="text-xs text-slate-500 mt-0.5">Mon – Sun: 7:00am – 8:00pm</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-5 bg-white rounded-2xl border border-slate-200 shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-sky-50 text-[#0082c9] flex items-center justify-center text-xl flex-shrink-0">✉️</div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Email Enquiries</p>
                            <a href="mailto:admin@hydrox.au" class="text-lg font-bold text-slate-900 hover:text-[#0082c9] transition">admin@hydrox.au</a>
                            <p class="text-xs text-slate-500 mt-0.5">Prompt response within 2 business hours</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-5 bg-white rounded-2xl border border-slate-200 shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-sky-50 text-[#0082c9] flex items-center justify-center text-xl flex-shrink-0">📍</div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Head Office</p>
                            <p class="text-base font-bold text-slate-900">Melbourne, Victoria, Australia</p>
                            <p class="text-xs text-slate-500 mt-0.5">Servicing all Greater Melbourne & Victorian regions</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-sky-50 rounded-2xl border border-sky-100 space-y-2">
                    <p class="font-bold text-sm text-slate-900">Looking for pricing instead?</p>
                    <p class="text-xs text-slate-600">Use our rapid quote form for an immediate service scope and estimate.</p>
                    <a href="{{ route('booking.create') }}" class="inline-block mt-2 font-bold text-xs text-[#0082c9] hover:underline">
                        Go to Quote Request Form →
                    </a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-3xl p-8 sm:p-10 border border-slate-200 shadow-lg">
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Send Us a Message</h3>
                    <p class="text-xs text-slate-500 mb-8">Fill in your contact details and our team will get back to you promptly.</p>

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

                    <form action="{{ route('contact.send') }}" method="POST" class="space-y-5">
                        @csrf
                        <input type="text" name="contact_guard_field" class="hidden" tabindex="-1" autocomplete="off">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Your Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. David Miller" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Company Name</label>
                                <input type="text" name="company" value="{{ old('company') }}" placeholder="e.g. Apex Logistics Pty Ltd" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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
                            <textarea name="message" rows="5" required placeholder="Tell us about your facility, cleaning requirements, or questions..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="w-full py-4 px-8 rounded-xl bg-[#0082c9] hover:bg-[#006da9] text-white font-bold text-sm shadow-lg shadow-sky-600/30 transition">
                            Send Enquiry
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
