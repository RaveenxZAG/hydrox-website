<!DOCTYPE html>
<html lang="en-AU" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hydrox Facility Management | Commercial & Specialized Cleaning Services')</title>
    <meta name="description" content="@yield('meta_description', 'Hydrox Facility Management provides trusted commercial, residential, NDIS, aged care, school, and industrial cleaning across Melbourne & Victoria.')">
    
    <link rel="icon" type="image/png" href="{{ asset('images/cropped-icon-5.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/cropped-icon-5.png') }}">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-18428986459"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'AW-18428986459');
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
    @stack('head')
</head>
<body class="bg-slate-50 text-slate-900 antialiased flex flex-col min-h-screen selection:bg-[#0082c9]/20 selection:text-[#041426]">

    <!-- Top Announcement Bar -->
    <div class="bg-[#041426] text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div class="flex items-center gap-4 flex-wrap justify-center sm:justify-start">
                <span class="inline-flex items-center gap-1.5 text-emerald-400 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Melbourne & Victoria Wide Service
                </span>
                <span class="hidden md:inline text-slate-600">|</span>
                <span class="hidden md:inline">Police Checked & Fully Insured Cleaners</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="tel:0418222477" class="font-semibold text-white hover:text-sky-400 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    0418 222 477
                </a>
                <span class="text-slate-600">|</span>
                <a href="mailto:admin@hydrox.au" class="hover:text-white transition">admin@hydrox.au</a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm" x-data="{ mobileMenu: false, servicesOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/Hydrox Logo.svg') }}" alt="Hydrox Facility Management" class="h-10 w-auto" onerror="this.src='{{ asset('images/Hydrox-Logok.png') }}'">
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center gap-8 text-sm font-medium text-slate-700">
                    <a href="{{ route('home') }}" class="transition hover:text-[#0082c9] {{ request()->routeIs('home') ? 'text-[#0082c9] font-bold' : '' }}">Home</a>

                    <!-- Services Dropdown -->
                    <div class="relative" @mouseenter="servicesOpen = true" @mouseleave="servicesOpen = false">
                        <button type="button" class="inline-flex items-center gap-1.5 transition hover:text-[#0082c9] {{ request()->routeIs('services.*') ? 'text-[#0082c9] font-bold' : '' }}">
                            <span>Services</span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': servicesOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="servicesOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="absolute left-0 mt-2 w-80 rounded-2xl bg-white p-3 shadow-2xl border border-slate-100 ring-1 ring-black/5" x-cloak>
                            <div class="grid gap-1">
                                <a href="{{ route('services.commercial') }}" class="flex items-center gap-3 rounded-xl p-2.5 hover:bg-slate-50 transition group">
                                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-sm group-hover:bg-[#0082c9] group-hover:text-white transition">🏢</div>
                                    <div>
                                        <p class="font-semibold text-slate-900 text-xs">Commercial Cleaning</p>
                                        <p class="text-[11px] text-slate-500">Offices, retail, business facilities</p>
                                    </div>
                                </a>
                                <a href="{{ route('services.residential') }}" class="flex items-center gap-3 rounded-xl p-2.5 hover:bg-slate-50 transition group">
                                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-sm group-hover:bg-[#0082c9] group-hover:text-white transition">🏡</div>
                                    <div>
                                        <p class="font-semibold text-slate-900 text-xs">Residential Cleaning</p>
                                        <p class="text-[11px] text-slate-500">Regular house, deep & end of lease</p>
                                    </div>
                                </a>
                                <a href="{{ route('services.ndis') }}" class="flex items-center gap-3 rounded-xl p-2.5 hover:bg-slate-50 transition group">
                                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-sm group-hover:bg-[#0082c9] group-hover:text-white transition">🤝</div>
                                    <div>
                                        <p class="font-semibold text-slate-900 text-xs">NDIS & DVA Cleaning</p>
                                        <p class="text-[11px] text-slate-500">Tailored care for participants</p>
                                    </div>
                                </a>
                                <a href="{{ route('services.aged-care') }}" class="flex items-center gap-3 rounded-xl p-2.5 hover:bg-slate-50 transition group">
                                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-sm group-hover:bg-[#0082c9] group-hover:text-white transition">🏥</div>
                                    <div>
                                        <p class="font-semibold text-slate-900 text-xs">Aged Care & Medical</p>
                                        <p class="text-[11px] text-slate-500">Clinical sanitization & hygiene</p>
                                    </div>
                                </a>
                                <a href="{{ route('services.industrial') }}" class="flex items-center gap-3 rounded-xl p-2.5 hover:bg-slate-50 transition group">
                                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-sm group-hover:bg-[#0082c9] group-hover:text-white transition">🏭</div>
                                    <div>
                                        <p class="font-semibold text-slate-900 text-xs">Industrial & Warehouse</p>
                                        <p class="text-[11px] text-slate-500">Heavy plant, floor scrubbing & factories</p>
                                    </div>
                                </a>
                                <a href="{{ route('services.school') }}" class="flex items-center gap-3 rounded-xl p-2.5 hover:bg-slate-50 transition group">
                                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-sm group-hover:bg-[#0082c9] group-hover:text-white transition">🏫</div>
                                    <div>
                                        <p class="font-semibold text-slate-900 text-xs">School & Childcare</p>
                                        <p class="text-[11px] text-slate-500">Safe, non-toxic sanitization</p>
                                    </div>
                                </a>
                                <a href="{{ route('services.lawn-care') }}" class="flex items-center gap-3 rounded-xl p-2.5 hover:bg-slate-50 transition group">
                                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-sm group-hover:bg-[#0082c9] group-hover:text-white transition">🌿</div>
                                    <div>
                                        <p class="font-semibold text-slate-900 text-xs">Lawn Care & Gardening</p>
                                        <p class="text-[11px] text-slate-500">Mowing, edges, weeding & grounds upkeep</p>
                                    </div>
                                </a>
                                <a href="{{ route('services.concreting') }}" class="flex items-center gap-3 rounded-xl p-2.5 hover:bg-slate-50 transition group">
                                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-[#0082c9] flex items-center justify-center font-bold text-sm group-hover:bg-[#0082c9] group-hover:text-white transition">🧱</div>
                                    <div>
                                        <p class="font-semibold text-slate-900 text-xs">Concreting Services</p>
                                        <p class="text-[11px] text-slate-500">Driveways, slabs & pressure washing</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('about') }}" class="transition hover:text-[#0082c9] {{ request()->routeIs('about') ? 'text-[#0082c9] font-bold' : '' }}">About Us</a>
                    <a href="{{ route('faq') }}" class="transition hover:text-[#0082c9] {{ request()->routeIs('faq') ? 'text-[#0082c9] font-bold' : '' }}">FAQ</a>
                    <a href="{{ route('careers') }}" class="transition hover:text-[#0082c9] {{ request()->routeIs('careers') ? 'text-[#0082c9] font-bold' : '' }}">Careers</a>
                    <a href="{{ route('contact') }}" class="transition hover:text-[#0082c9] {{ request()->routeIs('contact') ? 'text-[#0082c9] font-bold' : '' }}">Contact</a>
                </nav>

                <!-- Action Button -->
                <div class="hidden lg:flex items-center gap-4">
                    <a href="tel:0418222477" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-800 font-bold text-sm hover:border-[#0082c9] hover:text-[#0082c9] transition">
                        <svg class="w-4 h-4 text-[#0082c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        0418 222 477
                    </a>
                    <a href="{{ route('booking.create') }}" class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-md shadow-sky-600/20 hover:bg-[#006da9] transition">
                        Get Free Quote
                    </a>
                </div>

                <!-- Mobile Hamburger Button -->
                <button type="button" @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-xl text-slate-700 hover:bg-slate-100 transition" aria-label="Toggle navigation menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Drawer -->
        <div x-show="mobileMenu" x-transition class="lg:hidden border-t border-slate-200 bg-white px-4 pt-4 pb-6 space-y-4 shadow-xl" x-cloak>
            <div class="grid gap-2">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg font-medium text-slate-800 hover:bg-slate-50 {{ request()->routeIs('home') ? 'bg-sky-50 text-[#0082c9]' : '' }}">Home</a>
                
                <div class="py-1">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Cleaning & Facility Services</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 pl-2">
                        <a href="{{ route('services.commercial') }}" class="block px-3 py-1.5 text-sm text-slate-700 hover:text-[#0082c9]">Commercial Cleaning</a>
                        <a href="{{ route('services.residential') }}" class="block px-3 py-1.5 text-sm text-slate-700 hover:text-[#0082c9]">Residential Cleaning</a>
                        <a href="{{ route('services.ndis') }}" class="block px-3 py-1.5 text-sm text-slate-700 hover:text-[#0082c9]">NDIS & DVA Cleaning</a>
                        <a href="{{ route('services.aged-care') }}" class="block px-3 py-1.5 text-sm text-slate-700 hover:text-[#0082c9]">Aged Care & Medical</a>
                        <a href="{{ route('services.industrial') }}" class="block px-3 py-1.5 text-sm text-slate-700 hover:text-[#0082c9]">Industrial & Warehouse</a>
                        <a href="{{ route('services.school') }}" class="block px-3 py-1.5 text-sm text-slate-700 hover:text-[#0082c9]">School Cleaning</a>
                        <a href="{{ route('services.lawn-care') }}" class="block px-3 py-1.5 text-sm text-slate-700 hover:text-[#0082c9]">Lawn Care & Gardening</a>
                        <a href="{{ route('services.concreting') }}" class="block px-3 py-1.5 text-sm text-slate-700 hover:text-[#0082c9]">Concreting Services</a>
                    </div>
                </div>

                <a href="{{ route('about') }}" class="block px-3 py-2 rounded-lg font-medium text-slate-800 hover:bg-slate-50">About Us</a>
                <a href="{{ route('faq') }}" class="block px-3 py-2 rounded-lg font-medium text-slate-800 hover:bg-slate-50">FAQ</a>
                <a href="{{ route('careers') }}" class="block px-3 py-2 rounded-lg font-medium text-slate-800 hover:bg-slate-50">Careers</a>
                <a href="{{ route('contact') }}" class="block px-3 py-2 rounded-lg font-medium text-slate-800 hover:bg-slate-50">Contact Us</a>
            </div>

            <div class="pt-4 border-t border-slate-100 flex flex-col gap-2">
                <a href="tel:0418222477" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl border border-slate-300 font-bold text-slate-800">
                    <svg class="w-4 h-4 text-[#0082c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Call 0418 222 477
                </a>
                <a href="{{ route('booking.create') }}" class="w-full flex items-center justify-center py-3 rounded-xl bg-[#0082c9] text-white font-bold shadow-lg shadow-sky-600/25">
                    Get Free Quote
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Slot -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#041426] text-slate-300 border-t border-slate-800 pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-slate-800">
                <!-- Col 1: About Brand -->
                <div class="lg:col-span-2 space-y-4">
                    <a href="{{ route('home') }}" class="inline-block">
                        <img src="{{ asset('images/Hydrox Logo.svg') }}" alt="Hydrox Facility Management" class="h-10 w-auto brightness-200" onerror="this.src='{{ asset('images/Hydrox-Logok.png') }}'">
                    </a>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-sm">
                        Hydrox Facility Management Cleaning Services Pty. Ltd delivers premium commercial, residential, medical, and specialized facility services across Melbourne and Victoria. Reliable, fully vetted, and insured.
                    </p>
                    <div class="flex flex-wrap items-center gap-2 pt-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 text-xs font-medium text-slate-300 border border-slate-700">
                            ABN: 35 670 676 785
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-950/60 text-emerald-400 border border-emerald-800 text-xs font-medium">
                            ✓ Fully Insured
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-800/80 text-[11px] font-medium text-slate-300 border border-slate-700">
                            🛡️ WorkCover
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-800/80 text-[11px] font-medium text-slate-300 border border-slate-700">
                            ⚖️ Labour Hire
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-800/80 text-[11px] font-medium text-slate-300 border border-slate-700">
                            📋 Cm3 Prequalified
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-800/80 text-[11px] font-medium text-slate-300 border border-slate-700">
                            💜 NDIS & DVA
                        </span>
                    </div>
                </div>

                <!-- Col 2: Services -->
                <div>
                    <h3 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Core Services</h3>
                    <ul class="space-y-2.5 text-sm text-slate-400">
                        <li><a href="{{ route('services.commercial') }}" class="hover:text-white transition">Commercial Cleaning</a></li>
                        <li><a href="{{ route('services.residential') }}" class="hover:text-white transition">Residential Cleaning</a></li>
                        <li><a href="{{ route('services.ndis') }}" class="hover:text-white transition">NDIS & DVA Cleaning</a></li>
                        <li><a href="{{ route('services.aged-care') }}" class="hover:text-white transition">Aged Care & Medical</a></li>
                        <li><a href="{{ route('services.industrial') }}" class="hover:text-white transition">Industrial & Warehouse</a></li>
                        <li><a href="{{ route('services.school') }}" class="hover:text-white transition">School Cleaning</a></li>
                        <li><a href="{{ route('services.lawn-care') }}" class="hover:text-white transition">Lawn & Garden Care</a></li>
                        <li><a href="{{ route('services.concreting') }}" class="hover:text-white transition">Concreting Services</a></li>
                    </ul>
                </div>

                <!-- Col 3: Company -->
                <div>
                    <h3 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Company</h3>
                    <ul class="space-y-2.5 text-sm text-slate-400">
                        <li><a href="{{ route('about') }}" class="hover:text-white transition">About Us</a></li>
                        <li><a href="{{ route('careers') }}" class="hover:text-white transition">Careers & Subcontractors</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-white transition">Frequently Asked Questions</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact Us</a></li>
                        <li><a href="{{ route('booking.create') }}" class="hover:text-white transition">Get Free Quote</a></li>
                        <li><a href="{{ route('legal') }}" class="hover:text-white transition">Legal & Privacy Policies</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contact & Portal -->
                <div>
                    <h3 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Direct Contact</h3>
                    <div class="space-y-3 text-sm text-slate-400">
                        <p class="flex items-start gap-2">
                            <span class="text-sky-400 mt-0.5">📞</span>
                            <a href="tel:0418222477" class="hover:text-white font-semibold text-white">0418 222 477</a>
                        </p>
                        <p class="flex items-start gap-2">
                            <span class="text-sky-400 mt-0.5">✉️</span>
                            <a href="mailto:admin@hydrox.au" class="hover:text-white">admin@hydrox.au</a>
                        </p>
                        <p class="flex items-start gap-2">
                            <span class="text-sky-400 mt-0.5">📍</span>
                            <span>Melbourne, Victoria, Australia</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Acknowledgement of Country -->
            <div class="py-6 border-b border-slate-800 flex flex-col sm:flex-row items-start sm:items-center gap-4 text-xs text-slate-400">
                <div class="flex items-center gap-2 flex-shrink-0" aria-hidden="true">
                    <svg class="w-8 h-5 rounded-sm overflow-hidden flex-shrink-0 border border-slate-700/80 shadow-sm" viewBox="0 0 60 40" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Australian Aboriginal Flag">
                        <rect width="60" height="20" fill="#000000"/>
                        <rect y="20" width="60" height="20" fill="#d9241b"/>
                        <circle cx="30" cy="20" r="10" fill="#ffd100"/>
                    </svg>
                    <svg class="w-8 h-5 rounded-sm overflow-hidden flex-shrink-0 border border-slate-700/80 shadow-sm" viewBox="0 0 60 40" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Torres Strait Islander Flag">
                        <rect width="60" height="40" fill="#00247d"/>
                        <rect width="60" height="8" fill="#009944"/>
                        <rect y="8" width="60" height="2" fill="#000000"/>
                        <rect y="30" width="60" height="2" fill="#000000"/>
                        <rect y="32" width="60" height="8" fill="#009944"/>
                        <path d="M22 25 C23 15 37 15 38 25 L36 25 C35 17 25 17 24 25 Z" fill="#ffffff"/>
                        <polygon points="30,17 31.2,20.5 35,20.5 32,22.7 33.1,26.2 30,24 26.9,26.2 28,22.7 25,20.5 28.8,20.5" fill="#ffffff"/>
                    </svg>
                </div>
                <p class="leading-relaxed">
                    <span class="text-slate-300 font-semibold">Acknowledgement of Country:</span> Hydrox Facility Management acknowledges the Traditional Custodians of Country throughout Australia and their continuing connection to land, waters, and community. We pay our respects to Aboriginal and Torres Strait Islander cultures, and to Elders past and present.
                </p>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>© {{ date('Y') }} Hydrox Facility Management Cleaning Services Pty. Ltd. All rights reserved. <span class="mx-1.5 text-slate-600">|</span> Developed by <a href="https://geniusgeeks.au" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-white font-medium transition underline-offset-2 hover:underline">Genius Geeks</a></p>
                <div class="flex items-center gap-6">
                    <a href="{{ route('legal') }}" class="hover:text-slate-400 transition">Terms of Service</a>
                    <a href="{{ route('legal') }}" class="hover:text-slate-400 transition">Privacy Policy</a>
                    <a href="{{ route('contact') }}" class="hover:text-slate-400 transition">Support</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Fixed Floating CTA Bar -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur border-t border-slate-200 p-3 flex items-center gap-3 shadow-2xl">
        <a href="tel:0418222477" class="flex-1 inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-slate-300 font-bold text-sm text-slate-800 hover:bg-slate-50">
            <svg class="w-4 h-4 text-[#0082c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            Call 0418 222 477
        </a>
        <a href="{{ route('booking.create') }}" class="flex-1 inline-flex items-center justify-center py-3 px-4 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-md shadow-sky-600/30 hover:bg-[#006da9]">
            Get Free Quote
        </a>
    </div>

    @stack('scripts')
</body>
</html>
