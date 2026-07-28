@extends('layouts.app')
@section('title', 'Subcontractor Review')
@section('actions')
    <a class="btn-secondary" href="{{ route('subcontractor-onboardings.edit', $onboarding) }}">Edit Details</a>
    <a class="btn-secondary" href="{{ route('subcontractor-onboardings.index') }}">Back</a>
@endsection
@section('content')
    <div class="grid gap-6">
        <x-card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold">{{ $onboarding->legal_business_name ?: $onboarding->full_name }}</h2>
                    <p class="text-sm text-slate-500">{{ $onboarding->email }} · {{ $onboarding->mobile ?: $onboarding->phone }}</p>
                </div>
                <span class="badge {{ $onboarding->statusBadgeClass() }}">{{ $onboarding->status }}</span>
            </div>
            <div class="mt-5 grid gap-2 sm:grid-cols-4">
                @foreach (['Submitted', 'Pending Review', 'Approved', 'Active'] as $step)
                    <div class="rounded-lg border px-3 py-2 text-sm {{ in_array($step, ['Submitted', 'Pending Review'], true) || $onboarding->status === $step || ($step === 'Active' && $onboarding->status === 'Active') ? 'border-cyan-200 bg-cyan-50 text-cyan-800' : 'border-slate-200 text-slate-500' }}">{{ $step }}</div>
                @endforeach
            </div>
        </x-card>

        @if ($errors->any())
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-2">
            <x-card><h2 class="mb-4 text-lg font-bold">Company Information</h2>
                <dl class="grid gap-4 text-sm md:grid-cols-2">
                    @foreach ([
                        'Legal Business Name (if applicable)' => $onboarding->legal_business_name,
                        'Trading Name (if applicable)' => $onboarding->trading_name,
                        'ABN' => $onboarding->abn,
                        'GST Registered' => $onboarding->gst_registered ? 'Yes' : 'No',
                        'Business Structure' => $onboarding->business_structure,
                        'Contact Person (if applicable)' => $onboarding->contact_person,
                        'Email' => $onboarding->email,
                        'Mobile' => $onboarding->mobile ?: $onboarding->phone,
                        'Business Address / Personal Address' => $onboarding->business_address ?: $onboarding->address,
                    ] as $label => $value)
                        <div><dt class="font-semibold text-slate-500">{{ $label }}</dt><dd>{{ $value ?: 'Not listed' }}</dd></div>
                    @endforeach
                </dl>
            </x-card>

            <x-card><h2 class="mb-4 text-lg font-bold">Insurance & Compliance</h2>
                <dl class="grid gap-4 text-sm md:grid-cols-2">
                    <div><dt class="font-semibold text-slate-500">Insurance Expiry</dt><dd>{{ $onboarding->insurance_expiry?->format('d M Y') ?: 'Not listed' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Public Liability Insurance</dt><dd>{{ $onboarding->public_liability_insurance ? 'Uploaded' : 'Missing' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Working with Children Check (Ochre Card)</dt><dd>{{ $onboarding->workers_compensation_insurance ? 'Uploaded' : 'Missing' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Police Clearance</dt><dd>{{ $onboarding->police_clearance ? 'Uploaded' : 'Missing' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Driver Licence</dt><dd>{{ $onboarding->driver_licence ? 'Uploaded' : 'Missing' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Working Rights / VISA or relevant document</dt><dd>{{ $onboarding->working_rights ? 'Uploaded' : 'Missing' }}</dd></div>
                </dl>
            </x-card>

            <x-card><h2 class="mb-4 text-lg font-bold">Availability Details</h2>
                <dl class="grid gap-4 text-sm">
                    <div><dt class="font-semibold text-slate-500">Cleaning Skills</dt><dd>{{ $onboarding->skillsText() ?: 'Not listed' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Availability</dt><dd class="whitespace-pre-line">{{ $onboarding->available_hours ?: 'Not listed' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Previous Experience</dt><dd class="whitespace-pre-line">{{ $onboarding->experience ?: 'Not listed' }}</dd></div>
                </dl>
            </x-card>

            <x-card><h2 class="mb-4 text-lg font-bold">Payment Details</h2>
                <dl class="grid gap-4 text-sm">
                    <div><dt class="font-semibold text-slate-500">Bank Details</dt><dd class="whitespace-pre-line">{{ $onboarding->bank_details ?: 'Not listed' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Superannuation</dt><dd class="whitespace-pre-line">{{ $onboarding->superannuation ?: 'Not listed' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Notes (if applicable)</dt><dd class="whitespace-pre-line">{{ $onboarding->notes ?: 'Not listed' }}</dd></div>
                </dl>
            </x-card>
        </div>

        <x-card>
            <h2 class="mb-4 text-lg font-bold">Documents</h2>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($onboarding->documentFields() as $field => $label)
                    @php $hasDocument = $field === 'uploaded_certificates' ? filled($onboarding->uploaded_certificates) : filled($onboarding->{$field}); @endphp
                    <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                        <p class="font-semibold">{{ $label }}</p>
                        <p class="mt-1 text-sm {{ $hasDocument ? 'text-emerald-700' : 'text-rose-700' }}">{{ $hasDocument ? 'Uploaded' : 'Missing' }}</p>
                        @if ($hasDocument)
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a class="btn-secondary" href="{{ route('subcontractor-onboardings.documents.show', [$onboarding, $field]) }}" target="_blank">Preview</a>
                                <a class="btn-secondary" href="{{ route('subcontractor-onboardings.documents.download', [$onboarding, $field]) }}">Download</a>
                                <form method="POST" action="{{ route('subcontractor-onboardings.documents.destroy', [$onboarding, $field]) }}" onsubmit="return confirm('Delete this document? This will remove the uploaded file from this onboarding review.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700 hover:bg-rose-100">Delete</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card>
            @php $staffMember = $onboarding->staffMember; @endphp
            <h2 class="mb-4 text-lg font-bold">Review Actions</h2>
            <div class="flex flex-wrap items-start gap-3">
                <form method="POST" action="{{ route('subcontractor-onboardings.reject', $onboarding) }}" class="grid gap-2">
                    @csrf
                    <textarea class="input min-h-20" name="rejection_reason" placeholder="Reason for rejection" required></textarea>
                    <button class="btn-secondary text-rose-700" onclick="return confirm('Reject this subcontractor onboarding?')">Reject</button>
                </form>

                @if ($onboarding->status !== 'Active')
                    <form method="POST" action="{{ route('subcontractor-onboardings.approve', $onboarding) }}" class="grid gap-2">
                        @csrf
                        <button class="btn-primary" onclick="return confirm('Approve this onboarding and create a subcontractor profile in this system?')">Approve</button>
                        <p class="max-w-sm text-xs leading-5 text-slate-500">This creates an active Hydrox subcontractor profile and enables portal administration.</p>
                    </form>
                @endif
            </div>

            @if ($onboarding->status === 'Active' && $staffMember)
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100">
                    <p class="font-semibold">Approved and subcontractor profile created in this system.</p>
                    <p class="mt-1 leading-6">The subcontractor can now be managed directly in the Hydrox portal.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a class="btn-secondary" href="{{ route('staff-members.edit', $staffMember) }}">View Subcontractor Profile</a>
                    </div>
                </div>
            @endif

            @if ($onboarding->sync_logs)
                <div class="mt-4 grid gap-2 text-sm">
                    @php $log = collect($onboarding->sync_logs)->last(); @endphp
                    <p class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800"><strong>{{ $log['status'] }}</strong> · {{ $log['message'] }} · {{ $log['logged_at'] }}</p>
                </div>
            @endif
        </x-card>
    </div>
@endsection
