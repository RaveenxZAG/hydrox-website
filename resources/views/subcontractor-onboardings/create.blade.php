@extends('layouts.auth')
@section('auth-full-width', true)
@section('content')
    <div class="min-h-screen bg-[#eef3f6] px-4 py-8 sm:px-6 lg:px-8" x-data="onboardingSubmit">
        <div class="mx-auto max-w-6xl">
        <div x-show="submitting" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-slate-950/70 px-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-3xl border border-white/20 bg-white p-6 shadow-2xl">
                <div class="mb-4 flex items-center gap-3">
                    <div class="h-10 w-10 animate-spin rounded-full border-4 border-[#d4edf9] border-t-[#0082c9]"></div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Submitting onboarding</h2>
                        <p class="text-sm text-slate-500" x-text="submitMessage"></p>
                    </div>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-[#0082c9] transition-all duration-500 ease-out" :style="`width: ${progress}%`"></div>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs font-semibold text-slate-500">
                    <span>Uploading documents and saving application</span>
                    <span x-text="`${progress}%`"></span>
                </div>
            </div>
        </div>

        <section class="overflow-hidden rounded-[2rem] border border-white/15 bg-white shadow-[0_30px_90px_rgba(6,20,45,0.25)]">
            <div class="bg-[#06142d] px-6 py-7 text-white sm:px-9">
            <div class="flex flex-wrap items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <span class="grid h-14 w-40 place-items-center">
                        <img class="h-full w-full object-contain drop-shadow-[0_8px_20px_rgba(0,130,201,0.35)]" src="{{ asset('images/hydrox-logo.svg') }}" alt="Hydrox Facility Management logo">
                    </span>
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-[#76edbf]">Hydrox Facility Management</p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight sm:text-3xl">Subcontractor application</h1>
                    </div>
                </div>
                <a class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/15" href="{{ route('home') }}">Back to website</a>
            </div>
            <p class="mt-5 max-w-3xl text-sm leading-6 text-slate-300">Tell us about your cleaning business, compliance and availability to work with Hydrox across metropolitan Melbourne.</p>
            </div>

            <form method="POST" action="{{ route('subcontractor-onboardings.store') }}" enctype="multipart/form-data" class="grid gap-8 p-6 sm:p-9" @submit="startSubmit">
                @csrf

                <div class="rounded-2xl border border-[#b8dff3] bg-[#eaf6fc] px-5 py-4 text-sm text-[#0b2a4a]">
                    <p class="font-black">For independent subcontractors and cleaning businesses</p>
                    <p class="mt-1 leading-6">Use this form if you operate under an ABN and want to provide cleaning or facility services for Hydrox in Melbourne. This is not an employee application form.</p>
                </div>

                <div class="grid gap-4 rounded-3xl border border-slate-200 bg-slate-50/70 p-5 md:grid-cols-2 sm:p-6">
                    <div class="md:col-span-2"><p class="text-xs font-black uppercase tracking-[0.18em] text-[#0082c9]">Step 1</p><h2 class="mt-1 text-xl font-black">Business and contact details</h2></div>
                    <x-field label="Legal Business Name (if applicable)" name="legal_business_name"><input class="input" name="legal_business_name" value="{{ old('legal_business_name') }}" placeholder="Registered name shown on your ABN"></x-field>
                    <x-field label="Trading Name (if applicable)" name="trading_name"><input class="input" name="trading_name" value="{{ old('trading_name') }}" placeholder="Business or trading name"></x-field>
                    <x-field label="ABN" name="abn"><input class="input" name="abn" value="{{ old('abn') }}" placeholder="11-digit Australian Business Number" inputmode="numeric" pattern="[0-9]*" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required></x-field>
                    <x-field label="Business Structure" name="business_structure"><input class="input" name="business_structure" value="{{ old('business_structure') }}" placeholder="Sole trader, company, partnership" required></x-field>
                    <label class="flex items-center gap-3 pt-7 text-sm font-medium"><input type="hidden" name="gst_registered" value="0"><input class="rounded border-slate-300 text-[#0082c9]" type="checkbox" name="gst_registered" value="1" @checked(old('gst_registered'))> GST registered</label>
                    <x-field label="Primary Contact (if different)" name="contact_person"><input class="input" name="contact_person" value="{{ old('contact_person') }}" placeholder="Name of your operations contact"></x-field>
                    <x-field label="First Name" name="first_name"><input class="input" name="first_name" value="{{ old('first_name') }}" placeholder="Given name" required></x-field>
                    <x-field label="Last Name" name="last_name"><input class="input" name="last_name" value="{{ old('last_name') }}" placeholder="Family name" required></x-field>
                    <x-field label="Business Email" name="email"><input class="input" type="email" name="email" value="{{ old('email') }}" placeholder="name@business.com.au" required></x-field>
                    <x-field label="Mobile" name="mobile"><input class="input" name="mobile" value="{{ old('mobile') }}" placeholder="04xx xxx xxx" required></x-field>
                    <input type="hidden" name="position" value="Sub Contractor">
                    <x-field label="Business Address" name="business_address"><input class="input" name="business_address" value="{{ old('business_address') }}" placeholder="Street address, suburb, state and postcode" required></x-field>
                </div>

                <div class="grid gap-4 rounded-3xl border border-slate-200 p-5 md:grid-cols-2 sm:p-6" x-data="{ residency: @js(old('residency_status', '')) }">
                    <div class="md:col-span-2"><p class="text-xs font-black uppercase tracking-[0.18em] text-[#0082c9]">Step 2</p><h2 class="mt-1 text-xl font-black">Insurance and Victorian compliance</h2></div>
                    <x-field class="md:col-span-2" label="Citizenship or Residency Status" name="residency_status">
                        <select class="input" name="residency_status" x-model="residency" required>
                            <option value="">Select your status</option>
                            @foreach (\App\Models\SubcontractorOnboarding::RESIDENCY_STATUSES as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </x-field>
                    <div class="md:col-span-2 rounded-2xl border border-[#b8dff3] bg-[#eaf6fc] p-4 text-sm text-[#061b35]" x-show="residency === 'Australian Citizen'" x-cloak>
                        <p class="font-bold">Upload any one Australian citizenship document</p>
                        <div class="mt-3 grid gap-4 md:grid-cols-3">
                            <x-field label="Australian Passport" name="australian_passport"><input class="input" type="file" name="australian_passport" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"></x-field>
                            <x-field label="Birth Certificate" name="birth_certificate"><input class="input" type="file" name="birth_certificate" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"></x-field>
                            <x-field label="Citizenship Certificate" name="citizenship_certificate"><input class="input" type="file" name="citizenship_certificate" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"></x-field>
                        </div>
                        @error('citizenship_evidence')<p class="mt-2 font-semibold text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <template x-if="residency === 'Permanent Resident'">
                        <div class="contents">
                            <x-field label="Passport" name="passport"><input class="input" type="file" name="passport" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required></x-field>
                            <x-field label="Permanent Residency Evidence" name="permanent_residency_evidence"><input class="input" type="file" name="permanent_residency_evidence" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required></x-field>
                        </div>
                    </template>
                    <template x-if="residency === 'Other Visa Holder'">
                        <div class="contents">
                            <x-field label="Visa Type" name="visa_type"><input class="input" name="visa_type" value="{{ old('visa_type') }}" required></x-field>
                            <x-field label="Visa Expiry Date" name="visa_expiry_date"><input class="input" type="date" name="visa_expiry_date" value="{{ old('visa_expiry_date') }}" required></x-field>
                            <x-field label="Passport" name="passport"><input class="input" type="file" name="passport" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required></x-field>
                            <x-field label="Visa Evidence" name="visa_evidence"><input class="input" type="file" name="visa_evidence" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required></x-field>
                        </div>
                    </template>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 md:col-span-2">
                        <p class="font-bold">Before uploading documents</p>
                        <p class="mt-1 leading-6">Upload relevant current documents only: Public Liability Insurance, Victorian Working with Children Check, National Police Check, Driver Licence and Australian working-rights evidence. Leave optional documents blank if they are not relevant to your work.</p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-amber-800">Accepted file types: PDF, JPG, PNG, DOC, DOCX. Maximum size: 10 MB each.</p>
                    </div>
                    <x-field label="Insurance Expiry (if applicable)" name="insurance_expiry"><input class="input" type="date" name="insurance_expiry" value="{{ old('insurance_expiry') }}"></x-field>
                    <x-field label="Public Liability Insurance (if applicable)" name="public_liability_insurance"><input class="input" type="file" name="public_liability_insurance"></x-field>
                    <x-field label="Victorian Working with Children Check (if applicable)" name="workers_compensation_insurance"><input class="input" type="file" name="workers_compensation_insurance"></x-field>
                    <x-field label="National Police Check (if applicable)" name="police_clearance"><input class="input" type="file" name="police_clearance"></x-field>
                    <x-field label="Driver Licence (if applicable)" name="driver_licence"><input class="input" type="file" name="driver_licence"></x-field>
                    <x-field label="Australian Working Rights / Visa (if applicable)" name="working_rights"><input class="input" type="file" name="working_rights"></x-field>
                    <x-field label="Resume (required)" name="resume"><input class="input" type="file" name="resume" accept=".pdf,.doc,.docx" required></x-field>
                    <x-field label="Cover Letter Attachment (optional)" name="cover_letter_attachment"><input class="input" type="file" name="cover_letter_attachment" accept=".pdf,.doc,.docx"></x-field>
                    <x-field class="md:col-span-2" label="Cover Letter (optional)" name="cover_letter_text"><textarea class="input min-h-32" name="cover_letter_text" placeholder="Type your cover letter here, upload one above, or leave both blank.">{{ old('cover_letter_text') }}</textarea></x-field>
                </div>

                <div class="grid gap-4 rounded-3xl border border-slate-200 bg-slate-50/70 p-5 md:grid-cols-2 sm:p-6">
                    <div class="md:col-span-2"><p class="text-xs font-black uppercase tracking-[0.18em] text-[#0082c9]">Step 3</p><h2 class="mt-1 text-xl font-black">Services, experience and availability</h2></div>
                    @php $selectedSkills = old('skills', []); @endphp
                    <div class="md:col-span-2">
                        <p class="label">Hydrox service capabilities</p>
                        <p class="mt-1 text-sm text-slate-500">Select every service your business is equipped, insured and experienced to deliver.</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (\App\Models\SubcontractorOnboarding::skillOptions() as $skill)
                                <label class="cursor-pointer">
                                    <input class="peer sr-only" type="checkbox" name="skills[]" value="{{ $skill }}" @checked(in_array($skill, $selectedSkills, true))>
                                    <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 shadow-sm transition peer-checked:border-[#0082c9] peer-checked:bg-[#eaf6fc] peer-checked:text-[#07527d] hover:border-[#b8dff3] hover:bg-[#eaf6fc]">
                                        {{ $skill }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <x-field class="md:col-span-2" label="Melbourne Service Area and Availability" name="available_hours">
                        <textarea class="input min-h-28" name="available_hours" required placeholder="Example:&#10;Service area: Melbourne CBD, northern and eastern suburbs&#10;Monday-Friday: 6am-6pm&#10;After-hours commercial work: Available&#10;Weekends: By arrangement">{{ old('available_hours') }}</textarea>
                    </x-field>
                    <x-field class="md:col-span-2" label="Previous Experience" name="experience">
                        <textarea class="input min-h-32" name="experience" placeholder="Example:&#10;Three years delivering office, school and warehouse cleaning across Melbourne.&#10;Current capacity: two-person crew with own vehicle and equipment.&#10;Reference: Jane Smith, Facilities Manager, 0400 000 000.">{{ old('experience') }}</textarea>
                    </x-field>
                </div>

                <div class="grid gap-4 rounded-3xl border border-slate-200 p-5 md:grid-cols-2 sm:p-6">
                    <div class="md:col-span-2"><p class="text-xs font-black uppercase tracking-[0.18em] text-[#0082c9]">Step 4</p><h2 class="mt-1 text-xl font-black">Payment and final details</h2></div>
                    <x-field class="md:col-span-2" label="Business Bank Details" name="bank_details"><textarea class="input min-h-24" name="bank_details" required placeholder="Account name, BSB and account number">{{ old('bank_details') }}</textarea></x-field>
                    <x-field class="md:col-span-2" label="Superannuation (if applicable)" name="superannuation"><textarea class="input min-h-24" name="superannuation" placeholder="Fund name, member number and USI (only if requested)">{{ old('superannuation') }}</textarea></x-field>
                    <x-field class="md:col-span-2" label="Additional Information (if applicable)" name="notes"><textarea class="input min-h-24" name="notes" placeholder="Tell us about your crew size, vehicles, equipment, licences or site-access requirements">{{ old('notes') }}</textarea></x-field>
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-[#0082c9] px-7 py-4 text-sm font-black text-white shadow-xl shadow-[#0b2a4a]/20 transition hover:-translate-y-0.5 hover:bg-[#006da9] disabled:cursor-not-allowed disabled:opacity-70 sm:w-auto" :disabled="submitting">
                    <span x-text="submitting ? 'Submitting application...' : 'Submit subcontractor application'">Submit subcontractor application</span>
                </button>
            </form>
        </section>
        <p class="py-6 text-center text-xs text-slate-500">Hydrox Facility Management · Professional cleaning and facility services across Melbourne</p>
        </div>
    </div>
@endsection
