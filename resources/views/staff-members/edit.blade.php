@extends('layouts.app')
@section('title', 'Edit Subcontractor')
@section('content')
    <form method="POST" action="{{ route('staff-members.update', $staffMember) }}" enctype="multipart/form-data" class="grid gap-6">
        @csrf
        @method('PUT')

        <x-card>
            <div class="mb-5">
                <h2 class="text-lg font-bold">{{ $staffMember->fullName() }}</h2>
                <div class="mt-2">
                    <span class="badge bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">Hydrox subcontractor profile</span>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <x-field label="First Name" name="first_name">
                    <input class="input" name="first_name" value="{{ old('first_name', $staffMember->first_name) }}">
                </x-field>
                <x-field label="Last Name" name="last_name">
                    <input class="input" name="last_name" value="{{ old('last_name', $staffMember->last_name) }}">
                </x-field>
                <x-field label="Email" name="email">
                    <input class="input" type="email" name="email" value="{{ old('email', $staffMember->email) }}">
                </x-field>
                <x-field label="Mobile" name="mobile">
                    <input class="input" name="mobile" value="{{ old('mobile', $staffMember->mobile) }}">
                </x-field>
                <x-field label="Job Title" name="job_title">
                    <input class="input" name="job_title" value="{{ old('job_title', $staffMember->job_title) }}">
                </x-field>
                <x-field label="Security Role" name="security_role">
                    @if (! empty($roleNames))
                        <select class="input" name="security_role">
                            <option value="">Select role</option>
                            @foreach ($roleNames as $uuid => $name)
                                <option value="{{ $uuid }}" @selected(old('security_role', $staffMember->security_role) === $uuid)>{{ $name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input class="input" name="security_role" value="{{ old('security_role', $staffMember->security_role) }}" placeholder="Security role">
                    @endif
                    <p class="mt-1 text-xs text-slate-500">Current role: {{ $staffMember->securityRoleName($roleNames) }}</p>
                </x-field>
                <x-field label="Schedule Colour" name="schedule_colour">
                    <input class="input" name="schedule_colour" value="{{ old('schedule_colour', $staffMember->schedule_colour) }}" placeholder="#0082c9">
                </x-field>
                <x-field label="Labour Rate" name="labour_rate">
                    <input class="input" type="number" step="0.01" min="0" name="labour_rate" value="{{ old('labour_rate', $staffMember->labour_rate) }}">
                </x-field>
                <x-field label="Subcontractor Status" name="staff_status">
                    <select class="input" name="staff_status" required>
                        @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'archived' => 'Archived'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('staff_status', $staffMember->staff_status ?: 'active') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-field>
                <x-field label="Address" name="address">
                    <input class="input" name="address" value="{{ old('address', $staffMember->address) }}">
                </x-field>
                <x-field label="Emergency Contact" name="emergency_contact">
                    <input class="input" name="emergency_contact" value="{{ old('emergency_contact', $staffMember->emergency_contact) }}">
                </x-field>
                <x-field label="Legal Business Name (if applicable)" name="legal_business_name">
                    <input class="input" name="legal_business_name" value="{{ old('legal_business_name', $staffMember->legal_business_name) }}">
                </x-field>
                <x-field label="Trading Name (if applicable)" name="trading_name">
                    <input class="input" name="trading_name" value="{{ old('trading_name', $staffMember->trading_name) }}">
                </x-field>
                <x-field label="ABN" name="abn">
                    <input class="input" name="abn" value="{{ old('abn', $staffMember->abn) }}" inputmode="numeric" pattern="[0-9]*" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </x-field>
                <x-field label="Business Structure" name="business_structure">
                    <input class="input" name="business_structure" value="{{ old('business_structure', $staffMember->business_structure) }}" placeholder="Sole trader, company, partnership">
                </x-field>
                <x-field label="Contact Person (if applicable)" name="contact_person">
                    <input class="input" name="contact_person" value="{{ old('contact_person', $staffMember->contact_person) }}">
                </x-field>
                <x-field label="Business / Personal Address" name="business_address">
                    <input class="input" name="business_address" value="{{ old('business_address', $staffMember->business_address) }}">
                </x-field>
                <label class="flex items-center gap-3 pt-2 text-sm font-medium">
                    <input type="hidden" name="gst_registered" value="0">
                    <input class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" type="checkbox" name="gst_registered" value="1" @checked(old('gst_registered', $staffMember->gst_registered))>
                    GST registered
                </label>
                <x-field label="Insurance Expiry" name="insurance_expiry">
                    <input class="input" type="date" name="insurance_expiry" value="{{ old('insurance_expiry', optional($staffMember->insurance_expiry)->format('Y-m-d')) }}">
                </x-field>
                <x-field class="md:col-span-2" label="Availability" name="availability">
                    <textarea class="input min-h-32" name="availability" placeholder="Example:&#10;Monday: 8am - 5pm&#10;Tuesday: 8am - 5pm">{{ old('availability', $staffMember->availability) }}</textarea>
                </x-field>
                <x-field class="md:col-span-2" label="Bank Details" name="bank_details">
                    <textarea class="input min-h-32" name="bank_details">{{ old('bank_details', $staffMember->bank_details) }}</textarea>
                </x-field>
                <x-field class="md:col-span-2" label="Superannuation" name="superannuation">
                    <textarea class="input min-h-32" name="superannuation">{{ old('superannuation', $staffMember->superannuation) }}</textarea>
                </x-field>
                <label class="flex items-center gap-3 pt-2 text-sm font-medium">
                    <input type="hidden" name="active" value="0">
                    <input class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" type="checkbox" name="active" value="1" @checked(old('active', $staffMember->active))>
                    Active
                </label>
                <label class="flex items-center gap-3 pt-2 text-sm font-medium">
                    <input type="hidden" name="portal_access_enabled" value="0">
                    <input class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" type="checkbox" name="portal_access_enabled" value="1" @checked(old('portal_access_enabled', $staffMember->portal_access_enabled ?? true))>
                    Subcontractor portal access enabled
                </label>
                <label class="flex items-center gap-3 pt-2 text-sm font-medium">
                    <input type="hidden" name="invoicing_enabled" value="0">
                    <input class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" type="checkbox" name="invoicing_enabled" value="1" @checked(old('invoicing_enabled', $staffMember->invoicing_enabled ?? true))>
                    Invoicing enabled
                </label>
                <label class="flex items-center gap-3 pt-2 text-sm font-medium">
                    <input type="hidden" name="show_on_schedule" value="0">
                    <input class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" type="checkbox" name="show_on_schedule" value="1" @checked(old('show_on_schedule', $staffMember->show_on_schedule))>
                    Show on schedule
                </label>
                <x-field class="md:col-span-2" label="Notes" name="notes">
                    <textarea class="input min-h-32" name="notes">{{ old('notes', $staffMember->notes) }}</textarea>
                </x-field>
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-lg font-bold">Skills & Experience</h2>
            @php $selectedSkills = old('skills', $staffMember->skills ?? []); @endphp
            <div class="grid gap-4">
                <div>
                    <p class="label">Cleaning Skills</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach (\App\Models\SubcontractorOnboarding::skillOptions() as $skill)
                            <label class="cursor-pointer">
                                <input class="peer sr-only" type="checkbox" name="skills[]" value="{{ $skill }}" @checked(in_array($skill, $selectedSkills, true))>
                                <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 shadow-sm transition peer-checked:border-cyan-500 peer-checked:bg-cyan-50 peer-checked:text-cyan-800 hover:border-cyan-200 hover:bg-cyan-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                                    {{ $skill }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <x-field label="Previous Experience" name="experience">
                    <textarea class="input min-h-32" name="experience" placeholder="Example:&#10;Three years delivering commercial cleaning across Melbourne.&#10;Reference: Jane Smith, Facilities Manager, 0400 000 000.&#10;Service area: Melbourne CBD and surrounding suburbs.">{{ old('experience', $staffMember->experience) }}</textarea>
                </x-field>
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-lg font-bold">Insurance & Compliance Documents</h2>
            <p class="mb-4 text-sm text-slate-500">Upload only relevant replacement files. PDF, Word, JPG, PNG, or WebP. Maximum 10 MB each.</p>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($staffMember->documentFields() as $field => $label)
                    @php $currentVersion = $staffMember->currentDocumentsByCategory()->get($field)?->currentVersion; @endphp
                    <x-field :label="$label" :name="$field">
                        <input class="input file:mr-3 file:rounded-md file:border-0 file:bg-cyan-50 file:px-3 file:py-2 file:text-cyan-700" type="file" name="{{ $field }}" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*">
                        @if ($currentVersion || $staffMember->{$field})
                            <p class="mt-2 text-xs text-slate-500">Current file: {{ $currentVersion?->original_filename ?: $staffMember->documentName($field) }}</p>
                        @else
                            <p class="mt-2 text-xs text-amber-700">Missing document.</p>
                        @endif
                    </x-field>
                @endforeach
                <x-field class="md:col-span-2" label="Replacement Reason (applies to new files)" name="replacement_reason"><textarea class="input min-h-20" name="replacement_reason">{{ old('replacement_reason') }}</textarea></x-field>
            </div>
        </x-card>

        <div class="flex justify-end gap-3">
            <a class="btn-secondary" href="{{ route('staff-members.show', $staffMember) }}">Cancel</a>
            <button class="btn-primary">Save Subcontractor Details</button>
        </div>
    </form>
@endsection
