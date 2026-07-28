@extends('layouts.auth')
@section('content')
    <div class="mx-auto max-w-5xl" x-data="onboardingSubmit">
        <div x-show="submitting" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-slate-950/70 px-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-2xl">
                <div class="mb-4 flex items-center gap-3">
                    <div class="h-10 w-10 animate-spin rounded-full border-4 border-cyan-100 border-t-cyan-600"></div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Submitting onboarding</h2>
                        <p class="text-sm text-slate-500" x-text="submitMessage"></p>
                    </div>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-cyan-600 transition-all duration-500 ease-out" :style="`width: ${progress}%`"></div>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs font-semibold text-slate-500">
                    <span>Uploading documents and saving application</span>
                    <span x-text="`${progress}%`"></span>
                </div>
            </div>
        </div>

        <section class="panel p-6 sm:p-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="grid h-14 w-40 place-items-center rounded-2xl border border-slate-200 bg-white px-4 py-2 shadow-sm">
                        <img class="h-full w-full object-contain" src="{{ asset('images/hydrox-logo.svg') }}" alt="Hydrox Facility Management logo">
                    </span>
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-cyan-700">Hydrox Facility Management</p>
                        <h1 class="text-2xl font-bold">Subcontractor Onboarding Form</h1>
                    </div>
                </div>
                <a class="btn-secondary" href="{{ route('login') }}">Back to Home</a>
            </div>

            <form method="POST" action="{{ route('subcontractor-onboardings.store') }}" enctype="multipart/form-data" class="grid gap-6" @submit="startSubmit">
                @csrf

                <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-4 text-sm text-cyan-950">
                    <p class="font-bold">Subcontractor applications only</p>
                    <p class="mt-1 leading-6">Hydrox Facility Management works with subcontractors only. We do not accept casual, full-time, or part-time employee applications through this form. If you are applying as a subcontractor or business, please continue.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <h2 class="text-lg font-bold md:col-span-2">Company Information</h2>
                    <x-field label="Legal Business Name (if applicable)" name="legal_business_name"><input class="input" name="legal_business_name" value="{{ old('legal_business_name') }}"></x-field>
                    <x-field label="Trading Name (if applicable)" name="trading_name"><input class="input" name="trading_name" value="{{ old('trading_name') }}"></x-field>
                    <x-field label="ABN" name="abn"><input class="input" name="abn" value="{{ old('abn') }}" inputmode="numeric" pattern="[0-9]*" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required></x-field>
                    <x-field label="Business Structure" name="business_structure"><input class="input" name="business_structure" value="{{ old('business_structure') }}" placeholder="Sole trader, company, partnership" required></x-field>
                    <label class="flex items-center gap-3 pt-7 text-sm font-medium"><input type="hidden" name="gst_registered" value="0"><input class="rounded border-slate-300 text-cyan-600" type="checkbox" name="gst_registered" value="1" @checked(old('gst_registered'))> GST registered</label>
                    <x-field label="Contact Person (if applicable)" name="contact_person"><input class="input" name="contact_person" value="{{ old('contact_person') }}"></x-field>
                    <x-field label="First Name" name="first_name"><input class="input" name="first_name" value="{{ old('first_name') }}" required></x-field>
                    <x-field label="Last Name" name="last_name"><input class="input" name="last_name" value="{{ old('last_name') }}" required></x-field>
                    <x-field label="Email" name="email"><input class="input" type="email" name="email" value="{{ old('email') }}" required></x-field>
                    <x-field label="Mobile" name="mobile"><input class="input" name="mobile" value="{{ old('mobile') }}" required></x-field>
                    <input type="hidden" name="position" value="Sub Contractor">
                    <x-field label="Business Address / Personal Address" name="business_address"><input class="input" name="business_address" value="{{ old('business_address') }}" required></x-field>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <h2 class="text-lg font-bold md:col-span-2">Insurance & Compliance</h2>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 md:col-span-2">
                        <p class="font-bold">Before uploading documents</p>
                        <p class="mt-1 leading-6">Upload only documents that are relevant to you: Public Liability Insurance, Working with Children / Ochre Card, Police Clearance, Driver Licence, and Working Rights or VISA evidence. If a document is not relevant or you do not have it yet, leave it blank. Please do not upload unrelated photos, personal files, or duplicate documents unless administration asks for them.</p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-amber-800">Accepted file types: PDF, JPG, PNG, DOC, DOCX. Maximum size: 10 MB each.</p>
                    </div>
                    <x-field label="Insurance Expiry (if applicable)" name="insurance_expiry"><input class="input" type="date" name="insurance_expiry" value="{{ old('insurance_expiry') }}"></x-field>
                    <x-field label="Public Liability Insurance (if applicable)" name="public_liability_insurance"><input class="input" type="file" name="public_liability_insurance"></x-field>
                    <x-field label="Working with Children Check / Ochre Card (if applicable)" name="workers_compensation_insurance"><input class="input" type="file" name="workers_compensation_insurance"></x-field>
                    <x-field label="Police Clearance (if applicable)" name="police_clearance"><input class="input" type="file" name="police_clearance"></x-field>
                    <x-field label="Driver Licence (if applicable)" name="driver_licence"><input class="input" type="file" name="driver_licence"></x-field>
                    <x-field label="Working Rights / VISA document (if applicable)" name="working_rights"><input class="input" type="file" name="working_rights"></x-field>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <h2 class="text-lg font-bold md:col-span-2">Availability Details</h2>
                    @php $selectedSkills = old('skills', []); @endphp
                    <div class="md:col-span-2">
                        <p class="label">Cleaning Skills</p>
                        <p class="mt-1 text-sm text-slate-500">Select the work you can confidently perform.</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (\App\Models\SubcontractorOnboarding::skillOptions() as $skill)
                                <label class="cursor-pointer">
                                    <input class="peer sr-only" type="checkbox" name="skills[]" value="{{ $skill }}" @checked(in_array($skill, $selectedSkills, true))>
                                    <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 shadow-sm transition peer-checked:border-cyan-500 peer-checked:bg-cyan-50 peer-checked:text-cyan-800 hover:border-cyan-200 hover:bg-cyan-50">
                                        {{ $skill }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <x-field class="md:col-span-2" label="Availability" name="available_hours">
                        <textarea class="input min-h-24" name="available_hours" required placeholder="Example:&#10;Monday: 8am - 5pm&#10;Tuesday: 8am - 5pm&#10;Wednesday: Not available&#10;Saturday: 9am - 1pm">{{ old('available_hours') }}</textarea>
                    </x-field>
                    <x-field class="md:col-span-2" label="Previous Experience" name="experience">
                        <textarea class="input min-h-32" name="experience" placeholder="Example:&#10;ABC Cleaning Services - 3 years commercial cleaning in Darwin.&#10;Reference: Jane Smith, Supervisor, 0400 000 000.&#10;Location: Darwin CBD offices and schools.">{{ old('experience') }}</textarea>
                    </x-field>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <h2 class="text-lg font-bold md:col-span-2">Payment Details</h2>
                    <x-field class="md:col-span-2" label="Bank Details" name="bank_details"><textarea class="input min-h-24" name="bank_details" required>{{ old('bank_details') }}</textarea></x-field>
                    <x-field class="md:col-span-2" label="Superannuation (if applicable)" name="superannuation"><textarea class="input min-h-24" name="superannuation">{{ old('superannuation') }}</textarea></x-field>
                    <x-field class="md:col-span-2" label="Notes (if applicable)" name="notes"><textarea class="input min-h-24" name="notes">{{ old('notes') }}</textarea></x-field>
                </div>

                <button type="submit" class="btn-primary w-full disabled:cursor-not-allowed disabled:opacity-70 sm:w-auto" :disabled="submitting">
                    <span x-text="submitting ? 'Submitting...' : 'Submit Onboarding'">Submit Onboarding</span>
                </button>
            </form>
        </section>
    </div>
@endsection
