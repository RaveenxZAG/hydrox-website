@extends('layouts.auth')

@section('auth-full-width', true)
@section('hide-error-banner', true)

@section('content')
    @php
        $fullName = $staff->fullName();
        $initials = collect([$staff->first_name ?? null, $staff->last_name ?? null])
            ->filter()
            ->map(fn ($name) => str($name)->substr(0, 1)->upper()->toString())
            ->join('');
        $initials = $initials !== '' ? $initials : str($fullName)->substr(0, 2)->upper()->toString();

        $inputClass = 'block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-[#0082c9] focus:outline-none focus:ring-4 focus:ring-cyan-100';
        $fileClass = 'block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-cyan-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-[#0082c9] focus:border-[#0082c9] focus:outline-none focus:ring-4 focus:ring-cyan-100';
        $errorClass = fn (string $field): string => $errors->has($field) || $errors->has($field.'.*') ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-100' : '';
        $formValue = fn (string $field, mixed $default = null): mixed => $errors->any() ? old($field, $default) : $default;
        $uploadLimits = $uploadLimits ?? [
            'file_bytes' => 10 * 1024 * 1024,
            'post_bytes' => 10 * 1024 * 1024,
            'file_label' => '10 MB',
            'post_label' => '10 MB',
        ];
    @endphp

    <div class="min-h-screen bg-[#F8FAFC] text-slate-950">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-[1440px] flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-5">
                    <div class="flex items-center gap-3">
                        <img class="h-10 w-28 object-contain" src="{{ asset('images/hydrox-logo.svg') }}" alt="Hydrox Facility Management">
                        <div class="leading-tight">
                            <p class="text-base font-black tracking-tight text-slate-950">Hydrox Facility Management</p>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0082c9]">Subcontractor Portal</p>
                        </div>
                    </div>
                    <div class="hidden h-9 w-px bg-slate-200 sm:block"></div>
                    <div class="hidden items-center gap-2 text-sm font-bold text-slate-700 sm:flex">
                        <svg class="h-5 w-5 text-[#0082c9]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M15 3.4a4 4 0 0 1 0 7.2M21 21v-2a4 4 0 0 0-3-3.87M11 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Update Profile
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50" href="{{ route('staff-portal.invoices') }}">
                        <svg class="h-4 w-4 text-[#0082c9]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2v6h6M9 13h6M9 17h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Work Logs
                    </a>
                    <div class="hidden items-center gap-3 sm:flex">
                        <div class="grid h-11 w-11 place-items-center rounded-full bg-[#0082c9] text-sm font-black text-white shadow-sm">{{ $initials }}</div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $fullName }}</p>
                            <p class="text-xs text-slate-500">Subcontractor</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('staff-portal.logout') }}">
                        @csrf
                        <button class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8">
            <section>
                <p class="text-sm font-black uppercase tracking-[0.18em] text-[#0082c9]">Subcontractor profile</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Update Your Profile</h1>
                <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Send updated contact, business, payment, availability, skills, experience, or document details to the office for approval.</p>

                <div class="mt-5 grid gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_12px_34px_rgba(15,23,42,0.05)] sm:grid-cols-3">
                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-cyan-50 text-[#0082c9]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M20 21a8 8 0 1 0-16 0M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Current profile</p>
                            <p class="truncate font-black text-slate-950">{{ $fullName }}</p>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Email / Mobile</p>
                        <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $staff->email ?: 'No email' }}</p>
                        <p class="truncate text-sm text-slate-600">{{ $staff->mobile ?: 'No mobile' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Review status</p>
                        <p class="mt-2 inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $pendingProfileChange ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-green-200 bg-green-50 text-green-700' }}">
                            {{ $pendingProfileChange ? 'Pending admin review' : 'Ready for update' }}
                        </p>
                    </div>
                </div>
            </section>

            @if ($errors->any())
                <section class="mt-8 rounded-3xl border border-red-200 bg-red-50 p-5 text-sm text-red-900 shadow-[0_18px_45px_rgba(15,23,42,0.05)]">
                    <p class="font-black">Profile update was not sent</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($pendingProfileChange)
                <section class="mt-8 rounded-3xl border border-amber-200 bg-amber-50 p-6 shadow-[0_18px_45px_rgba(15,23,42,0.05)]">
                    <div class="flex items-start gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-amber-700 shadow-sm">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 8v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-black text-slate-950">Profile update already submitted</h2>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-700">Your latest profile update is waiting for admin review. You can submit another update after the current one is approved or rejected.</p>
                        </div>
                    </div>
                </section>
            @else
                <form id="staff-profile-update-form" method="POST" action="{{ route('staff-portal.profile-update') }}" enctype="multipart/form-data" class="mt-6 grid gap-6">
                    @csrf
                    <div id="staff-profile-file-error" class="hidden rounded-3xl border border-red-200 bg-red-50 p-5 text-sm font-semibold text-red-900 shadow-[0_18px_45px_rgba(15,23,42,0.05)]"></div>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
                        <div class="flex items-start gap-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-cyan-50 text-[#0082c9]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.7.6 2.5a2 2 0 0 1-.5 2.1L8 9.5a16 16 0 0 0 6.5 6.5l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.6.5 2.5.6a2 2 0 0 1 1.7 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-black tracking-tight text-slate-950">Contact Details</h2>
                                <p class="mt-1 text-sm text-slate-500">Keep your phone, email, address, and emergency contact up to date.</p>
                            </div>
                        </div>
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Email<input class="{{ $inputClass }} {{ $errorClass('email') }}" type="email" name="email" value="{{ $formValue('email', $staff->email) }}">@error('email')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Mobile<input class="{{ $inputClass }} {{ $errorClass('mobile') }}" name="mobile" value="{{ $formValue('mobile', $staff->mobile) }}">@error('mobile')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Address<input class="{{ $inputClass }} {{ $errorClass('address') }}" name="address" value="{{ $formValue('address', $staff->address) }}">@error('address')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Emergency Contact<input class="{{ $inputClass }} {{ $errorClass('emergency_contact') }}" name="emergency_contact" value="{{ $formValue('emergency_contact', $staff->emergency_contact) }}">@error('emergency_contact')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
                        <div class="flex items-start gap-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-blue-50 text-blue-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9h.01M9 13h.01M9 17h.01M15 13h.01M15 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-black tracking-tight text-slate-950">Business Details</h2>
                                <p class="mt-1 text-sm text-slate-500">Complete these if you work under a business, company, or trading name.</p>
                            </div>
                        </div>
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Legal Business Name<input class="{{ $inputClass }} {{ $errorClass('legal_business_name') }}" name="legal_business_name" value="{{ $formValue('legal_business_name', $staff->legal_business_name) }}">@error('legal_business_name')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Trading Name<input class="{{ $inputClass }} {{ $errorClass('trading_name') }}" name="trading_name" value="{{ $formValue('trading_name', $staff->trading_name) }}">@error('trading_name')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">ABN<input class="{{ $inputClass }} {{ $errorClass('abn') }}" name="abn" value="{{ $formValue('abn', $staff->abn) }}" inputmode="numeric" pattern="[0-9]*" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">@error('abn')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Business Structure<input class="{{ $inputClass }} {{ $errorClass('business_structure') }}" name="business_structure" value="{{ $formValue('business_structure', $staff->business_structure) }}" placeholder="Sole trader, company, partnership">@error('business_structure')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Contact Person<input class="{{ $inputClass }} {{ $errorClass('contact_person') }}" name="contact_person" value="{{ $formValue('contact_person', $staff->contact_person) }}">@error('contact_person')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Business / Personal Address<input class="{{ $inputClass }} {{ $errorClass('business_address') }}" name="business_address" value="{{ $formValue('business_address', $staff->business_address) }}">@error('business_address')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 md:col-span-2">
                                <input type="hidden" name="gst_registered" value="0">
                                <input class="rounded border-slate-300 text-[#0082c9] focus:ring-[#0082c9]" type="checkbox" name="gst_registered" value="1" @checked($formValue('gst_registered', $staff->gst_registered))>
                                GST registered
                                @error('gst_registered')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror
                            </label>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
                        <div class="flex items-start gap-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 7h18v12H3zM3 11h18M7 15h4M16 15h1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-black tracking-tight text-slate-950">Skills, Experience, Availability & Payment</h2>
                                <p class="mt-1 text-sm text-slate-500">Tell us what work you can do, your previous experience, availability, and payment details.</p>
                            </div>
                        </div>
                        <div class="mt-6 grid gap-4">
                            @php $selectedSkills = $formValue('skills', $staff->skills ?? []); @endphp
                            <div>
                                <p class="text-sm font-bold text-slate-800">Cleaning Skills</p>
                                <p class="mt-1 text-sm text-slate-500">Select the work you can confidently perform.</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach (\App\Models\SubcontractorOnboarding::skillOptions() as $skill)
                                        <label class="cursor-pointer">
                                            <input class="peer sr-only" type="checkbox" name="skills[]" value="{{ $skill }}" @checked(in_array($skill, $selectedSkills, true))>
                                            <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 shadow-sm transition peer-checked:border-[#0082c9] peer-checked:bg-cyan-50 peer-checked:text-[#0082c9] hover:border-cyan-200 hover:bg-cyan-50">
                                                {{ $skill }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @if ($errors->has('skills') || $errors->has('skills.*'))
                                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $errors->first('skills') ?: $errors->first('skills.*') }}</p>
                                @endif
                            </div>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Availability<textarea class="{{ $inputClass }} {{ $errorClass('availability') }} min-h-32" name="availability" placeholder="Example: Monday 8am - 5pm&#10;Tuesday 8am - 5pm">{{ $formValue('availability', $staff->availability) }}</textarea>@error('availability')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Previous Experience<textarea class="{{ $inputClass }} {{ $errorClass('experience') }} min-h-32" name="experience" placeholder="Example: ABC Cleaning Services - 3 years commercial cleaning in Darwin.&#10;Reference: Jane Smith, Supervisor, 0400 000 000.&#10;Location: Darwin CBD offices and schools.">{{ $formValue('experience', $staff->experience) }}</textarea>@error('experience')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Bank Details<textarea class="{{ $inputClass }} {{ $errorClass('bank_details') }} min-h-32" name="bank_details">{{ $formValue('bank_details', $staff->bank_details) }}</textarea>@error('bank_details')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Superannuation<textarea class="{{ $inputClass }} {{ $errorClass('superannuation') }} min-h-28" name="superannuation">{{ $formValue('superannuation', $staff->superannuation) }}</textarea>@error('superannuation')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
                        <div class="flex items-start gap-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-amber-50 text-amber-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 2v6h6M9 13h6M9 17h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-black tracking-tight text-slate-950">Compliance Documents</h2>
                                <p class="mt-1 text-sm text-slate-500">Your current document status is shown below. Upload a file only when you need to add or replace a relevant document.</p>
                            </div>
                        </div>
                        <div class="mt-6 grid gap-3 md:grid-cols-2">
                            @foreach ($staff->documentFields() as $field => $label)
                                @php $documentUploaded = filled($staff->{$field}); @endphp
                                <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <span class="text-sm font-bold text-slate-700">{{ $label }}</span>
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-black {{ $documentUploaded ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-500 ring-1 ring-slate-200' }}">
                                        {{ $documentUploaded ? 'Uploaded' : 'Missing' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                            Replacement documents are not changed immediately. They become active only after admin approval.
                        </div>
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <label class="grid gap-2 text-sm font-bold text-slate-800">Insurance Expiry<input class="{{ $inputClass }} {{ $errorClass('insurance_expiry') }}" type="date" name="insurance_expiry" value="{{ $formValue('insurance_expiry', optional($staff->insurance_expiry)->format('Y-m-d')) }}">@error('insurance_expiry')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                            @foreach ($staff->documentFields() as $field => $label)
                                <label class="grid gap-2 text-sm font-bold text-slate-800">
                                    {{ $label }}
                                    <input class="{{ $fileClass }} {{ $errorClass($field) }}" type="file" name="{{ $field }}" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*">
                                    @error($field)<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror
                                </label>
                            @endforeach
                        </div>
                        <div id="staff-profile-selected-files" class="mt-5 hidden rounded-2xl border border-cyan-100 bg-cyan-50 p-4 text-sm text-cyan-950"></div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
                        <div class="flex items-start gap-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-slate-100 text-slate-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-xl font-black tracking-tight text-slate-950">Notes</h2>
                                <p class="mt-1 text-sm text-slate-500">Add anything else the admin team should know.</p>
                                <label class="mt-5 grid gap-2 text-sm font-bold text-slate-800">
                                    Notes
                                    <textarea class="{{ $inputClass }} {{ $errorClass('notes') }} min-h-28" name="notes" placeholder="Anything else the admin team should know">{{ $formValue('notes') }}</textarea>
                                    @error('notes')<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror
                                </label>
                            </div>
                        </div>
                    </section>

                    <div class="sticky bottom-4 z-10 rounded-3xl border border-slate-200 bg-white/95 p-4 shadow-2xl shadow-slate-900/10 backdrop-blur">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="max-w-2xl text-sm leading-6 text-slate-600">Your changes will be reviewed before your official profile is updated.</p>
                            <button class="inline-flex min-w-56 items-center justify-center rounded-2xl bg-[#0082c9] px-6 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#0b6176]">Submit Profile Update</button>
                        </div>
                    </div>
                </form>
            @endif
        </main>
    </div>

    <script>
        const form = document.getElementById('staff-profile-update-form');
        const errorBox = document.getElementById('staff-profile-file-error');
        const selectedFilesBox = document.getElementById('staff-profile-selected-files');
        const maxBytes = @json($uploadLimits['file_bytes']);
        const maxTotalBytes = @json($uploadLimits['post_bytes']);
        const maxBytesLabel = @json($uploadLimits['file_label']);
        const maxTotalBytesLabel = @json($uploadLimits['post_label']);
        const allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'];

        const formatBytes = (bytes) => {
            if (! bytes) {
                return '0 KB';
            }

            const units = ['Bytes', 'KB', 'MB'];
            const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);

            return `${(bytes / Math.pow(1024, index)).toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
        };

        const selectedFiles = () => Array.from(form?.querySelectorAll('input[type="file"]') || [])
            .flatMap((input) => Array.from(input.files || []).map((file) => ({ input, file })));

        const updateSelectedFiles = () => {
            const files = selectedFiles();

            if (! selectedFilesBox || files.length === 0) {
                selectedFilesBox?.classList.add('hidden');
                if (selectedFilesBox) {
                    selectedFilesBox.innerHTML = '';
                }
                return;
            }

            selectedFilesBox.replaceChildren();

            const title = document.createElement('p');
            title.className = 'font-black';
            title.textContent = 'Selected documents';

            const list = document.createElement('ul');
            list.className = 'mt-2 grid gap-1';

            files.forEach(({ file }) => {
                const item = document.createElement('li');
                const size = document.createElement('span');

                size.className = 'font-semibold text-cyan-700';
                size.textContent = ` (${formatBytes(file.size)})`;

                item.textContent = file.name;
                item.appendChild(size);
                list.appendChild(item);
            });

            selectedFilesBox.append(title, list);
            selectedFilesBox.classList.remove('hidden');
        };

        form?.querySelectorAll('input[type="file"]').forEach((input) => {
            input.addEventListener('change', updateSelectedFiles);
        });

        form?.addEventListener('submit', function (event) {
            const files = selectedFiles();
            const unsupported = files.find(({ file }) => ! allowedExtensions.includes((file.name.split('.').pop() || '').toLowerCase()));
            const oversized = files.find(({ file }) => file.size > maxBytes);
            const totalBytes = files.reduce((total, { file }) => total + file.size, 0);
            const tooMuchTotal = totalBytes > maxTotalBytes;

            if (! unsupported && ! oversized && ! tooMuchTotal) {
                return;
            }

            event.preventDefault();
            errorBox.textContent = unsupported
                ? `${unsupported.file.name} is not an accepted document type. Please upload PDF, Word, JPG, PNG, or WebP files only.`
                : oversized
                    ? `${oversized.file.name} is too large. Please keep each uploaded file under ${maxBytesLabel}.`
                    : `The selected documents are too large to send together. Please keep the total upload under ${maxTotalBytesLabel}, or upload fewer files at once.`;
            errorBox.classList.remove('hidden');
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    </script>
@endsection
