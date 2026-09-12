@extends('layouts.public')

@section('title', 'Residential Cleaning Services Melbourne | Hydrox Facility Management')
@section('meta_description', 'Trusted residential house cleaning, deep cleaning, and bond back vacate cleaning across Melbourne and surrounding Victoria.')

@section('content')
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <span class="inline-block px-3.5 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">Home Cleanliness</span>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">Residential & Home Cleaning Services</h1>
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                    Come home to a fresh, healthy, and spotless living space. Whether you need ongoing weekly maintenance, a thorough seasonal spring clean, or an end-of-lease bond clean, Hydrox delivers meticulous domestic care.
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
                        Balancing busy careers, families, and personal time is challenging. Our verified residential cleaners handle the heavy lifting, deep sanitizing kitchens, scrubbing bathrooms, and leaving your floors gleaming so you can enjoy your home.
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
                            <span>Optional: Oven deep clean, fridge inside & windows</span>
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
                    <p class="text-xs text-slate-600 mb-6">Tell us your suburb and number of bedrooms/bathrooms for a fast quote.</p>

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
@endsection
