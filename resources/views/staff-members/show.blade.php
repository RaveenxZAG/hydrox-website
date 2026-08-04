@extends('layouts.app')
@section('title', 'Subcontractor Profile')
@section('actions')
    <div class="flex flex-wrap gap-2">
        <a class="btn-secondary" href="{{ route('staff-members.index') }}">Back</a>
        <a class="btn-primary" href="{{ route('staff-members.edit', $staffMember) }}">Edit Subcontractor</a>
    </div>
@endsection
@section('content')
    @php
        $onboarding = $staffMember->onboarding;
        $display = fn ($field) => filled($staffMember->{$field}) ? $staffMember->{$field} : 'Missing';
        $missing = $staffMember->missingInfo();
    @endphp

    <div class="grid gap-6">
        <x-card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold">{{ $staffMember->fullName() }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $staffMember->email ?: 'Missing email' }} · {{ $staffMember->mobile ?: 'Missing mobile' }}</p>
                    @if ($onboarding)
                        <a class="mt-3 inline-flex text-sm font-semibold text-[#006da9]" href="{{ route('subcontractor-onboardings.show', $onboarding) }}">View original subcontractor onboarding</a>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="badge {{ $staffMember->staff_status === 'active' ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">{{ ucfirst($staffMember->staff_status ?: 'active') }}</span>
                    <span class="badge {{ $staffMember->invoicing_enabled ? 'bg-[#eaf6fc] text-[#07527d]' : 'bg-slate-100 text-slate-700' }}">Invoicing {{ $staffMember->invoicing_enabled ? 'Enabled' : 'Disabled' }}</span>
                    @if ($staffMember->missingInfoCount() === 0)
                        <span class="badge bg-emerald-50 text-emerald-800">Profile complete</span>
                    @else
                        <span class="badge bg-rose-50 text-rose-800">{{ $staffMember->missingInfoCount() }} missing</span>
                    @endif
                </div>
            </div>

            @if ($missing)
                <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                    <p class="font-semibold">Missing information</p>
                    <p class="mt-1">{{ implode(', ', $missing) }}</p>
                </div>
            @endif
        </x-card>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-card>
                <h2 class="mb-4 text-lg font-bold">Personal & Contact Details</h2>
                <dl class="grid gap-4 text-sm md:grid-cols-2">
                    <div><dt class="font-semibold text-slate-500">First Name</dt><dd>{{ $staffMember->first_name ?: 'Missing' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Last Name</dt><dd>{{ $staffMember->last_name ?: 'Missing' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Email</dt><dd>{{ $staffMember->email ?: 'Missing' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Mobile</dt><dd>{{ $staffMember->mobile ?: 'Missing' }}</dd></div>
                    <div class="md:col-span-2"><dt class="font-semibold text-slate-500">Address</dt><dd>{{ $staffMember->address ?: 'Missing' }}</dd></div>
                    <div class="md:col-span-2"><dt class="font-semibold text-slate-500">Emergency Contact</dt><dd>{{ $staffMember->emergency_contact ?: 'Missing' }}</dd></div>
                </dl>
            </x-card>

            <x-card>
                <h2 class="mb-4 text-lg font-bold">Business Details</h2>
                <dl class="grid gap-4 text-sm md:grid-cols-2">
                    <div><dt class="font-semibold text-slate-500">Legal Business Name</dt><dd>{{ $display('legal_business_name') }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Trading Name</dt><dd>{{ $display('trading_name') }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">ABN</dt><dd>{{ $display('abn') }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">GST Registered</dt><dd>{{ $staffMember->gst_registered ? 'Yes' : 'No' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Business Structure</dt><dd>{{ $display('business_structure') }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Contact Person</dt><dd>{{ $display('contact_person') }}</dd></div>
                    <div class="md:col-span-2"><dt class="font-semibold text-slate-500">Business / Personal Address</dt><dd>{{ $display('business_address') }}</dd></div>
                </dl>
            </x-card>

            <x-card>
                <h2 class="mb-4 text-lg font-bold">Availability Details</h2>
                <dl class="grid gap-4 text-sm">
                    <div><dt class="font-semibold text-slate-500">Availability</dt><dd class="whitespace-pre-line">{{ $display('availability') }}</dd></div>
                </dl>
            </x-card>

            <x-card>
                <h2 class="mb-4 text-lg font-bold">Skills & Experience</h2>
                <dl class="grid gap-4 text-sm">
                    <div>
                        <dt class="font-semibold text-slate-500">Cleaning Skills</dt>
                        <dd class="mt-2 flex flex-wrap gap-2">
                            @php $skills = \Illuminate\Support\Arr::wrap($staffMember->skills); @endphp
                            @forelse ($skills as $skill)
                                <span class="rounded-full border border-[#b8dff3] bg-[#eaf6fc] px-3 py-1 text-xs font-bold text-[#07527d]">{{ $skill }}</span>
                            @empty
                                <span>Missing</span>
                            @endforelse
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Previous Experience</dt>
                        <dd class="mt-1 whitespace-pre-line">{{ $staffMember->experience ?: 'Missing' }}</dd>
                    </div>
                </dl>
            </x-card>

            <x-card>
                <h2 class="mb-4 text-lg font-bold">Payment Details</h2>
                <dl class="grid gap-4 text-sm">
                    <div><dt class="font-semibold text-slate-500">Bank Details</dt><dd class="whitespace-pre-line">{{ $display('bank_details') }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Superannuation</dt><dd class="whitespace-pre-line">{{ $display('superannuation') }}</dd></div>
                </dl>
            </x-card>

            <x-card>
                <h2 class="mb-4 text-lg font-bold">Insurance & Compliance</h2>
                <dl class="grid gap-4 text-sm md:grid-cols-2">
                    <div><dt class="font-semibold text-slate-500">Insurance Expiry</dt><dd>{{ $staffMember->insurance_expiry?->format('d M Y') ?: 'Missing' }}</dd></div>
                    @foreach ($staffMember->documentFields() as $field => $label)
                        <div><dt class="font-semibold text-slate-500">{{ $label }}</dt><dd>{{ $staffMember->{$field} ? 'Uploaded' : 'Missing' }}</dd></div>
                    @endforeach
                    @if ($staffMember->onboarding)
                        <div><dt class="font-semibold text-slate-500">Citizenship / Residency</dt><dd>{{ $staffMember->onboarding->residency_status ?: 'Not recorded' }}</dd></div>
                        <div><dt class="font-semibold text-slate-500">Visa Type</dt><dd>{{ $staffMember->onboarding->visa_type ?: 'Not applicable' }}</dd></div>
                        <div><dt class="font-semibold text-slate-500">Visa Expiry</dt><dd>{{ $staffMember->onboarding->visa_expiry_date?->format('d M Y') ?: 'Not applicable' }}</dd></div>
                    @endif
                </dl>
            </x-card>

            <x-card class="xl:col-span-2">
                <h2 class="mb-4 text-lg font-bold">Portal Access</h2>
                <dl class="grid gap-4 text-sm md:grid-cols-2 xl:grid-cols-3">
                    <div><dt class="font-semibold text-slate-500">Portal Access</dt><dd>{{ $staffMember->portal_access_enabled ? 'Enabled' : 'Disabled' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Invoicing</dt><dd>{{ $staffMember->invoicing_enabled ? 'Enabled' : 'Disabled' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Security Role</dt><dd>{{ $staffMember->securityRoleName($roleNames) }}</dd></div>
                </dl>
            </x-card>
        </div>

        <x-card>
            <h2 class="mb-4 text-lg font-bold">Documents</h2>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($staffMember->documentFields() as $field => $label)
                    @php
                        $document = $staffMember->currentDocumentsByCategory()->get($field);
                        $current = $document?->currentVersion;
                        $staffHasDocument = (bool) $current || filled($staffMember->{$field});
                    @endphp
                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="font-semibold">{{ $label }}</p>
                        <p class="mt-1 text-sm {{ $staffHasDocument ? 'text-emerald-700' : 'text-rose-700' }}">{{ $staffHasDocument ? 'Uploaded' : 'Missing' }}</p>
                        @if ($staffHasDocument)
                            @if ($current)<p class="mt-1 text-xs text-slate-500">Version {{ $current->version_number }} · {{ $current->review_status }}</p>@endif
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a class="btn-secondary" href="{{ route('staff-members.documents.show', [$staffMember, $field]) }}" target="_blank">Preview</a>
                                <a class="btn-secondary" href="{{ route('staff-members.documents.download', [$staffMember, $field]) }}">Download</a>
                                <form method="POST" action="{{ $current ? route('subcontractor-document-versions.archive', $current) : route('staff-members.documents.destroy', [$staffMember, $field]) }}" onsubmit="return confirm('Archive {{ $label }}? It will remain available in history.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-700 shadow-sm transition hover:bg-red-100">Archive</button>
                                </form>
                            </div>
                            <p class="mt-2 break-all text-xs text-slate-500">{{ $current?->original_filename ?: $staffMember->documentName($field) }}</p>
                        @endif
                        @if ($document && $document->versions->isNotEmpty())
                            <details class="mt-3 text-sm"><summary class="cursor-pointer font-semibold text-[#006da9]">History ({{ $document->versions->count() }})</summary>
                                <div class="mt-2 grid gap-2">@foreach ($document->versions as $version)
                                    <div class="rounded border border-slate-200 p-2 text-xs"><p>v{{ $version->version_number }} · {{ $version->review_status }}{{ $version->archived_at ? ' · Archived' : '' }}</p><a class="font-semibold text-[#006da9]" href="{{ route('subcontractor-document-versions.download', $version) }}">{{ $version->original_filename }}</a></div>
                                @endforeach</div>
                            </details>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-lg font-bold">Notes</h2>
            <p class="whitespace-pre-line text-sm text-slate-600">{{ $staffMember->notes ?: 'No notes recorded.' }}</p>
        </x-card>

        <x-card class="border-red-200 bg-red-50/60">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-base font-bold text-red-900">Delete Subcontractor</h2>
                    <p class="mt-1 text-sm text-red-700">
                        Permanently deletes this subcontractor profile, portal access, and saved files. This cannot be recovered.
                    </p>
                </div>
                <form method="POST" action="{{ route('staff-members.destroy', $staffMember) }}">
                    @csrf
                    @method('DELETE')
                    <button
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-red-700"
                        onclick="return confirm('Warning: deleting this subcontractor will permanently remove their local profile, uploaded subcontractor files, work log files, profile update files, OTP records, and portal access information from the Hydrox Portal. This cannot be recovered. Continue?') && confirm('Final confirmation: are you absolutely sure you want to delete this subcontractor?')"
                    >Delete Subcontractor</button>
                </form>
            </div>
        </x-card>
    </div>
@endsection
