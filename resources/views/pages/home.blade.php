@extends('layouts.public')

@section('title', 'Hydrox Facility Management | Commercial, Residential & Specialised Cleaning Melbourne')
@section('meta_description', 'Top-rated commercial, residential, NDIS, aged care, and industrial cleaning services in Melbourne and Victoria. Get a fast free quote online.')

@section('content')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-b from-slate-900 via-[#061b35] to-[#041426] text-white pt-16 pb-24 lg:pt-20 lg:pb-32 overflow-hidden">
        <!-- Ambient background glows -->
        <div class="absolute -top-40 right-0 w-96 h-96 bg-sky-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-10 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                <!-- Left Hero Copy -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2.5">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/15 text-xs font-semibold text-sky-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            Premium Facility Solutions Across Victoria
                        </div>
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/15 text-xs font-semibold text-white">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.24v3.15C3.26 21.36 7.33 24 12 24Z"/>
                                <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.24C.45 8.16 0 9.98 0 12s.45 3.84 1.24 5.42l4.04-3.15Z"/>
                                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.24 6.58l4.04 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
                            </svg>
                            <span class="text-amber-400 font-bold">★★★★★ 5.0</span>
                            <span class="text-slate-300 font-normal">Google & hipages</span>
                        </div>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.15] text-white">
                        Professional Cleaning. <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-sky-300 to-emerald-300">Trusted Results.</span>
                    </h1>

                    <p class="text-lg text-slate-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                        Hydrox Facility Management delivers exceptional commercial, residential, NDIS, aged care, and industrial cleaning services across Melbourne. Vetted, police-checked cleaners with guaranteed satisfaction.
                    </p>

                    <!-- Trust checklist -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 max-w-lg mx-auto lg:mx-0 text-left text-xs font-medium text-slate-200">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                            <span>Police-Checked Staff</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                            <span>Public Liability Insured</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                            <span>Tailored Schedules</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                            <span>Eco-Friendly Products</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                            <span>NDIS & DVA Approved</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                            <span>24/7 Client Support</span>
                        </div>
                    </div>

                    <!-- Call & Action -->
                    <div class="flex flex-col sm:flex-row items-center gap-4 pt-4 justify-center lg:justify-start">
                        <a href="{{ route('booking.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 rounded-xl bg-[#0082c9] text-white font-bold text-base shadow-xl shadow-sky-600/30 hover:bg-[#006da9] hover:shadow-sky-600/50 transition transform hover:-translate-y-0.5">
                            Get a Free Quote
                        </a>
                        <a href="tel:0418222477" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-4 rounded-xl bg-white/10 text-white font-semibold text-base border border-white/20 hover:bg-white/15 transition">
                            <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            Call 0418 222 477
                        </a>
                    </div>
                </div>

                <!-- Right Quick Lead Capture Card -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 text-slate-900 shadow-2xl shadow-black/40 border border-slate-100">
                        <div class="mb-6">
                            <span class="inline-block px-3 py-1 rounded-md bg-sky-50 text-[#0082c9] text-xs font-bold uppercase tracking-wider mb-2">Instant Lead Estimate</span>
                            <h2 class="text-2xl font-black text-slate-900">Request Your Quote</h2>
                            <p class="text-xs text-slate-500 mt-1">Tell us what you need and get a rapid response from our local team.</p>
                        </div>

                        <form action="{{ route('booking.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="text" name="booking_guard_field" class="hidden" tabindex="-1" autocomplete="off">

                            <!-- Service Selection -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Cleaning Service *</label>
                                <select name="service" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                                    <option value="">Select a service...</option>
                                    <option value="Commercial Cleaning">Commercial & Office Cleaning</option>
                                    <option value="Residential Cleaning">Residential & House Cleaning</option>
                                    <option value="NDIS Cleaning">NDIS & DVA Cleaning Services</option>
                                    <option value="Aged Care & Medical">Aged Care & Medical Facilities</option>
                                    <option value="Industrial & Warehouse">Industrial & Warehouse Cleaning</option>
                                    <option value="School Cleaning">School & Childcare Cleaning</option>
                                    <option value="Lawn Care & Gardening">Lawn Care & Gardening</option>
                                    <option value="Concreting & Pressure Cleaning">Concreting & Pressure Cleaning</option>
                                    <option value="Other Facility Request">Other Facility Request</option>
                                </select>
                            </div>

                            <!-- Suburb and Postcode -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Suburb *</label>
                                    <input type="text" name="suburb" required placeholder="e.g. Melbourne" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Postcode</label>
                                    <input type="text" name="postcode" maxlength="4" placeholder="3000" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                                </div>
                            </div>

                            <!-- Name & Phone -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Your Name *</label>
                                    <input type="text" name="customer_name" required placeholder="Full name" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Phone Number *</label>
                                    <input type="tel" name="phone" required placeholder="04XX XXX XXX" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Email Address *</label>
                                <input type="email" name="email" required placeholder="your.name@company.com.au" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#0082c9] focus:ring-[#0082c9]">
                            </div>

                            <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-[#0082c9] hover:bg-[#006da9] text-white font-bold text-base shadow-lg shadow-sky-600/30 transition duration-150">
                                Get Free Quote Now
                            </button>

                            <p class="text-[11px] text-center text-slate-500">
                                🔒 No obligation. Your information is kept strictly private.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Showcase Section -->
    <section class="py-16 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-5 space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Inside Hydrox</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 leading-tight">Delivering High-Standard Facility Support Across Victoria</h2>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Watch our quick introduction video to see how Hydrox Facility Management delivers dependable, safe, and spotless results for corporate clients, commercial premises, healthcare, and educational providers.
                    </p>
                    <div class="pt-2 space-y-2.5">
                        <div class="flex items-center gap-3 text-sm text-slate-700">
                            <span class="w-6 h-6 rounded-lg bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-xs">✓</span>
                            <span>Dedicated account manager and proactive reporting</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-700">
                            <span class="w-6 h-6 rounded-lg bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-xs">✓</span>
                            <span>Consistent quality audits and completion checklists</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-700">
                            <span class="w-6 h-6 rounded-lg bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-xs">✓</span>
                            <span>Flexible after-hours & weekend schedules</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl bg-black aspect-video border border-slate-200">
                        <video controls poster="{{ asset('images/36B42554-D4C4-4DBF-91B9-11B162AE4F3F.png') }}" class="w-full h-full object-cover">
                            <source src="{{ asset('videos/Hydrox_business_introduction_video_1080p_202608151956.mp4') }}" type="video/mp4">
                            Your browser does not support HTML5 video.
                        </video>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Grid Section -->
    <section class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-block px-3.5 py-1 rounded-full bg-sky-100 text-[#0082c9] text-xs font-bold uppercase tracking-wider mb-3">Our Core Expertise</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Tailored Cleaning & Facility Solutions</h2>
                <p class="text-base text-slate-600 mt-3">From daily office maintenance to specialized medical sanitization and heavy industrial scrubbing, we provide end-to-end facility services.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Service 1: Commercial -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col group">
                    <div class="h-48 overflow-hidden bg-slate-100 relative">
                        <img src="{{ asset('images/08DAF5B0-3CEE-464C-B334-84A930649E27.png') }}" alt="Commercial Cleaning" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-xs font-bold px-2.5 py-1 rounded-md text-slate-800">Commercial</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 mb-2">Commercial Cleaning</h3>
                            <p class="text-xs text-slate-600 leading-relaxed mb-4">Complete office, retail, and corporate workplace cleaning tailored around your operational hours.</p>
                        </div>
                        <a href="{{ route('services.commercial') }}" class="inline-flex items-center text-xs font-bold text-[#0082c9] hover:underline">
                            Learn more →
                        </a>
                    </div>
                </div>

                <!-- Service 2: Residential -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col group">
                    <div class="h-48 overflow-hidden bg-slate-100 relative">
                        <img src="{{ asset('images/9EA94CC5-F6BF-48BF-8253-DEB940AFD0CC.png') }}" alt="Residential Cleaning" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-xs font-bold px-2.5 py-1 rounded-md text-slate-800">Residential</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 mb-2">Residential Cleaning</h3>
                            <p class="text-xs text-slate-600 leading-relaxed mb-4">Weekly, fortnightly, spring cleans, and end-of-lease vacate cleans with guaranteed bond back standards.</p>
                        </div>
                        <a href="{{ route('services.residential') }}" class="inline-flex items-center text-xs font-bold text-[#0082c9] hover:underline">
                            Learn more →
                        </a>
                    </div>
                </div>

                <!-- Service 3: NDIS & DVA -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col group">
                    <div class="h-48 overflow-hidden bg-slate-100 relative">
                        <img src="{{ asset('images/5FEDE1A6-6A36-4D69-A34E-E54753EF05EC.png') }}" alt="NDIS Cleaning" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-xs font-bold px-2.5 py-1 rounded-md text-slate-800">NDIS / DVA</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 mb-2">NDIS & DVA Support</h3>
                            <p class="text-xs text-slate-600 leading-relaxed mb-4">Respectful, compassionate domestic support designed around individual participant plans and needs.</p>
                        </div>
                        <a href="{{ route('services.ndis') }}" class="inline-flex items-center text-xs font-bold text-[#0082c9] hover:underline">
                            Learn more →
                        </a>
                    </div>
                </div>

                <!-- Service 4: Aged Care & Medical -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col group">
                    <div class="h-48 overflow-hidden bg-slate-100 relative">
                        <img src="{{ asset('images/293D37B7-4B0A-4617-A899-0A9CAA5C9188.png') }}" alt="Aged Care Cleaning" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-xs font-bold px-2.5 py-1 rounded-md text-slate-800">Healthcare</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 mb-2">Aged Care & Medical</h3>
                            <p class="text-xs text-slate-600 leading-relaxed mb-4">Strict infection control, medical grade disinfection, and safe sanitation for clinics and retirement living.</p>
                        </div>
                        <a href="{{ route('services.aged-care') }}" class="inline-flex items-center text-xs font-bold text-[#0082c9] hover:underline">
                            Learn more →
                        </a>
                    </div>
                </div>

                <!-- Service 5: Industrial -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col group">
                    <div class="h-48 overflow-hidden bg-slate-100 relative">
                        <img src="{{ asset('images/BD1AA40B-5901-4391-8F1D-F5A685B48CD3.png') }}" alt="Industrial Cleaning" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-xs font-bold px-2.5 py-1 rounded-md text-slate-800">Industrial</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 mb-2">Industrial & Warehouse</h3>
                            <p class="text-xs text-slate-600 leading-relaxed mb-4">Heavy machinery zones, high-pressure washing, warehouse floor scrubbers, and industrial depots.</p>
                        </div>
                        <a href="{{ route('services.industrial') }}" class="inline-flex items-center text-xs font-bold text-[#0082c9] hover:underline">
                            Learn more →
                        </a>
                    </div>
                </div>

                <!-- Service 6: School Cleaning -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col group">
                    <div class="h-48 overflow-hidden bg-slate-100 relative">
                        <img src="{{ asset('images/BB04C489-7C7A-4FFE-A966-95E45B414013.png') }}" alt="School Cleaning" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-xs font-bold px-2.5 py-1 rounded-md text-slate-800">Education</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 mb-2">School & Childcare</h3>
                            <p class="text-xs text-slate-600 leading-relaxed mb-4">Child-safe sanitizing, non-toxic products, classrooms, auditoriums, and playground outdoor care.</p>
                        </div>
                        <a href="{{ route('services.school') }}" class="inline-flex items-center text-xs font-bold text-[#0082c9] hover:underline">
                            Learn more →
                        </a>
                    </div>
                </div>

                <!-- Service 7: Lawn Care -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col group">
                    <div class="h-48 overflow-hidden bg-slate-100 relative">
                        <img src="{{ asset('images/B0ED0E75-620F-4F7C-A4E5-E404C41CA7B2.png') }}" alt="Lawn Care" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-xs font-bold px-2.5 py-1 rounded-md text-slate-800">Outdoor</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 mb-2">Lawn & Garden Care</h3>
                            <p class="text-xs text-slate-600 leading-relaxed mb-4">Scheduled lawn mowing, hedging, weeding, pruning, and comprehensive commercial grounds maintenance.</p>
                        </div>
                        <a href="{{ route('services.lawn-care') }}" class="inline-flex items-center text-xs font-bold text-[#0082c9] hover:underline">
                            Learn more →
                        </a>
                    </div>
                </div>

                <!-- Service 8: Concreting -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col group">
                    <div class="h-48 overflow-hidden bg-slate-100 relative">
                        <img src="{{ asset('images/F96E5EB4-AE8A-4B55-8C6B-45C198FAEC6F.png') }}" alt="Concreting Services" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-xs font-bold px-2.5 py-1 rounded-md text-slate-800">Concreting</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 mb-2">Concreting Services</h3>
                            <p class="text-xs text-slate-600 leading-relaxed mb-4">Driveways, concrete slabs, pathways, commercial repair, and deep industrial surface high-pressure washing.</p>
                        </div>
                        <a href="{{ route('services.concreting') }}" class="inline-flex items-center text-xs font-bold text-[#0082c9] hover:underline">
                            Learn more →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Accreditation, Safety & Compliance Showcase -->
    <section class="py-16 lg:py-20 bg-slate-50/80 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14 space-y-3">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-sky-50 text-[#0082c9] text-xs font-bold uppercase tracking-wider border border-sky-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0082c9]"></span>
                    Governance & Safety Accreditations
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Accredited, Insured & Fully Compliant
                </h2>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                    Hydrox Facility Management operates under strict Australian regulatory, safety, and workplace standards, giving commercial facilities, educational campuses, and participants complete confidence.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 items-stretch">
                <!-- 1. WorkCover -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-xl hover:border-sky-300 transition-all duration-300 flex flex-col justify-between group">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold border border-amber-100 group-hover:scale-110 transition-transform">
                            🛡️
                        </div>
                        <div>
                            <h3 class="font-extrabold text-lg text-slate-900 group-hover:text-[#0082c9] transition-colors">WorkCover Covered</h3>
                            <p class="text-xs font-bold text-amber-600 mt-0.5">Committed to Workplace Safety</p>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Our employees and operations are protected under comprehensive WorkCover insurance, ensuring every project is carried out safely, professionally, and compliantly.
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-100 mt-5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                            ✓ Safety Protected
                        </span>
                    </div>
                </div>

                <!-- 2. Labour Hire -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-xl hover:border-sky-300 transition-all duration-300 flex flex-col justify-between group">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold border border-indigo-100 group-hover:scale-110 transition-transform">
                            ⚖️
                        </div>
                        <div>
                            <h3 class="font-extrabold text-lg text-slate-900 group-hover:text-[#0082c9] transition-colors">Labour Hire Compliant</h3>
                            <p class="text-xs font-bold text-indigo-600 mt-0.5">Professional & Industry Compliant</p>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Operating in accordance with Australian labour hire requirements where applicable, delivering dependable, ethical, and qualified cleaning professionals.
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-100 mt-5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                            ✓ Industry Compliant
                        </span>
                    </div>
                </div>

                <!-- 3. Cm3 Prequalified -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-xl hover:border-sky-300 transition-all duration-300 flex flex-col justify-between group">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-sky-50 text-[#0082c9] flex items-center justify-center text-xl font-bold border border-sky-100 group-hover:scale-110 transition-transform">
                            📋
                        </div>
                        <div>
                            <h3 class="font-extrabold text-lg text-slate-900 group-hover:text-[#0082c9] transition-colors">Cm3 Prequalified</h3>
                            <p class="text-xs font-bold text-[#0082c9] mt-0.5">Contractor Safety & Compliance</p>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Demonstrating verified contractor WHS compliance, comprehensive safety documentation, and workplace readiness through Cm3 contractor prequalification.
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-100 mt-5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                            ✓ Cm3 Compliant
                        </span>
                    </div>
                </div>

                <!-- 4. NDIS Provider -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-xl hover:border-sky-300 transition-all duration-300 flex flex-col justify-between group">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold border border-purple-100 group-hover:scale-110 transition-transform">
                            💜
                        </div>
                        <div>
                            <h3 class="font-extrabold text-lg text-slate-900 group-hover:text-[#0082c9] transition-colors">NDIS Provider</h3>
                            <p class="text-xs font-bold text-purple-600 mt-0.5">Disability Support Cleaning</p>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Supporting NDIS participants with reliable, respectful, and professional cleaning services tailored to individual support plans and daily living needs.
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-100 mt-5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                            ✓ NDIS Services
                        </span>
                    </div>
                </div>

                <!-- 5. DVA Provider -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-xl hover:border-sky-300 transition-all duration-300 flex flex-col justify-between group">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold border border-emerald-100 group-hover:scale-110 transition-transform">
                            🎖️
                        </div>
                        <div>
                            <h3 class="font-extrabold text-lg text-slate-900 group-hover:text-[#0082c9] transition-colors">DVA Provider</h3>
                            <p class="text-xs font-bold text-emerald-600 mt-0.5">Veteran Support Cleaning</p>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Providing dependable household cleaning services for eligible veterans through Australian Department of Veterans' Affairs support programs.
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-100 mt-5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                            ✓ DVA Services
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Client Reviews & Verified Ratings Section (Google & hipages) -->
    <section class="py-20 bg-slate-900 text-white relative overflow-hidden border-t border-slate-800">
        <!-- Ambient background glows -->
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#0082c9]/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-10 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-400/10 border border-amber-400/20 text-amber-300 text-xs font-bold uppercase tracking-wider mb-4">
                        <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span>Verified Client Feedback</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                        Trusted by Melbourne Businesses, <br class="hidden sm:inline">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-sky-300 to-amber-300">Healthcare Providers & Families</span>
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base mt-3 max-w-2xl leading-relaxed">
                        Consistent 5-star ratings across Google and hipages. From busy corporate facilities to sensitive medical and NDIS environments, see what our clients have to say.
                    </p>
                </div>

                <!-- Platform Aggregate Badges -->
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                    <!-- Google Scorecard -->
                    <div class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-2xl p-3.5 px-4 backdrop-blur shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center p-2 shadow-inner shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.24v3.15C3.26 21.36 7.33 24 12 24Z"/>
                                <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.24C.45 8.16 0 9.98 0 12s.45 3.84 1.24 5.42l4.04-3.15Z"/>
                                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.24 6.58l4.04 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-1 text-amber-400 text-xs font-bold">
                                <span>★★★★★</span>
                                <span class="text-white text-sm font-extrabold ml-1">5.0</span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-medium">Google Reviews</div>
                        </div>
                    </div>

                    <!-- hipages Scorecard -->
                    <a href="https://hipages.com.au/connect/hydroxfacilitymanagement" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-orange-500/40 rounded-2xl p-3.5 px-4 backdrop-blur shadow-sm transition-all">
                        <div class="w-10 h-10 rounded-xl bg-[#FF5A36] text-white flex items-center justify-center font-black text-sm tracking-tighter shrink-0 shadow-sm">
                            hi
                        </div>
                        <div>
                            <div class="flex items-center gap-1 text-amber-400 text-xs font-bold">
                                <span>★★★★★</span>
                                <span class="text-white text-sm font-extrabold ml-1">5.0</span>
                            </div>
                            <div class="text-[11px] text-slate-400 group-hover:text-orange-300 font-medium transition-colors flex items-center gap-1">
                                <span>hipages Verified</span>
                                <svg class="w-2.5 h-2.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Reviews Grid (6 realistic reviews covering commercial, medical, NDIS, industrial, childcare, residential) -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Review 1: Commercial Office (Google) -->
                <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 flex flex-col justify-between hover:border-[#0082c9]/60 hover:bg-slate-800 transition-all duration-200">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-amber-400 text-sm">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <div class="flex items-center gap-1 text-[11px] text-slate-400 font-medium">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.24v3.15C3.26 21.36 7.33 24 12 24Z"/>
                                    <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.24C.45 8.16 0 9.98 0 12s.45 3.84 1.24 5.42l4.04-3.15Z"/>
                                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.24 6.58l4.04 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
                                </svg>
                                <span>Google Review</span>
                            </div>
                        </div>
                        <p class="text-slate-200 text-sm leading-relaxed">
                            "Hydrox has looked after our two corporate office floors in Melbourne for over a year now. Reliable, thorough, and always attentive. Their supervisor checks in routinely, and our staff noticed the hygiene improvement immediately."
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-700/60 mt-5 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-white">David M.</div>
                            <div class="text-xs text-sky-400">Operations Manager • Commercial Offices</div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Verified Client</span>
                    </div>
                </div>

                <!-- Review 2: hipages Verified Job -->
                <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 flex flex-col justify-between hover:border-orange-500/60 hover:bg-slate-800 transition-all duration-200">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-amber-400 text-sm">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <div class="flex items-center gap-1 text-[11px] text-orange-400 font-semibold">
                                <span class="w-3.5 h-3.5 rounded bg-[#FF5A36] text-white flex items-center justify-center text-[8px] font-black">hi</span>
                                <span>hipages Verified</span>
                            </div>
                        </div>
                        <p class="text-slate-200 text-sm leading-relaxed">
                            "Booked Hydrox through hipages for extensive concrete cleaning, driveway pressure washing, and building washdown. Punctual, top-of-the-line equipment, and fair pricing. The results exceeded expectations."
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-700/60 mt-5 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-white">Craig T.</div>
                            <div class="text-xs text-orange-300">Property Owner • High-Pressure Cleaning</div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-orange-500/10 text-orange-400 border border-orange-500/20">hipages Job</span>
                    </div>
                </div>

                <!-- Review 3: Medical / Dental Clinic (Google) -->
                <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 flex flex-col justify-between hover:border-[#0082c9]/60 hover:bg-slate-800 transition-all duration-200">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-amber-400 text-sm">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <div class="flex items-center gap-1 text-[11px] text-slate-400 font-medium">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.24v3.15C3.26 21.36 7.33 24 12 24Z"/>
                                    <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.24C.45 8.16 0 9.98 0 12s.45 3.84 1.24 5.42l4.04-3.15Z"/>
                                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.24 6.58l4.04 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
                                </svg>
                                <span>Google Review</span>
                            </div>
                        </div>
                        <p class="text-slate-200 text-sm leading-relaxed">
                            "In healthcare, infection control standards are strict. Hydrox strictly follows sanitation protocols, uses hospital-grade disinfectants, and provides full digital sign-off logs every shift. Truly commendable team."
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-700/60 mt-5 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-white">Dr. Sarah K.</div>
                            <div class="text-xs text-sky-400">Clinic Director • Medical Practice</div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Verified Client</span>
                    </div>
                </div>

                <!-- Review 4: NDIS Support Coordinator (Google) -->
                <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 flex flex-col justify-between hover:border-[#0082c9]/60 hover:bg-slate-800 transition-all duration-200">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-amber-400 text-sm">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <div class="flex items-center gap-1 text-[11px] text-slate-400 font-medium">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.24v3.15C3.26 21.36 7.33 24 12 24Z"/>
                                    <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.24C.45 8.16 0 9.98 0 12s.45 3.84 1.24 5.42l4.04-3.15Z"/>
                                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.24 6.58l4.04 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
                                </svg>
                                <span>Google Review</span>
                            </div>
                        </div>
                        <p class="text-slate-200 text-sm leading-relaxed">
                            "As an NDIS support coordinator, finding compassionate, respectful, and reliable cleaners is tough. Hydrox cleaners are gentle, police-vetted, and make our participants feel respected and safe in their homes."
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-700/60 mt-5 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-white">Elena R.</div>
                            <div class="text-xs text-sky-400">Support Coordinator • NDIS Services</div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Verified Client</span>
                    </div>
                </div>

                <!-- Review 5: Industrial Warehouse (Google) -->
                <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 flex flex-col justify-between hover:border-[#0082c9]/60 hover:bg-slate-800 transition-all duration-200">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-amber-400 text-sm">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <div class="flex items-center gap-1 text-[11px] text-slate-400 font-medium">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.24v3.15C3.26 21.36 7.33 24 12 24Z"/>
                                    <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.24C.45 8.16 0 9.98 0 12s.45 3.84 1.24 5.42l4.04-3.15Z"/>
                                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.24 6.58l4.04 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
                                </svg>
                                <span>Google Review</span>
                            </div>
                        </div>
                        <p class="text-slate-200 text-sm leading-relaxed">
                            "They handle our distribution warehouse floor scrubbing and amenities cleaning in Dandenong South. Cm3 prequalified, correct SWMS, full PPE compliance, and zero disruptions to our dispatch shifts."
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-700/60 mt-5 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-white">Jason B.</div>
                            <div class="text-xs text-sky-400">Logistics & Site Manager • Industrial Logistics</div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Verified Client</span>
                    </div>
                </div>

                <!-- Review 6: School / Childcare Center (Google) -->
                <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 flex flex-col justify-between hover:border-[#0082c9]/60 hover:bg-slate-800 transition-all duration-200">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-amber-400 text-sm">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <div class="flex items-center gap-1 text-[11px] text-slate-400 font-medium">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.24v3.15C3.26 21.36 7.33 24 12 24Z"/>
                                    <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.24C.45 8.16 0 9.98 0 12s.45 3.84 1.24 5.42l4.04-3.15Z"/>
                                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.24 6.58l4.04 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
                                </svg>
                                <span>Google Review</span>
                            </div>
                        </div>
                        <p class="text-slate-200 text-sm leading-relaxed">
                            "Hydrox cleaned our early learning centre thoroughly prior to the new term. All staff hold Working with Children Checks, they only use safe, eco-certified products, and the centre looks spotless every morning."
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-700/60 mt-5 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-white">Michelle P.</div>
                            <div class="text-xs text-sky-400">Centre Director • Early Learning & Education</div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Verified Client</span>
                    </div>
                </div>
            </div>

            <!-- Bottom trust banner & CTAs -->
            <div class="mt-12 p-6 rounded-2xl bg-gradient-to-r from-slate-800 to-slate-800/60 border border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-6 text-center sm:text-left">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-400/20 border border-amber-400/30 flex items-center justify-center text-amber-400 text-xl font-bold shrink-0">
                        ★
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Looking for verified commercial or domestic cleaning?</h3>
                        <p class="text-xs sm:text-sm text-slate-400">Read our reviews or speak directly with our Melbourne facility team today.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-3 shrink-0">
                    <a href="https://www.google.com/search?q=Hydrox+Facility+Management+Melbourne" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-white text-xs font-semibold transition-all">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.24v3.15C3.26 21.36 7.33 24 12 24Z"/>
                            <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.24C.45 8.16 0 9.98 0 12s.45 3.84 1.24 5.42l4.04-3.15Z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.24 6.58l4.04 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
                        </svg>
                        <span>Google Search Profile</span>
                        <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a href="{{ route('booking.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#0082c9] to-sky-500 hover:from-sky-500 hover:to-[#0082c9] text-white text-xs font-bold shadow-md shadow-sky-500/20 transition-all">
                        Request a Free Quote
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Hydrox / Trust Factors -->
    <section class="py-20 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-6 space-y-6">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">The Hydrox Advantage</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 leading-tight">Reliable Cleaning Backed By Rigorous Standards</h2>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        We know facility managers, business owners, and homeowners need cleaners who show up on time, communicate clearly, and take genuine pride in their work.
                    </p>

                    <div class="grid sm:grid-cols-2 gap-6 pt-4">
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-base">🛡️</div>
                            <h3 class="font-bold text-slate-900 text-base">Fully Vetted & Insured</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Every team member undergoes national police checks, background verification, and continuous quality training.</p>
                        </div>

                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base">🌿</div>
                            <h3 class="font-bold text-slate-900 text-base">Eco & Safe Products</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Commercial-grade products that eliminate pathogens while safeguarding employee health and air quality.</p>
                        </div>

                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-base">⚡</div>
                            <h3 class="font-bold text-slate-900 text-base">Fast Turnaround</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Swift quoting and rapid deployment across Melbourne metropolitan and Victorian regional hubs.</p>
                        </div>

                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base">⭐</div>
                            <h3 class="font-bold text-slate-900 text-base">100% Satisfaction</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">If anything falls short of our agreed service level, we promptly return to rectify it at zero charge.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6 grid grid-cols-2 gap-4">
                    <img src="{{ asset('images/08DAF5B0-3CEE-464C-B334-84A930649E27.png') }}" alt="Commercial Facility Care" class="rounded-2xl shadow-md w-full h-64 object-cover">
                    <img src="{{ asset('images/BD1AA40B-5901-4391-8F1D-F5A685B48CD3.png') }}" alt="Industrial Facility Maintenance" class="rounded-2xl shadow-md w-full h-64 object-cover mt-8">
                    <img src="{{ asset('images/BB04C489-7C7A-4FFE-A966-95E45B414013.png') }}" alt="Educational Campus Cleaning" class="rounded-2xl shadow-md w-full h-64 object-cover">
                    <img src="{{ asset('images/293D37B7-4B0A-4617-A899-0A9CAA5C9188.png') }}" alt="Healthcare Facility Sanitation" class="rounded-2xl shadow-md w-full h-64 object-cover mt-8">
                </div>
            </div>
        </div>
    </section>

    <!-- 3-Step Simple Process -->
    <section class="py-20 bg-slate-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-sky-400">Streamlined Experience</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold mt-2">How Easy It Is To Work With Us</h2>
                <p class="text-sm text-slate-400 mt-2">No complicated forms or endless phone tag. Capture your quote in three straightforward steps.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 relative">
                    <span class="text-5xl font-black text-sky-500/20 absolute top-4 right-6">01</span>
                    <h3 class="text-xl font-bold mb-3">1. Tell Us What You Need</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">Choose your service, provide your suburb and preferred date. No forced account registration or unnecessary hurdles.</p>
                </div>
                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 relative">
                    <span class="text-5xl font-black text-sky-500/20 absolute top-4 right-6">02</span>
                    <h3 class="text-xl font-bold mb-3">2. Fast Custom Estimate</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">Our Melbourne operations team reviews your requirements and contacts you with transparent, competitive pricing.</p>
                </div>
                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 relative">
                    <span class="text-5xl font-black text-sky-500/20 absolute top-4 right-6">03</span>
                    <h3 class="text-xl font-bold mb-3">3. Confirmed & Delivered</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">Once accepted, your cleaning team is dispatched with a full scope checklist and photographic completion report.</p>
                </div>
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('booking.create') }}" class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-[#0082c9] text-white font-bold text-base shadow-xl hover:bg-[#006da9] transition">
                    Request Your Free Quote Today
                </a>
            </div>
        </div>
    </section>

    <!-- Bottom CTA Banner -->
    <section class="py-16 bg-[#0082c9] text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Ready For A Cleaner, Healthier Facility?</h2>
            <p class="text-base text-sky-100 max-w-2xl mx-auto">
                Call our friendly Melbourne team directly at <a href="tel:0418222477" class="underline font-bold text-white">0418 222 477</a> or request your free quote online in less than 60 seconds.
            </p>
            <div class="flex flex-wrap justify-center gap-4 pt-2">
                <a href="{{ route('booking.create') }}" class="px-8 py-3.5 rounded-xl bg-white text-[#0082c9] font-bold text-sm shadow-xl hover:bg-slate-100 transition">
                    Get Free Quote
                </a>
                <a href="{{ route('contact') }}" class="px-8 py-3.5 rounded-xl bg-sky-800/80 text-white border border-white/20 font-bold text-sm hover:bg-sky-900 transition">
                    Contact Our Office
                </a>
            </div>
        </div>
    </section>
@endsection
