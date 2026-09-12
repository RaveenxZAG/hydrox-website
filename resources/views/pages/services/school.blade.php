@extends('layouts.public')

@section('title', 'School & Educational Facility Cleaning Melbourne | Hydrox Facility Management')
@section('meta_description', 'Safe, non-toxic school, daycare, and higher education campus cleaning across Melbourne. Working With Children Checked staff.')

@section('content')
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <span class="inline-block px-3.5 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">Safe & Hygienic Campuses</span>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">School & Childcare Cleaning Services</h1>
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                    Protect students, educators, and campus visitors with rigorous, non-toxic sanitization. All Hydrox educational cleaners hold current Working With Children Checks (WWCC) and police verifications.
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('booking.create', ['service' => 'School Cleaning']) }}" class="px-8 py-4 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-xl shadow-sky-600/30 hover:bg-[#006da9] transition">
                        Request Education Tender
                    </a>
                    <a href="tel:0418222477" class="px-6 py-4 rounded-xl bg-white/10 text-white font-bold text-sm border border-white/20 hover:bg-white/15 transition flex items-center gap-2">
                        📞 0418 222 477
                    </a>
                </div>
            </div>
            <div class="lg:col-span-5">
                <img src="{{ asset('images/BB04C489-7C7A-4FFE-A966-95E45B414013.png') }}" alt="School and Childcare Cleaning" class="rounded-3xl shadow-2xl border border-white/10 w-full object-cover h-80 sm:h-96">
            </div>
        </div>
    </div>
</section>

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12">
            <div class="lg:col-span-8 space-y-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">Creating Clean Environments Where Children Thrive</h2>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                        High student density makes classrooms and playgrounds high-risk zones for germs and viruses. Hydrox provides specialized daily after-hours cleaning, deep term-break resets, and child-safe antimicrobial treatments.
                    </p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">School & Campus Scope</h3>
                    <div class="grid sm:grid-cols-2 gap-4 text-xs sm:text-sm text-slate-700">
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Classroom desks, whiteboards, chairs & tech equipment</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Student bathrooms deep scrub & antibacterial restocking</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Sports halls, gymnasiums, courts & locker rooms</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Staffrooms, administrative offices & sick bays</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Non-toxic, low-VOC eco-certified detergents</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="text-[#0082c9] font-bold">✓</span>
                            <span>Term break deep carpet steam extraction and floor resealing</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-sky-50 rounded-3xl p-6 sm:p-8 border border-sky-100 sticky top-28">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#0082c9]">Child-Safe Standards</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-1 mb-3">Campus Quote</h3>
                    <p class="text-xs text-slate-600 mb-6">Contact us for school tenders, private academies, or early learning centres.</p>

                    <a href="{{ route('booking.create', ['service' => 'School Cleaning']) }}" class="w-full block text-center py-3.5 px-6 rounded-xl bg-[#0082c9] text-white font-bold text-sm shadow-md hover:bg-[#006da9] transition mb-3">
                        Request School Quote
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
