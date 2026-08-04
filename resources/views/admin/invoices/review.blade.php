@extends('layouts.app')
@section('title', 'Work Log Review')
@section('content')
    @php
        $openFlags = $invoice->workLogs->where('status', 'flagged')->count();
        $claimedTotal = (float) $invoice->total_amount;
        $paid = $invoice->status === 'paid';
        $correctionRequired = $invoice->status === 'correction_required';
        $readyForPayment = $invoice->status === 'ready_for_payment';
        $invoiceId = $invoice->invoice_reference ?: 'INV-'.str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT);
        $latestEmailDelivery = $invoice->remittanceDeliveries->firstWhere('channel', 'email');
        $latestSmsDelivery = $invoice->remittanceDeliveries->firstWhere('channel', 'sms');
        $statusTone = match ($invoice->status) {
            'paid' => 'bg-[#0082c9]/10 text-[#0082c9] ring-[#0082c9]/20',
            'ready_for_payment' => 'bg-green-50 text-green-700 ring-green-100',
            'correction_required' => 'bg-red-50 text-red-700 ring-red-100',
            default => 'bg-amber-50 text-amber-700 ring-amber-100',
        };
    @endphp

    <div class="invoice-shell -m-4 min-h-[calc(100vh-5rem)] bg-[#F8FAFC] p-4 font-sans text-slate-950 sm:-m-6 sm:p-6 lg:-m-8 lg:p-8">
        <div class="mx-auto grid max-w-[1480px] gap-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a class="inline-flex items-center gap-2 rounded-xl border border-[#E5E7EB] bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50" href="{{ route('staff-invoices.index', ['month' => $backMonth]) }}">
                    <span aria-hidden="true">‹</span>
                    Back to Work Logs
                </a>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusTone }}">{{ $invoice->statusLabel() }}</span>
            </div>

            <section class="rounded-2xl border border-[#E5E7EB] bg-white p-6 shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem] xl:items-center">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-[#0082c9]">Work Log Review</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ $invoice->staffMember?->fullName() }}</h1>
                        <div class="mt-5 grid gap-3 text-sm md:grid-cols-3">
                            <div class="rounded-2xl border border-[#E5E7EB] bg-[#F8FAFC] p-4">
                                <p class="font-bold text-slate-500">Work Log Month</p>
                                <p class="mt-1 font-black text-slate-950">{{ $invoice->invoice_period?->format('F Y') }}</p>
                            </div>
                            <div class="rounded-2xl border border-[#E5E7EB] bg-[#F8FAFC] p-4">
                                <p class="font-bold text-slate-500">Work Log ID</p>
                                <p class="mt-1 font-black text-slate-950">{{ $invoiceId }}</p>
                            </div>
                            <div class="rounded-2xl border border-[#E5E7EB] bg-[#F8FAFC] p-4">
                                <p class="font-bold text-slate-500">Submitted</p>
                                <p class="mt-1 font-black text-slate-950">{{ $invoice->submitted_at?->format('d M Y, h:i A') ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-[#E5E7EB] bg-[#F8FAFC] p-5">
                        <p class="text-sm font-bold text-slate-500">Claimed Total</p>
                        <p class="mt-1 text-4xl font-black tracking-tight text-[#0082c9]">${{ number_format($claimedTotal, 2) }}</p>
                        <div class="mt-5 grid gap-2">
                            <a class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50" href="{{ route('staff-invoices.download', $invoice) }}">View Uploaded Work Log</a>
                            <a class="rounded-xl bg-[#0082c9] px-4 py-2.5 text-center text-sm font-bold text-white shadow-sm transition hover:bg-[#0082c9]" href="{{ route('staff-invoices.download', $invoice) }}">Download Excel</a>
                        </div>
                    </div>
                </div>
            </section>

            @if ($correctionRequired)
                <section class="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-900 shadow-sm">
                    <p class="font-bold">Correction requested</p>
                    <p class="mt-1 leading-6">{{ $invoice->correction_reason }}</p>
                    @if ($invoice->correction_instructions)
                        <p class="mt-2 leading-6">{{ $invoice->correction_instructions }}</p>
                    @endif
                </section>
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_27rem]">
                <main class="grid gap-6">
                    <section class="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                        <div class="border-b border-[#E5E7EB] p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-[#0082c9]">Step 1</p>
                                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Regular Hours</h2>
                                    <p class="mt-1 text-sm text-slate-500">Site status uses all active subcontractor work logs for the month. This subcontractor's contribution is shown separately.</p>
                                    @if (($submissionProgress['missing'] ?? 0) > 0)
                                        <p class="mt-2 text-xs font-bold text-amber-700">Combined totals are provisional: {{ $submissionProgress['missing'] }} subcontractor work log{{ $submissionProgress['missing'] === 1 ? ' is' : 's are' }} still missing.</p>
                                    @endif
                                </div>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $regularIssueCount > 0 ? 'bg-amber-50 text-amber-700 ring-amber-100' : 'bg-green-50 text-green-700 ring-green-100' }}">
                                    {{ $regularIssueCount > 0 ? $regularIssueCount.' issue'.($regularIssueCount === 1 ? '' : 's') : 'Correct' }}
                                </span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[940px] text-left text-sm">
                                <thead class="bg-[#F8FAFC] text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-5 py-3">Site</th>
                                        <th class="px-5 py-3">Subcontractor Roster</th>
                                        <th class="px-5 py-3">This Subcontractor</th>
                                        <th class="px-5 py-3">All Subcontractors</th>
                                        <th class="px-5 py-3">Difference</th>
                                        <th class="px-5 py-3">Amount</th>
                                        <th class="px-5 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB] bg-white">
                                    @forelse ($regularRows as $row)
                                        @php
                                            $variance = (float) $row['variance'];
                                            $isCorrect = $row['status'] === 'matched';
                                            $isCalm = in_array($row['status'], ['matched', 'unclaimed'], true);
                                        @endphp
                                        <tr class="transition hover:bg-[#F8FAFC]">
                                            <td class="px-5 py-4">
                                                <p class="font-semibold text-slate-950">{{ $row['site'] }}</p>
                                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600 ring-1 ring-slate-200">{{ $row['pattern_label'] }}</span>
                                                    @if ($row['validation_warning'])
                                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700 ring-1 ring-amber-100">{{ $row['validation_warning'] }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['expected'], 2) }}</td>
                                            <td class="px-5 py-4 font-semibold text-slate-900">{{ number_format((float) $row['staff_claimed'], 2) }}</td>
                                            <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['all_staff_claimed'], 2) }}</td>
                                            <td class="px-5 py-4 font-black {{ $isCalm ? 'text-green-700' : 'text-amber-700' }}">{{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 2) }}</td>
                                            <td class="px-5 py-4 font-semibold text-slate-900">${{ number_format((float) $row['amount'], 2) }}</td>
                                            <td class="px-5 py-4">
                                                <div class="flex flex-col items-start gap-1.5">
                                                    @if ($isCorrect)
                                                        <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700 ring-1 ring-green-100">Correct</span>
                                                    @elseif ($row['status'] === 'unclaimed')
                                                        <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700 ring-1 ring-green-100">{{ number_format(abs($variance), 2) }} hrs unclaimed</span>
                                                    @elseif ($row['status'] === 'missing_invoices')
                                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">Missing subcontractor work logs</span>
                                                    @elseif ($row['status'] === 'manual_review')
                                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">Manual review</span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">Extra {{ number_format(abs($variance), 2) }} hrs</span>
                                                    @endif
                                                    @if ($row['flagged'] > 0)
                                                        <span class="text-[11px] font-semibold text-slate-500">{{ $row['flagged'] }} claim flag{{ $row['flagged'] === 1 ? '' : 's' }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">No regular work rows imported.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                        <div class="border-b border-[#E5E7EB] p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-[#0082c9]">Imported detail</p>
                                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Dates, Sites &amp; Shifts</h2>
                                    <p class="mt-1 text-sm text-slate-500">The portal checks each selected shift against the site roster for that weekday. Older templates without a Shift column remain supported.</p>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 ring-1 ring-slate-200">{{ $regularLogs->count() }} row{{ $regularLogs->count() === 1 ? '' : 's' }}</span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[860px] text-left text-sm">
                                <thead class="bg-[#F8FAFC] text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-5 py-3">Date</th>
                                        <th class="px-5 py-3">Site</th>
                                        <th class="px-5 py-3">Shift</th>
                                        <th class="px-5 py-3">Hours</th>
                                        <th class="px-5 py-3">Amount</th>
                                        <th class="px-5 py-3">Check</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB] bg-white">
                                    @forelse ($regularLogs as $log)
                                        <tr class="scroll-mt-24 transition hover:bg-[#F8FAFC]" id="work-log-{{ $log->id }}">
                                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $log->work_date?->format('d M Y') ?: 'Missing' }}</td>
                                            <td class="px-5 py-4 text-slate-700">{{ $log->site_name }}</td>
                                            <td class="px-5 py-4">
                                                @if ($log->shift_label)
                                                    <span class="inline-flex rounded-full bg-[#eaf6fc] px-2.5 py-1 text-xs font-bold text-[#0082c9] ring-1 ring-[#d4edf9]">{{ $log->shift_label }}</span>
                                                @else
                                                    <span class="text-xs font-semibold text-slate-400">Legacy template</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-slate-700">{{ number_format((float) $log->hours, 2) }}</td>
                                            <td class="px-5 py-4 font-semibold text-slate-900">${{ number_format((float) $log->amount, 2) }}</td>
                                            <td class="px-5 py-4">
                                                @if ($log->status === 'flagged')
                                                    <div class="max-w-xs">
                                                        <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-100">Needs review</span>
                                                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $log->flag_reason }}</p>
                                                    </div>
                                                @else
                                                    <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-bold text-green-700 ring-1 ring-green-100">Matched</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No regular site rows imported.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                        <div class="border-b border-[#E5E7EB] p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-[#0082c9]">Step 2</p>
                                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Other Work</h2>
                                    <p class="mt-1 text-sm text-slate-500">Non-regular work stays separate and needs admin approval.</p>
                                </div>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $otherLogs->where('status', 'flagged')->count() > 0 ? 'bg-amber-50 text-amber-700 ring-amber-100' : 'bg-green-50 text-green-700 ring-green-100' }}">
                                    {{ $otherLogs->where('status', 'flagged')->count() > 0 ? 'Needs Review' : 'Clear' }}
                                </span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[760px] text-left text-sm">
                                <thead class="bg-[#F8FAFC] text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-5 py-3">Site</th>
                                        <th class="px-5 py-3">Description</th>
                                        <th class="px-5 py-3">Hours</th>
                                        <th class="px-5 py-3">Amount</th>
                                        <th class="px-5 py-3">Approval</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB] bg-white">
                                    @forelse ($otherLogs as $log)
                                        <tr class="scroll-mt-24 transition hover:bg-[#F8FAFC]" id="work-log-{{ $log->id }}">
                                            <td class="px-5 py-4 font-semibold text-slate-950">{{ $log->site_name }}</td>
                                            <td class="px-5 py-4 text-slate-600">{{ $log->notes ?: $log->flag_reason ?: 'Other Work' }}</td>
                                            <td class="px-5 py-4 text-slate-600">{{ number_format((float) $log->hours, 2) }}</td>
                                            <td class="px-5 py-4 font-semibold text-slate-900">${{ number_format((float) $log->amount, 2) }}</td>
                                            <td class="px-5 py-4">
                                                @if ($log->status === 'flagged')
                                                    <form method="POST" action="{{ route('staff-invoices.work-logs.approve', $log) }}">
                                                        @csrf
                                                        <button class="rounded-xl bg-green-50 px-3 py-1.5 text-xs font-bold text-green-700 ring-1 ring-green-100 transition hover:bg-green-100">Approve</button>
                                                    </form>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700 ring-1 ring-green-100">Approved</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">No other work rows.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="grid gap-6 lg:grid-cols-2">
                        <div class="rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                            <h2 class="text-lg font-black tracking-tight text-slate-950">Work Log File</h2>
                            <div class="mt-4 flex flex-wrap items-center gap-4">
                                <div class="grid h-12 w-12 place-items-center rounded-xl bg-green-50 text-xs font-black text-green-700 ring-1 ring-green-100">XLSX</div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-950">{{ $invoice->storedFilename() }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Uploaded {{ $invoice->submitted_at?->format('d M Y, h:i A') ?: '-' }}</p>
                                    <p class="text-sm text-slate-500">Size: {{ $invoice->file_size ? number_format($invoice->file_size / 1024, 1).' KB' : '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                            <h2 class="text-lg font-black tracking-tight text-slate-950">Timeline</h2>
                            <div class="mt-5 space-y-5 border-l border-[#E5E7EB] pl-5 text-sm">
                                <div class="relative">
                                    <span class="absolute -left-[25px] top-1 h-3 w-3 rounded-full bg-[#0082c9]"></span>
                                    <p class="font-semibold text-slate-950">Submitted</p>
                                    <p class="mt-1 text-slate-500">{{ $invoice->submitted_at?->format('d M Y, h:i A') ?: '-' }}</p>
                                </div>
                                <div class="relative">
                                    <span class="absolute -left-[25px] top-1 h-3 w-3 rounded-full bg-slate-300"></span>
                                    <p class="font-semibold text-slate-950">{{ $invoice->statusLabel() }}</p>
                                    <p class="mt-1 text-slate-500">{{ $invoice->reviewed_at?->format('d M Y, h:i A') ?: 'Under review' }}</p>
                                </div>
                                @if ($invoice->paid_at)
                                    <div class="relative">
                                        <span class="absolute -left-[25px] top-1 h-3 w-3 rounded-full bg-green-500"></span>
                                        <p class="font-semibold text-slate-950">Paid</p>
                                        <p class="mt-1 text-slate-500">{{ $invoice->paid_at->format('d M Y, h:i A') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                </main>

                <aside class="grid content-start gap-6 xl:sticky xl:top-24">
                    <section class="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                        <div class="border-b border-[#E5E7EB] p-5">
                            <p class="text-sm font-bold text-[#0082c9]">Step 3</p>
                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Payment Summary</h2>
                            <p class="mt-1 text-sm text-slate-500">Remittance shows site and amount only.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-[#F8FAFC] text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-5 py-3">Site</th>
                                        <th class="px-5 py-3 text-right">Approved Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB] bg-white">
                                    @forelse ($paymentRows as $row)
                                        <tr>
                                            <td class="px-5 py-4 font-semibold text-slate-950">{{ $row['site'] ?: 'Unknown site' }}</td>
                                            <td class="px-5 py-4 text-right font-semibold text-slate-900">${{ number_format((float) $row['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="px-5 py-8 text-center text-slate-500">No payment rows.</td></tr>
                                    @endforelse
                                    <tr class="bg-[#F8FAFC]">
                                        <td class="px-5 py-4 font-black text-slate-950">Total Approved</td>
                                        <td class="px-5 py-4 text-right text-xl font-black text-[#0082c9]">${{ number_format((float) $approvedTotal, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-[0_10px_30px_rgba(15,23,42,0.04)]" id="correction-form">
                        <h2 class="text-xl font-black tracking-tight text-slate-950">Actions</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $openFlags > 0 ? $openFlags.' item'.($openFlags === 1 ? '' : 's').' still need review.' : 'No open flags.' }}</p>

                        @if (! in_array($invoice->status, ['correction_required', 'paid'], true))
                            <form method="POST" action="{{ route('staff-invoices.correction', $invoice) }}" class="mt-5 grid gap-3">
                                @csrf
                                <textarea class="rounded-xl border-[#E5E7EB] text-sm shadow-sm placeholder:text-slate-400 focus:border-[#0082c9] focus:ring-[#0082c9]" name="reason" rows="3" placeholder="Reason for correction" required></textarea>
                                <textarea class="rounded-xl border-[#E5E7EB] text-sm shadow-sm placeholder:text-slate-400 focus:border-[#0082c9] focus:ring-[#0082c9]" name="instructions" rows="2" placeholder="Instructions for contractor"></textarea>
                                <input class="rounded-xl border-[#E5E7EB] text-sm shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" type="date" name="due_at">
                                <button class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700 ring-1 ring-red-100 transition hover:bg-red-100">Request Correction</button>
                            </form>

                            @if (! $readyForPayment)
                                @if ($openFlags > 0)
                                    <div class="mt-3" x-data="{ confirmOverride: false }">
                                        <button
                                            class="w-full rounded-xl bg-[#0082c9] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#0082c9]"
                                            type="button"
                                            x-show="! confirmOverride"
                                            x-on:click="confirmOverride = true"
                                        >Mark Ready for Payment</button>

                                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4" x-cloak x-show="confirmOverride">
                                            <p class="font-black text-amber-950">{{ $openFlags }} unresolved item{{ $openFlags === 1 ? '' : 's' }}</p>
                                            <p class="mt-1 text-sm leading-5 text-amber-800">Requesting a correction is recommended. You can still bypass these items and mark the work log ready for payment.</p>
                                            <div class="mt-4 grid gap-2">
                                                <a class="rounded-xl bg-white px-4 py-2.5 text-center text-sm font-bold text-red-700 ring-1 ring-red-200 transition hover:bg-red-50" href="#correction-form" x-on:click="confirmOverride = false">Request Correction Instead</a>
                                                <form method="POST" action="{{ route('staff-invoices.ready', $invoice) }}">
                                                    @csrf
                                                    <input type="hidden" name="override_flags" value="1">
                                                    <button class="w-full rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-amber-700">Confirm Override and Mark Ready</button>
                                                </form>
                                                <button class="rounded-xl px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-white" type="button" x-on:click="confirmOverride = false">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('staff-invoices.ready', $invoice) }}" class="mt-3">
                                        @csrf
                                        <button class="w-full rounded-xl bg-[#0082c9] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#0082c9]">Mark Ready for Payment</button>
                                    </form>
                                @endif
                            @endif
                        @endif

                        @if ($readyForPayment)
                            <form method="POST" action="{{ route('staff-invoices.paid', $invoice) }}" class="mt-5 grid gap-3 rounded-2xl border border-green-200 bg-green-50 p-4">
                                @csrf
                                <p class="font-bold text-green-900">Mark Paid</p>
                                <input class="rounded-xl border-green-200 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" type="date" name="paid_at" value="{{ now('Australia/Melbourne')->toDateString() }}" required>
                                <input class="rounded-xl border-green-200 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" type="number" step="0.01" min="0" name="approved_total" value="{{ $invoice->approved_total ?? $invoice->total_amount }}" required>
                                <button class="rounded-xl bg-green-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-green-700">Mark Paid and Create Remittance</button>
                            </form>
                        @endif

                        @if ($paid && $invoice->remittance_path)
                            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-bold text-slate-950">Remittance Delivery</p>
                                        <p class="mt-1 text-xs text-slate-500">Latest email and SMS delivery results.</p>
                                    </div>
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-[#0082c9]">Paid</span>
                                </div>

                                <div class="mt-4 grid gap-2">
                                    @foreach ([['label' => 'Email', 'delivery' => $latestEmailDelivery, 'recipient' => $invoice->staffMember?->email], ['label' => 'SMS', 'delivery' => $latestSmsDelivery, 'recipient' => $invoice->staffMember?->mobile]] as $deliveryRow)
                                        @php
                                            $deliveryStatus = $deliveryRow['delivery']?->status ?: 'not_sent';
                                            $deliveryTone = match ($deliveryStatus) {
                                                'sent' => 'bg-green-50 text-green-700 ring-green-100',
                                                'failed' => 'bg-red-50 text-red-700 ring-red-100',
                                                'skipped' => 'bg-amber-50 text-amber-700 ring-amber-100',
                                                default => 'bg-slate-100 text-slate-600 ring-slate-200',
                                            };
                                        @endphp
                                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-sm font-bold text-slate-800">{{ $deliveryRow['label'] }}</p>
                                                    <p class="mt-0.5 truncate text-xs text-slate-500">{{ $deliveryRow['recipient'] ?: 'Contact detail missing' }}</p>
                                                </div>
                                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $deliveryTone }}">{{ str($deliveryStatus)->replace('_', ' ')->headline() }}</span>
                                            </div>
                                            @if ($deliveryRow['delivery']?->attempted_at)
                                                <p class="mt-2 text-[11px] text-slate-400">Attempted {{ $deliveryRow['delivery']->attempted_at->format('d M Y, h:i A') }}</p>
                                            @endif
                                            @if ($deliveryRow['delivery']?->error_message)
                                                <p class="mt-2 break-words text-xs leading-5 text-red-600">{{ $deliveryRow['delivery']->error_message }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 grid gap-2">
                                    <a class="block rounded-xl bg-[#0082c9] px-4 py-3 text-center text-sm font-bold text-white shadow-sm transition hover:bg-[#0082c9]" href="{{ route('staff-invoices.remittance', $invoice) }}">Download Remittance</a>
                                    <form method="POST" action="{{ route('staff-invoices.remittance.send', $invoice) }}" onsubmit="return confirm('Send the remittance email and SMS notification again?')">
                                        @csrf
                                        <button class="w-full rounded-xl border border-[#0082c9]/30 bg-white px-4 py-3 text-sm font-bold text-[#0082c9] transition hover:bg-[#0082c9]/5">Resend Email &amp; SMS</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection
