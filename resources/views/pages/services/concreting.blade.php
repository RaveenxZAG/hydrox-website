@extends('layouts.public')

@section('title', 'Concreting & Pressure Cleaning Melbourne | Hydrox Facility Management')
@section('meta_description', 'High-pressure cleaning, concrete driveway rejuvenation, pathway restoration, and concreting services across Melbourne.')

@section('content')
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <span class="inline-block px-3.5 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">Hard Surfaces & Restoration</span>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">Concreting & Pressure Cleaning Services</h1>
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                    Restore dull, stained concrete surfaces to pristine condition. Hydrox provides commercial high-pressure power washing, driveway sealing, slab repairs, and expert concreting across Melbourne.
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('booking.create', ['service' => 'Concreting']) }}" class="px-8 py-4 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-xl shadow-sky-600/30 hover:bg-[#006da9] transition">
                        Get Concreting Quote
                    </a>
                    <a href="tel:0418222477" class="px-6 py-4 rounded-xl bg-white/10 text-white font-bold text-sm border border-white/20 hover:bg-white/15 transition flex items-center gap-2">
                        📞 0418 222 477
                    </a>
                </div>
            </div>
            <div class="lg:col-span-5">
                <img src="{{ asset('images/F96E5EB4-AE8A-4B55-8C6B-45C198FAEC6F.png') }}" alt="Concreting and Pressure Cleaning" class="rounded-3xl shadow-2xl border border-white/10 w-full object-cover h-80 sm:h-96">
            </div>
        </div>
    </div>
</section>

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12">
            <div class="lg:col-span-8 space-y-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">Powerful Restoration For Hard Surface Facilities</h2>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                        Outdoor walkways, car parks, loading zones, and driveways accumulate moss, oil, rubber, and airborne pollutants that look unsightly and create hazardous slip conditions. Our industrial pressure washing machines and concreting specialists revitalize surfaces quickly and safely.
                    </p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Hard Surface Scope</h3>
                    <div class="grid sm:grid-cols-2 gap-4 text-xs sm:text-sm text-slate-700">
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>High-pressure hot & cold water rotary floor cleaning</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Driveways, footpaths, car park bays & loading ramps</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Oil stain, tire mark & chewing gum extraction</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Protective clear & tinted concrete sealant application</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Slab repairs, expansion joint cutting & minor concrete works</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Graffiti removal from masonry and brickwork</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-sky-50 rounded-3xl p-6 sm:p-8 border border-sky-100 sticky top-28">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Concrete & Wash</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-1 mb-3">Pressure Quote</h3>
                    <p class="text-xs text-slate-600 mb-6">Contact our team with your square meterage or photos for a prompt estimate.</p>

                    <a href="{{ route('booking.create', ['service' => 'Concreting']) }}" class="w-full block text-center py-3.5 px-6 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-md hover:bg-[#006da9] transition mb-3">
                        Request Concreting Quote
                    </a>
                    <a href="tel:0418222477" class="w-full block text-center py-3.5 px-6 rounded-xl bg-white border border-slate-300 text-slate-800 font-bold text-sm hover:bg-slate-50 transition">
                        Call 0418 222 477
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Recent Work</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2">Quality Concreting & Driveway Finishes</h2>
            <p class="text-sm text-slate-600 mt-2">Precision exposed aggregate, clean edges, and durable sealing across Melbourne properties.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="overflow-hidden rounded-2xl shadow-md border border-slate-200">
                <img src="{{ asset('images/WhatsApp Image 2025-11-13 at 19.59.44 (2).jpeg') }}" alt="Exposed aggregate driveway" class="w-full h-56 object-cover hover:scale-105 transition duration-300">
            </div>
            <div class="overflow-hidden rounded-2xl shadow-md border border-slate-200">
                <img src="{{ asset('images/WhatsApp Image 2025-11-13 at 19.59.45 (2).jpeg') }}" alt="Exposed aggregate pathway" class="w-full h-56 object-cover hover:scale-105 transition duration-300">
            </div>
            <div class="overflow-hidden rounded-2xl shadow-md border border-slate-200">
                <img src="{{ asset('images/WhatsApp Image 2025-11-13 at 19.59.47 (1).jpeg') }}" alt="Finished concrete driveway" class="w-full h-56 object-cover hover:scale-105 transition duration-300">
            </div>
            <div class="overflow-hidden rounded-2xl shadow-md border border-slate-200">
                <img src="{{ asset('images/WhatsApp Image 2025-11-13 at 19.59.49.jpeg') }}" alt="Exposed aggregate side pathway" class="w-full h-56 object-cover hover:scale-105 transition duration-300">
            </div>
        </div>
    </div>
</section>
@endsection
