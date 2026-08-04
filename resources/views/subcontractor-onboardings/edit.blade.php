@extends('layouts.app')
@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-[#006da9]">Subcontractor Onboarding</p>
            <h1 class="text-2xl font-bold">Edit Details</h1>
        </div>
        <a class="btn-secondary" href="{{ route('subcontractor-onboardings.show', $onboarding) }}">Back to Review</a>
    </div>

    <form method="POST" action="{{ route('subcontractor-onboardings.update', $onboarding) }}" enctype="multipart/form-data" class="grid gap-6">
        @csrf
        @method('PUT')

        <section class="panel grid gap-4 p-6 md:grid-cols-2">
            <h2 class="text-lg font-bold md:col-span-2">Company Information</h2>
            <x-field label="Status" name="status">
                <select class="input" name="status" required>
                    @foreach (\App\Models\SubcontractorOnboarding::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $onboarding->status) === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </x-field>
            <label class="flex items-center gap-3 pt-7 text-sm font-medium"><input type="hidden" name="gst_registered" value="0"><input class="rounded border-slate-300 text-[#0082c9]" type="checkbox" name="gst_registered" value="1" @checked(old('gst_registered', $onboarding->gst_registered))> GST registered</label>
            <x-field label="Legal Business Name (if applicable)" name="legal_business_name"><input class="input" name="legal_business_name" value="{{ old('legal_business_name', $onboarding->legal_business_name) }}"></x-field>
            <x-field label="Trading Name (if applicable)" name="trading_name"><input class="input" name="trading_name" value="{{ old('trading_name', $onboarding->trading_name) }}"></x-field>
            <x-field label="ABN" name="abn"><input class="input" name="abn" value="{{ old('abn', $onboarding->abn) }}" inputmode="numeric" pattern="[0-9]*" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required></x-field>
            <x-field label="Business Structure" name="business_structure"><input class="input" name="business_structure" value="{{ old('business_structure', $onboarding->business_structure) }}" required></x-field>
            <x-field label="Contact Person (if applicable)" name="contact_person"><input class="input" name="contact_person" value="{{ old('contact_person', $onboarding->contact_person) }}"></x-field>
            <x-field label="First Name" name="first_name"><input class="input" name="first_name" value="{{ old('first_name', $onboarding->first_name) }}" required></x-field>
            <x-field label="Last Name" name="last_name"><input class="input" name="last_name" value="{{ old('last_name', $onboarding->last_name) }}" required></x-field>
            <x-field label="Email" name="email"><input class="input" type="email" name="email" value="{{ old('email', $onboarding->email) }}" required></x-field>
            <x-field label="Mobile" name="mobile"><input class="input" name="mobile" value="{{ old('mobile', $onboarding->mobile) }}" required></x-field>
            <input type="hidden" name="position" value="Sub Contractor">
            <x-field class="md:col-span-2" label="Business Address / Personal Address" name="business_address"><input class="input" name="business_address" value="{{ old('business_address', $onboarding->business_address) }}" required></x-field>
            <x-field label="Citizenship or Residency Status" name="residency_status">
                <select class="input" name="residency_status">
                    <option value="">Legacy record — not recorded</option>
                    @foreach (\App\Models\SubcontractorOnboarding::RESIDENCY_STATUSES as $residencyStatus)
                        <option value="{{ $residencyStatus }}" @selected(old('residency_status', $onboarding->residency_status) === $residencyStatus)>{{ $residencyStatus }}</option>
                    @endforeach
                </select>
            </x-field>
            <x-field label="Visa Type" name="visa_type"><input class="input" name="visa_type" value="{{ old('visa_type', $onboarding->visa_type) }}"></x-field>
            <x-field label="Visa Expiry Date" name="visa_expiry_date"><input class="input" type="date" name="visa_expiry_date" value="{{ old('visa_expiry_date', $onboarding->visa_expiry_date?->format('Y-m-d')) }}"></x-field>
        </section>

        <section class="panel grid gap-4 p-6 md:grid-cols-2">
            <h2 class="text-lg font-bold md:col-span-2">Insurance & Compliance</h2>
            <x-field label="Insurance Expiry (if applicable)" name="insurance_expiry"><input class="input" type="date" name="insurance_expiry" value="{{ old('insurance_expiry', $onboarding->insurance_expiry?->format('Y-m-d')) }}"></x-field>
            @foreach ($onboarding->documentFields() as $field => $label)
                @php $currentVersion = $onboarding->currentDocumentsByCategory()->get($field)?->currentVersion; @endphp
                <div class="grid gap-2 rounded-lg border border-slate-200 p-4">
                    <p class="text-sm font-semibold">{{ $label }}</p>
                    @if ($field === 'uploaded_certificates')
                        <p class="text-xs text-slate-500">Upload new certificates to replace the current certificate set.</p>
                        @if ($onboarding->uploaded_certificates)
                            <a class="text-sm font-semibold text-[#006da9]" href="{{ route('subcontractor-onboardings.documents.download', [$onboarding, $field]) }}">Current certificates available</a>
                        @else
                            <p class="text-sm text-slate-500">No current certificates.</p>
                        @endif
                        <input class="input" type="file" name="uploaded_certificates[]" multiple>
                    @else
                        @if ($currentVersion || $onboarding->{$field})
                            <a class="text-sm font-semibold text-[#006da9]" href="{{ route('subcontractor-onboardings.documents.download', [$onboarding, $field]) }}">Download current file</a>
                        @else
                            <p class="text-sm text-slate-500">No current file.</p>
                        @endif
                        <input class="input" type="file" name="{{ $field }}">
                    @endif
                </div>
            @endforeach
            <x-field class="md:col-span-2" label="Replacement Reason (applies to new files)" name="replacement_reason"><textarea class="input min-h-20" name="replacement_reason">{{ old('replacement_reason') }}</textarea></x-field>
        </section>

        <section class="panel grid gap-4 p-6 md:grid-cols-2">
            <h2 class="text-lg font-bold md:col-span-2">Availability Details</h2>
            @php $selectedSkills = old('skills', $onboarding->skills ?? []); @endphp
            <div class="md:col-span-2">
                <p class="label">Cleaning Skills</p>
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
            <x-field class="md:col-span-2" label="Availability" name="available_hours">
                <textarea class="input min-h-24" name="available_hours" required placeholder="Example:&#10;Monday: 8am - 5pm&#10;Tuesday: 8am - 5pm&#10;Wednesday: Not available&#10;Saturday: 9am - 1pm">{{ old('available_hours', $onboarding->available_hours) }}</textarea>
            </x-field>
            <x-field class="md:col-span-2" label="Previous Experience" name="experience">
                <textarea class="input min-h-32" name="experience" placeholder="Example:&#10;Three years delivering commercial cleaning across Melbourne.&#10;Reference: Jane Smith, Facilities Manager, 0400 000 000.&#10;Service area: Melbourne CBD and surrounding suburbs.">{{ old('experience', $onboarding->experience) }}</textarea>
            </x-field>
            <x-field class="md:col-span-2" label="Typed Cover Letter" name="cover_letter_text"><textarea class="input min-h-32" name="cover_letter_text">{{ old('cover_letter_text', $onboarding->cover_letter_text) }}</textarea></x-field>
        </section>

        <section class="panel grid gap-4 p-6 md:grid-cols-2">
            <h2 class="text-lg font-bold md:col-span-2">Payment Details</h2>
            <x-field class="md:col-span-2" label="Bank Details" name="bank_details"><textarea class="input min-h-24" name="bank_details" required>{{ old('bank_details', $onboarding->bank_details) }}</textarea></x-field>
            <x-field class="md:col-span-2" label="Superannuation (if applicable)" name="superannuation"><textarea class="input min-h-24" name="superannuation">{{ old('superannuation', $onboarding->superannuation) }}</textarea></x-field>
            <x-field class="md:col-span-2" label="Notes (if applicable)" name="notes"><textarea class="input min-h-24" name="notes">{{ old('notes', $onboarding->notes) }}</textarea></x-field>
        </section>

        <div class="flex flex-wrap gap-3">
            <button class="btn-primary">Save Changes</button>
            <a class="btn-secondary" href="{{ route('subcontractor-onboardings.show', $onboarding) }}">Cancel</a>
        </div>
    </form>
@endsection
