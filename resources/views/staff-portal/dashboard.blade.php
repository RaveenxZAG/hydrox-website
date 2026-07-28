@extends('layouts.auth')
@section('content')
    <div class="mx-auto grid max-w-5xl gap-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-[#0082c9]">Subcontractor Portal</p>
                    <h1 class="mt-1 text-2xl font-black">Welcome, {{ $staff->fullName() }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Work log period: <strong>{{ $window['label'] }}</strong></p>
                </div>
                <form method="POST" action="{{ route('staff-portal.logout') }}">
                    @csrf
                    <button class="btn-secondary">Logout</button>
                </form>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            <h2 class="text-xl font-bold">Monthly Work Log Upload</h2>
            <div class="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-3">
                <p><strong>Opens:</strong> {{ $window['opens_at']->format('d M Y H:i') }}</p>
                <p><strong>Deadline:</strong> {{ $window['closes_at']->format('d M Y H:i') }}</p>
                <p><strong>Status:</strong> {{ $currentInvoice ? 'Submitted' : ($window['is_open'] ? 'Open' : 'Closed') }}</p>
            </div>

            @if ($currentInvoice)
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                    Your work log for {{ $window['label'] }} was submitted on {{ $currentInvoice->submitted_at?->format('d M Y H:i') }}.
                </div>
            @elseif ($window['is_open'])
                <form method="POST" action="{{ route('staff-portal.invoices.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-4">
                    @csrf
                    <x-field label="Work Log File" name="invoice_file">
                        <input class="input file:mr-3 file:rounded-md file:border-0 file:bg-cyan-50 file:px-3 file:py-2 file:text-cyan-700" type="file" name="invoice_file" accept="application/pdf,.pdf" required>
                    </x-field>
                    <x-field label="Claimed Total Amount" name="total_amount">
                        <input class="input" type="number" step="0.01" min="0" name="total_amount" required>
                    </x-field>
                    <x-field label="Work Log Reference Number (optional)" name="invoice_reference">
                        <input class="input" name="invoice_reference">
                    </x-field>
                    <label class="flex gap-3 text-sm font-medium">
                        <input class="mt-1 rounded border-slate-300 text-cyan-600" type="checkbox" name="confirm" value="1" required>
                        I confirm the work log information is correct.
                    </label>
                    <button class="btn-primary">Submit Work Log</button>
                </form>
            @else
                <p class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Work log uploading is currently closed. Please contact administration if you need help.</p>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            <h2 class="text-xl font-bold">Previous Work Log History</h2>
            <div class="mt-4 divide-y divide-slate-200">
                @forelse ($invoices as $invoice)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3 text-sm">
                        <div>
                            <p class="font-bold">{{ $invoice->invoice_period->format('F Y') }} · ${{ $invoice->total_amount }}</p>
                            <p class="text-slate-500">{{ $invoice->storedFilename() }} · {{ $invoice->submitted_at?->format('d M Y') }}</p>
                        </div>
                        <a class="btn-secondary" href="{{ route('staff-portal.invoices.download', $invoice) }}">Download Work Log</a>
                    </div>
                @empty
                    <p class="py-5 text-sm text-slate-500">No work logs submitted yet.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            <h2 class="text-xl font-bold">Update Profile Details</h2>
            @if ($pendingProfileChange)
                <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">You already have a profile update waiting for admin review.</p>
            @else
                <form method="POST" action="{{ route('staff-portal.profile-update') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    <x-field label="Email" name="email"><input class="input" type="email" name="email" value="{{ $staff->email }}"></x-field>
                    <x-field label="Mobile" name="mobile"><input class="input" name="mobile" value="{{ $staff->mobile }}"></x-field>
                    <x-field label="Address" name="address"><input class="input" name="address" value="{{ $staff->address }}"></x-field>
                    <x-field label="Emergency Contact" name="emergency_contact"><input class="input" name="emergency_contact" value="{{ $staff->emergency_contact }}"></x-field>
                    <x-field label="Availability" name="availability"><textarea class="input min-h-28" name="availability">{{ $staff->availability }}</textarea></x-field>
                    <x-field class="md:col-span-2" label="Bank Details" name="bank_details"><textarea class="input min-h-28" name="bank_details">{{ $staff->bank_details }}</textarea></x-field>
                    <x-field class="md:col-span-2" label="Notes" name="notes"><textarea class="input min-h-28" name="notes"></textarea></x-field>
                    <button class="btn-primary md:col-span-2">Submit Profile Update</button>
                </form>
            @endif
        </section>
    </div>
@endsection
