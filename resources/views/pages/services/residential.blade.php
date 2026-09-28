@extends('layouts.public')

@section('title', 'Residential Cleaning Services Melbourne | Hydrox Facility Management')
@section('meta_description', 'Trusted residential house cleaning, carpet steam cleaning, pressure washing, window cleaning, and bond back vacate cleaning across Melbourne and surrounding Victoria.')

@section('content')
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <span class="inline-block px-3.5 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">Home Cleanliness</span>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">Residential & Home Cleaning Services</h1>
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                    Come home to a fresh, healthy, and spotless living space. Whether you need ongoing weekly maintenance, carpet steam cleaning, high-pressure surface washing, streak-free window cleaning, or an end-of-lease bond clean, Hydrox delivers meticulous domestic care across Melbourne.
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('booking.create', ['service' => 'Residential Cleaning']) }}" class="px-8 py-4 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-xl shadow-sky-600/30 hover:bg-[#006da9] transition">
                        Get House Cleaning Quote
                    </a>
                    <a href="tel:0418222477" class="px-6 py-4 rounded-xl bg-white/10 text-white font-bold text-sm border border-white/20 hover:bg-white/15 transition flex items-center gap-2">
                        📞 0418 222 477
                    </a>
                </div>
            </div>
            <div class="lg:col-span-5">
                <img src="{{ asset('images/9EA94CC5-F6BF-48BF-8253-DEB940AFD0CC.png') }}" alt="Residential Cleaning" class="rounded-3xl shadow-2xl border border-white/10 w-full object-cover h-80 sm:h-96">
            </div>
        </div>
    </div>
</section>

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12">
            <div class="lg:col-span-8 space-y-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">A Fresh, Tidy Home Without the Stress</h2>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                        Balancing busy careers, families, and personal time is challenging. Our verified residential cleaners handle the heavy lifting, deep sanitizing kitchens, scrubbing bathrooms, steam-cleaning carpets, washing windows, and leaving your floors and exterior surfaces gleaming so you can enjoy your home.
                    </p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Our Residential Cleaning Checklist</h3>
                    <div class="grid sm:grid-cols-2 gap-4 text-xs sm:text-sm text-slate-700">
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Bathrooms, showers, tiles, mirrors & vanity sanitizing</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Kitchen benchtops, cooktops, sink & microwave</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Full carpet vacuuming and hard floor mopping</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Dusting furniture, skirting boards & light fixtures</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Emptying internal bins and replacing liners</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Professional carpet steam extraction & stain removal</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Streak-free interior & exterior window cleaning</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>High-pressure washing for driveways, paths & patios</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Service Options</h3>
                    <div class="grid sm:grid-cols-3 gap-4 text-xs">
                        <div class="p-4 bg-white border border-slate-200 rounded-2xl space-y-2">
                            <span class="font-bold text-sm text-[#0082c9]">Regular Cleaning</span>
                            <p class="text-slate-600">Weekly or fortnightly recurring service maintaining ongoing home cleanliness.</p>
                        </div>
                        <div class="p-4 bg-white border border-slate-200 rounded-2xl space-y-2">
                            <span class="font-bold text-sm text-[#0082c9]">Deep Spring Clean</span>
                            <p class="text-slate-600">Thorough top-to-bottom scrub tackling built-up grease, grout, and hidden dust.</p>
                        </div>
                        <div class="p-4 bg-white border border-slate-200 rounded-2xl space-y-2">
                            <span class="font-bold text-sm text-[#0082c9]">End of Lease Vacate</span>
                            <p class="text-slate-600">Rigorous real estate standard clean designed to help secure 100% bond recovery.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-sky-50 rounded-3xl p-6 sm:p-8 border border-sky-100 sticky top-28">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Free Estimate</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-1 mb-3">Book Home Cleaning</h3>
                    <p class="text-xs text-slate-600 mb-6">Tell us your suburb and services required for a prompt, transparent quote.</p>

                    <a href="{{ route('booking.create', ['service' => 'Residential Cleaning']) }}" class="w-full block text-center py-3.5 px-6 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-md hover:bg-[#006da9] transition mb-3">
                        Get Free Quote
                    </a>
                    <a href="tel:0418222477" class="w-full block text-center py-3.5 px-6 rounded-xl bg-white border border-slate-300 text-slate-800 font-bold text-sm hover:bg-slate-50 transition">
                        Call 0418 222 477
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dedicated Specialized Services Section: Carpet Cleaning, Pressure Washing, Window Cleaning -->
<section class="py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="inline-block px-3.5 py-1 rounded-full bg-sky-100 text-[#0082c9] text-xs font-bold uppercase tracking-wider mb-3">Specialized Residential Services</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Carpet Cleaning, Pressure Washing & Window Cleaning</h2>
            <p class="text-base text-slate-600 mt-4 leading-relaxed">
                Hydrox provides advanced residential equipment and certified technicians for carpets, high-pressure surface rejuvenation, and crystal-clear windows across Melbourne homes.
            </p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- 1. Carpet Cleaning -->
            <div class="bg-white rounded-3xl overflow-hidden shadow-lg border border-slate-200 flex flex-col hover:shadow-xl transition-all duration-300 group">
                <div class="relative overflow-hidden h-64">
                    <img src="{{ asset('images/residential-carpet-cleaning.jpg') }}" alt="Residential Carpet Cleaning Melbourne" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <div class="absolute top-4 left-4 bg-slate-900/80 backdrop-blur-md text-white text-xs font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5">
                        <span>🧼</span> Deep Extraction
                    </div>
                </div>
                <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-6">
                    <div class="space-y-3">
                        <h3 class="text-xl font-bold text-slate-900">Carpet Cleaning & Steam Care</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Industrial hot water extraction that penetrates deep into carpet fibres to dissolve trapped dirt, eliminate dust mites, neutralise pet odours, and lift stubborn stains.
                        </p>
                        <ul class="space-y-2 pt-2 text-xs sm:text-sm text-slate-700">
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>High-temperature steam sanitisation</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>Tough stain & pet odour neutralisation</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>Fast-drying, child & pet safe solutions</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>Suitable for wool, nylon & blended fibres</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <a href="{{ route('booking.create', ['service' => 'Carpet Cleaning']) }}" class="w-full block text-center py-3.5 px-6 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-md hover:bg-[#006da9] transition">
                            Book Carpet Cleaning
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. Pressure Washing -->
            <div class="bg-white rounded-3xl overflow-hidden shadow-lg border border-slate-200 flex flex-col hover:shadow-xl transition-all duration-300 group">
                <div class="relative overflow-hidden h-64">
                    <img src="{{ asset('images/residential-pressure-washing.jpg') }}" alt="Residential Pressure Washing Melbourne" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <div class="absolute top-4 left-4 bg-slate-900/80 backdrop-blur-md text-white text-xs font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5">
                        <span>💦</span> High-PSI Washing
                    </div>
                </div>
                <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-6">
                    <div class="space-y-3">
                        <h3 class="text-xl font-bold text-slate-900">Pressure Washing & Surface Cleaning</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Restore the pristine look of your outdoor spaces. High-pressure rotary surface cleaners remove slippery algae, moss, vehicle oil, dirt build-up, and weather stains.
                        </p>
                        <ul class="space-y-2 pt-2 text-xs sm:text-sm text-slate-700">
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>Concrete driveways, footpaths & patios</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>Brickwork, pavers & outdoor entertaining</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>Slippery moss & algae eradication</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>Pre-sale & seasonal exterior detailing</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <a href="{{ route('booking.create', ['service' => 'Pressure Washing']) }}" class="w-full block text-center py-3.5 px-6 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-md hover:bg-[#006da9] transition">
                            Book Pressure Washing
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. Window Cleaning -->
            <div class="bg-white rounded-3xl overflow-hidden shadow-lg border border-slate-200 flex flex-col hover:shadow-xl transition-all duration-300 group">
                <div class="relative overflow-hidden h-64">
                    <img src="{{ asset('images/residential-window-cleaning.jpg') }}" alt="Residential Window Cleaning Melbourne" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <div class="absolute top-4 left-4 bg-slate-900/80 backdrop-blur-md text-white text-xs font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5">
                        <span>🪟</span> Streak-Free Glass
                    </div>
                </div>
                <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-6">
                    <div class="space-y-3">
                        <h3 class="text-xl font-bold text-slate-900">Residential Window Cleaning</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Crystal-clear glass that lets natural light flood into your home. We clean interior and exterior glass panes, sliding doors, flyscreens, sills, and window tracks.
                        </p>
                        <ul class="space-y-2 pt-2 text-xs sm:text-sm text-slate-700">
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>Internal & external streak-free glass</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>Sliding doors, balustrades & mirrors</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>Flyscreens washed & window tracks vacuumed</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-[#0082c9] font-bold">✓</span>
                                <span>Single & double-storey residential reach</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <a href="{{ route('booking.create', ['service' => 'Window Cleaning']) }}" class="w-full block text-center py-3.5 px-6 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-md hover:bg-[#006da9] transition">
                            Book Window Cleaning
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

