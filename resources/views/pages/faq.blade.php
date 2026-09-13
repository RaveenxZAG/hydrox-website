@extends('layouts.public')

@section('title', 'Frequently Asked Questions | Hydrox Facility Management')
@section('meta_description', 'Common questions about Hydrox cleaning services, pricing, insurance, service areas in Melbourne, and booking procedures.')

@section('content')
<section class="bg-gradient-to-b from-slate-900 to-[#061b35] text-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <span class="inline-block px-3.5 py-1 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold uppercase tracking-wider">Help & Answers</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">Frequently Asked Questions</h1>
        <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto">
            Everything you need to know about our commercial and residential cleaning services, billing, insurance, and scheduling.
        </p>
    </div>
</section>

<section class="py-20 bg-white" x-data="{ activeFaq: null }">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @php
            $faqs = [
                [
                    'q' => 'What services does Hydrox Facility Management provide?',
                    'a' => 'Hydrox provides a full spectrum of facility solutions including commercial office cleaning, retail cleaning, residential house cleaning, NDIS and DVA daily living support, aged care and medical facility sanitization, industrial and warehouse scrubbing, school and childcare sanitizing, lawn and grounds maintenance, and concreting / pressure washing.'
                ],
                [
                    'q' => 'Are your cleaners police checked and insured?',
                    'a' => 'Yes, absolutely. 100% of our staff and subcontractors hold clean national police checks. Team members assigned to schools and childcare hold current Working With Children Checks (WWCC). Furthermore, we carry comprehensive Public Liability insurance for complete peace of mind.'
                ],
                [
                    'q' => 'What areas across Victoria do you cover?',
                    'a' => 'We service Greater Melbourne (CBD, Eastern, Western, Northern, and South-Eastern suburbs) as well as key Victorian regional hubs. If you are unsure whether we cover your location, simply enter your suburb and postcode in our quote form or call 0418 222 477.'
                ],
                [
                    'q' => 'How quickly can you start after I accept a quote?',
                    'a' => 'For urgent one-off or vacate jobs, we can often deploy within 24 to 48 hours. For ongoing commercial and facility contracts, we typically coordinate a walkthrough and commence service on your preferred schedule within 3 to 5 business days.'
                ],
                [
                    'q' => 'Do I need to supply cleaning products and equipment?',
                    'a' => 'No. Our professional teams arrive fully equipped with commercial-grade cleaning products, HEPA-filter vacuums, microfiber systems, and specialized machinery. If you have specific eco-friendly or hypoallergenic product preferences, we are happy to accommodate them.'
                ],
                [
                    'q' => 'How does billing work for NDIS and commercial clients?',
                    'a' => 'For NDIS participants, we provide compliant tax invoices itemized under Core Supports (Assistance with Daily Life) sent directly to your Plan Manager or for self-management claim. For commercial accounts, we offer straightforward monthly invoicing with flexible payment terms.'
                ],
                [
                    'q' => 'What is your satisfaction guarantee?',
                    'a' => 'If any area covered under your agreed scope does not meet your expectations, notify our team within 24 hours and we will dispatch a team to re-clean the specified area at zero additional charge.'
                ],
            ];
        @endphp

        @foreach ($faqs as $index => $faq)
            <div class="border border-slate-200 rounded-2xl overflow-hidden transition" :class="activeFaq === {{ $index }} ? 'border-[#0082c9] shadow-sm' : 'hover:border-slate-300'">
                <button type="button" @click="activeFaq = activeFaq === {{ $index }} ? null : {{ $index }}" class="w-full p-6 text-left flex items-center justify-between gap-4 bg-white select-none">
                    <span class="font-bold text-base text-slate-900">{{ $faq['q'] }}</span>
                    <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180 bg-sky-100 text-[#0082c9]': activeFaq === {{ $index }} }">
                        ↓
                    </span>
                </button>
                <div x-show="activeFaq === {{ $index }}" x-transition class="px-6 pb-6 text-sm text-slate-600 leading-relaxed bg-white border-t border-slate-100" x-cloak>
                    <p class="pt-3">{{ $faq['a'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="max-w-xl mx-auto px-4 mt-16 text-center bg-sky-50 rounded-3xl p-8 border border-sky-100">
        <h3 class="text-xl font-bold text-slate-900 mb-2">Still have a question?</h3>
        <p class="text-xs text-slate-600 mb-6">Our local operations managers in Melbourne are ready to answer your specific queries.</p>
        <div class="flex justify-center gap-3">
            <a href="{{ route('contact') }}" class="px-6 py-3 rounded-xl bg-white border border-slate-300 font-bold text-xs text-slate-800 hover:bg-slate-50">Contact Us</a>
            <a href="{{ route('booking.create') }}" class="px-6 py-3 rounded-xl bg-[#0082c9] text-white font-bold text-xs hover:bg-[#006da9]">Get Free Quote</a>
        </div>
    </div>
</section>
@endsection
