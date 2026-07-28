@extends('layouts.auth')

@section('auth-full-width', true)

@section('content')
    @php
        $fullName = $staff->fullName();
        $initials = collect([$staff->first_name ?? null, $staff->last_name ?? null])
            ->filter()
            ->map(fn ($name) => str($name)->substr(0, 1)->upper()->toString())
            ->join('');
        $initials = $initials !== '' ? $initials : str($fullName)->substr(0, 2)->upper()->toString();

        $currentStatus = $currentInvoice
            ? $currentInvoice->statusLabel()
            : ($window['is_open'] ? 'Open' : 'Closed');

        if (! $invoicingEnabled) {
            $periodTone = 'border-amber-200 bg-amber-50 text-amber-800';
            $periodLabel = 'INVOICING DISABLED';
        } elseif ($currentInvoice?->status === 'correction_required') {
            $periodTone = 'border-red-200 bg-red-50 text-red-700';
            $periodLabel = 'CORRECTION REQUIRED';
        } elseif ($currentInvoice) {
            $periodTone = match ($currentInvoice->status) {
                'paid' => 'border-blue-200 bg-blue-50 text-blue-700',
                'ready_for_payment' => 'border-green-200 bg-green-50 text-green-700',
                default => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            };
            $periodLabel = str($currentInvoice->statusLabel())->upper()->toString();
        } elseif ($window['is_open']) {
            $periodTone = 'border-green-200 bg-green-50 text-green-700';
            $periodLabel = 'SUBMISSIONS OPEN';
        } else {
            $periodTone = 'border-amber-200 bg-amber-50 text-amber-800';
            $periodLabel = 'SUBMISSIONS CLOSED';
        }

        $statusPill = function ($status) {
            return match ($status) {
                'paid' => 'border-blue-200 bg-blue-50 text-blue-700',
                'ready_for_payment' => 'border-green-200 bg-green-50 text-green-700',
                'correction_required' => 'border-red-200 bg-red-50 text-red-700',
                'resubmitted', 'pending_review' => 'border-amber-200 bg-amber-50 text-amber-700',
                default => 'border-slate-200 bg-slate-50 text-slate-700',
            };
        };

        $isCorrection = $currentInvoice?->status === 'correction_required';
        $canUpload = $invoicingEnabled && $window['is_open'] && (! $currentInvoice || $isCorrection);
    @endphp

    <div class="min-h-screen bg-[#F8FAFC] text-slate-950">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-[1440px] flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-5">
                    <div class="flex items-center gap-3">
                        <img class="h-10 w-28 object-contain" src="{{ asset('images/hydrox-logo.svg') }}" alt="Hydrox Facility Management">
                        <div class="leading-tight">
                            <p class="text-base font-black tracking-tight text-slate-950">Hydrox Facility Management</p>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0082c9]">Work Log Portal</p>
                        </div>
                    </div>
                    <div class="hidden h-9 w-px bg-slate-200 sm:block"></div>
                    <div class="hidden items-center gap-2 text-sm font-bold text-slate-700 sm:flex">
                        <svg class="h-5 w-5 text-[#0082c9]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M15 3.4a4 4 0 0 1 0 7.2M21 21v-2a4 4 0 0 0-3-3.87M11 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Subcontractor Portal
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-full bg-[#0082c9] text-sm font-black text-white shadow-sm">{{ $initials }}</div>
                        <div class="hidden sm:block">
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

        <div class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8">
            <section class="flex flex-wrap items-end justify-between gap-5">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Monthly Work Log Upload</h1>
                    <p class="mt-2 text-base text-slate-600">Submit and track your monthly work logs.</p>
                </div>

                <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-[0_12px_34px_rgba(15,23,42,0.06)]">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-cyan-50 text-[#0082c9]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div class="pr-2">
                        <p class="text-sm font-semibold text-slate-500">Work log month</p>
                        <p class="text-lg font-black text-slate-950">{{ $window['label'] }}</p>
                    </div>
                    <span class="hidden h-10 w-px bg-slate-200 sm:block"></span>
                    <span class="rounded-full border px-4 py-2 text-xs font-black tracking-wide {{ $periodTone }}">{{ $periodLabel }}</span>
                </div>
            </section>

            <section class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:p-7">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#0082c9] text-white shadow-sm">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 2v6h6M9 13h6M9 17h6M9 9h1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xl font-black text-slate-950 sm:text-2xl">Submit Your Work Log</h2>
                            <p class="mt-1 text-sm text-slate-500">Use the monthly template and upload one completed .xlsx file.</p>
                        </div>
                    </div>

                    @if ($invoicingEnabled)
                        <a class="inline-flex items-center gap-2 rounded-xl border border-[#0082c9] bg-white px-4 py-2.5 text-sm font-black text-[#0082c9] shadow-sm transition hover:bg-cyan-50" href="{{ route('staff-portal.invoices.template') }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Download Work Log Template
                        </a>
                    @endif
                </div>

                <div class="mt-8 grid gap-4 lg:grid-cols-3">
                    <div class="flex items-start gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-cyan-50 text-[#0082c9]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <p class="font-black text-slate-950">Opens</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $window['opens_at']->format('d M Y - H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-cyan-50 text-[#0082c9]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <p class="font-black text-slate-950">Deadline</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $window['closes_at']->format('d M Y - H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-cyan-50 text-[#0082c9]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M8 11V7a4 4 0 1 1 8 0v4M6 11h12v10H6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <p class="font-black text-slate-950">Status</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $currentStatus }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid gap-6 xl:grid-cols-[1fr_420px]">
                    <div>
                        @if ($errors->has('invoice') || $errors->has('invoice_file') || $errors->has('confirm'))
                            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">
                                <p class="font-black">Work log could not be submitted</p>
                                <ul class="mt-2 grid gap-1">
                                    @foreach (['invoice', 'invoice_file', 'confirm'] as $field)
                                        @foreach ($errors->get($field) as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (! $invoicingEnabled)
                            <div class="grid min-h-[260px] place-items-center rounded-3xl border border-dashed border-amber-300 bg-amber-50/70 px-6 py-10 text-center">
                                <div class="max-w-lg">
                                    <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-white text-amber-700 shadow-sm">
                                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M8 11V7a4 4 0 1 1 8 0v4M6 11h12v10H6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <h3 class="mt-5 text-lg font-black text-slate-950">Work log upload is not enabled</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">This subcontractor profile is not enabled for monthly work log submissions. Please contact administration if this should be changed.</p>
                                </div>
                            </div>
                        @elseif ($isCorrection)
                            <div class="rounded-3xl border border-red-200 bg-red-50/70 p-6">
                                <div class="flex items-start gap-4">
                                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-red-600 shadow-sm">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 9v4M12 17h.01M10.3 3.9 2.5 17.4A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.6L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h3 class="text-lg font-black text-slate-950">Correction required</h3>
                                        <p class="mt-2 text-sm leading-6 text-slate-700">{{ $currentInvoice->correction_reason }}</p>
                                        @if ($currentInvoice->correction_instructions)
                                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ $currentInvoice->correction_instructions }}</p>
                                        @endif
                                        @if ($currentInvoice->correction_due_at)
                                            <p class="mt-3 text-sm font-black text-red-700">Please upload again by {{ $currentInvoice->correction_due_at->format('d M Y') }}.</p>
                                        @endif
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('staff-portal.invoices.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-4 rounded-2xl border border-red-100 bg-white p-5">
                                    @csrf
                                    <label class="grid gap-2 text-sm font-bold text-slate-800">
                                        Corrected Excel work log
                                        <input class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-cyan-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-[#0082c9] focus:border-[#0082c9] focus:outline-none focus:ring-4 focus:ring-cyan-100" type="file" name="invoice_file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                                    </label>
                                    <label class="flex gap-3 text-sm font-semibold text-slate-700">
                                        <input class="mt-1 rounded border-slate-300 text-[#0082c9] focus:ring-[#0082c9]" type="checkbox" name="confirm" value="1" required>
                                        I confirm this corrected work log is for {{ $window['label'] }} and uses the Hydrox Facility Management monthly template.
                                    </label>
                                    <button class="inline-flex items-center justify-center rounded-2xl bg-[#0082c9] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#0b6176]">Upload Corrected Work Log</button>
                                </form>
                            </div>
                        @elseif ($currentInvoice)
                            <div class="rounded-3xl border border-emerald-200 bg-emerald-50/70 p-6">
                                <div class="flex flex-wrap items-start justify-between gap-5">
                                    <div class="flex items-start gap-4">
                                        <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <h3 class="text-lg font-black text-slate-950">Work log submitted</h3>
                                            <p class="mt-2 text-sm leading-6 text-slate-700">Your work log for {{ $window['label'] }} was submitted on {{ $currentInvoice->submitted_at?->format('d M Y - H:i') }}.</p>
                                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                                <div class="rounded-2xl border border-emerald-100 bg-white p-4">
                                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Claimed total</p>
                                                    <p class="mt-1 text-2xl font-black text-slate-950">${{ number_format((float) $currentInvoice->total_amount, 2) }}</p>
                                                </div>
                                                <div class="rounded-2xl border border-emerald-100 bg-white p-4">
                                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Review status</p>
                                                    <p class="mt-2 inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusPill($currentInvoice->status) }}">{{ $currentInvoice->statusLabel() }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <a class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50" href="{{ route('staff-portal.invoices.download', $currentInvoice) }}">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Download Work Log
                                        </a>
                                        @if ($currentInvoice->remittance_path)
                                            <a class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-black text-blue-700 shadow-sm transition hover:bg-blue-100" href="{{ route('staff-portal.invoices.remittance', $currentInvoice) }}">Remittance</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif ($canUpload)
                            <form method="POST" action="{{ route('staff-portal.invoices.store') }}" enctype="multipart/form-data" class="grid min-h-[300px] gap-5 rounded-3xl border border-dashed border-slate-300 bg-slate-50/70 px-6 py-8">
                                @csrf
                                <div class="mx-auto max-w-xl text-center">
                                    <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-white text-[#0082c9] shadow-sm">
                                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M14 2v6h6M12 18v-6M9 15l3-3 3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <h3 class="mt-5 text-lg font-black text-slate-950">Upload your monthly Excel work log</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">Choose the completed .xlsx template for {{ $window['label'] }}. The system will import the rows for review.</p>
                                </div>
                                <label class="mx-auto grid w-full max-w-2xl gap-2 text-sm font-bold text-slate-800">
                                    Excel work log file
                                    <input class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-cyan-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-[#0082c9] focus:border-[#0082c9] focus:outline-none focus:ring-4 focus:ring-cyan-100" type="file" name="invoice_file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                                </label>
                                <label class="mx-auto flex max-w-2xl gap-3 text-sm font-semibold text-slate-700">
                                    <input class="mt-1 rounded border-slate-300 text-[#0082c9] focus:ring-[#0082c9]" type="checkbox" name="confirm" value="1" required>
                                    I confirm this work log is for {{ $window['label'] }} and uses the Hydrox Facility Management monthly template.
                                </label>
                                <button class="mx-auto inline-flex min-w-56 items-center justify-center rounded-2xl bg-[#0082c9] px-6 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[#0b6176]">Submit Work Log</button>
                            </form>
                        @else
                            <div class="grid min-h-[260px] place-items-center rounded-3xl border border-dashed border-slate-300 bg-slate-50/70 px-6 py-10 text-center">
                                <div class="max-w-lg">
                                    <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-white text-[#0082c9] shadow-sm">
                                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M8 11V7a4 4 0 1 1 8 0v4M6 11h12v10H6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <h3 class="mt-5 text-lg font-black text-slate-950">Work log uploads are currently closed</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">Need to make a late submission? Please contact administration for assistance.</p>
                                    <span class="mt-5 inline-flex items-center gap-2 rounded-xl border border-[#0082c9] bg-white px-4 py-2.5 text-sm font-black text-[#0082c9]">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 4h16v16H4zM22 6l-10 7L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Contact administration
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-black text-slate-950">Before you upload</h3>
                        <div class="mt-6 grid gap-5 text-sm font-semibold text-slate-700">
                            <div class="flex items-start gap-3">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full border border-[#0082c9] text-[#0082c9]">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                Open the guide first, then move to the Work Log sheet
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full border border-[#0082c9] text-[#0082c9]">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                Use the Site Code dropdown, then enter Hours and Hourly Rate manually
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full border border-[#0082c9] text-[#0082c9]">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                Check dates, hourly rates and totals, then upload the .xlsx file (maximum 10 MB)
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

            <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:p-7">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-cyan-50 text-[#0082c9]">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 2v6h6M9 13h6M9 17h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xl font-black text-slate-950">Work Log History</h2>
                            <p class="mt-1 text-sm text-slate-500">Your submitted work logs and payment documents.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div class="flex min-w-64 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-400 shadow-sm">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m21 21-4.3-4.3M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Search work logs
                        </div>
                        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-600 shadow-sm">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M3 5h18M6 12h12M10 19h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            All Statuses
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    @forelse ($invoices as $invoice)
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:bg-slate-50">
                            <div class="flex min-w-0 items-center gap-4">
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-cyan-50 text-[#0082c9]">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M14 2v6h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-black text-slate-950">{{ $invoice->invoice_period->format('F Y') }}</p>
                                        <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $statusPill($invoice->status) }}">{{ $invoice->statusLabel() }}</span>
                                    </div>
                                    <p class="mt-1 truncate text-sm text-slate-500">{{ $invoice->storedFilename() }} - submitted {{ $invoice->submitted_at?->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-4">
                                <div class="text-right">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Claimed total</p>
                                    <p class="text-lg font-black text-slate-950">${{ number_format((float) $invoice->total_amount, 2) }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <a class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50" href="{{ route('staff-portal.invoices.download', $invoice) }}">Download Work Log</a>
                                    @if ($invoice->remittance_path)
                                        <a class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-black text-blue-700 shadow-sm transition hover:bg-blue-100" href="{{ route('staff-portal.invoices.remittance', $invoice) }}">Remittance</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="grid min-h-[240px] place-items-center rounded-3xl bg-slate-50 px-6 py-10 text-center">
                            <div>
                                <span class="mx-auto grid h-24 w-24 place-items-center rounded-3xl border border-cyan-100 bg-white text-[#0082c9] shadow-sm">
                                    <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 20h16M5 20V8l4-4h6l4 4v12M9 4v5h6V4M8 14h8M8 17h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <h3 class="mt-5 text-lg font-black text-slate-950">No work logs submitted yet</h3>
                                <p class="mt-2 text-sm text-slate-500">Your submitted work logs will appear here with their review status.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
