@extends('layouts.public')

@section('title', 'Industrial & Warehouse Cleaning Melbourne | Hydrox Facility Management')
@section('meta_description', 'Heavy industrial, factory, warehouse, and distribution center cleaning across Victoria. Ride-on floor scrubbers, degreasing, and safety compliant.')

@section('content')
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <span class="inline-block px-3.5 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">Heavy-Duty Operations</span>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">Industrial & Warehouse Cleaning Services</h1>
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                    Maintain safety, compliance, and operational efficiency across your factory, storage yard, logistics terminal, or distribution facility with specialized industrial cleaning solutions.
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('booking.create', ['service' => 'Industrial & Warehouse']) }}" class="px-8 py-4 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-xl shadow-sky-600/30 hover:bg-[#006da9] transition">
                        Get Industrial Quote
                    </a>
                    <a href="tel:0418222477" class="px-6 py-4 rounded-xl bg-white/10 text-white font-bold text-sm border border-white/20 hover:bg-white/15 transition flex items-center gap-2">
                        📞 0418 222 477
                    </a>
                </div>
            </div>
            <div class="lg:col-span-5">
                <img src="{{ asset('images/BD1AA40B-5901-4391-8F1D-F5A685B48CD3.png') }}" alt="Industrial and Warehouse Cleaning" class="rounded-3xl shadow-2xl border border-white/10 w-full object-cover h-80 sm:h-96">
            </div>
        </div>
    </div>
</section>

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12">
            <div class="lg:col-span-8 space-y-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">Industrial Grade Machinery & Experienced Crews</h2>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                        Heavy industrial environments generate unique challenges: chemical residues, forklift tire marks, oil spills, and high-altitude warehouse dust. Our crews are trained in OH&S protocols, white card certified, and equipped with ride-on scrubbers and high-pressure washing gear.
                    </p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Capabilities & Industrial Scope</h3>
                    <div class="grid sm:grid-cols-2 gap-4 text-xs sm:text-sm text-slate-700">
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>High-capacity mechanical floor scrubbing & sweeping</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Forklift tire scuff removal & heavy degreasing</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Loading dock, bay & waste corral pressure cleaning</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>High-level rafter, duct, truss & pipe de-dusting</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Factory workshop staff amenities & shower blocks</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Site office & security gatehouse daily cleaning</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-sky-50 rounded-3xl p-6 sm:p-8 border border-sky-100 sticky top-28">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Heavy Duty Solutions</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-1 mb-3">Warehouse Quote</h3>
                    <p class="text-xs text-slate-600 mb-6">Contact our industrial cleaning operations team for an on-site site assessment.</p>

                    <a href="{{ route('booking.create', ['service' => 'Industrial & Warehouse']) }}" class="w-full block text-center py-3.5 px-6 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-md hover:bg-[#006da9] transition mb-3">
                        Request Industrial Quote
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
