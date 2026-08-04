@extends('layouts.app')

@section('title', 'Send New Email')

@section('content')
    <div class="mx-auto grid max-w-6xl gap-5">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#0082c9]">Email</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Send new email</h1>
            <p class="mt-1 text-sm text-slate-500">Send the preconfigured Hydrox company profile email to one or more recipients.</p>
        </div>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(300px,0.72fr)]">
            <form method="POST" action="{{ route('emails.store') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                @csrf

                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-black text-slate-950">Recipients</h2>
                    <p class="mt-1 text-sm text-slate-500">Each address receives a separate email. Recipients will never see one another.</p>
                </div>

                <div class="mt-5">
                    <x-field label="Email addresses" name="recipients">
                        <textarea class="input min-h-44 resize-y" name="recipients" required placeholder="client@example.com&#10;contact@example.com">{{ old('recipients') }}</textarea>
                    </x-field>
                    <p class="mt-2 text-xs leading-5 text-slate-500">Separate addresses with a new line, comma, or semicolon. Maximum {{ $maxRecipients }} unique recipients.</p>
                </div>

                <div class="mt-5 flex justify-end">
                    <button class="btn-primary">Send company profile email</button>
                </div>
            </form>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#0082c9]/10 text-[#0082c9]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-[#0082c9]">Preconfigured template</p>
                        <h2 class="mt-1 text-lg font-black text-slate-950">Company profile introduction</h2>
                    </div>
                </div>

                <dl class="mt-5 grid gap-4 text-sm">
                    <div>
                        <dt class="font-bold text-slate-500">Subject</dt>
                        <dd class="mt-1 text-slate-900">{{ $subject }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-500">Company</dt>
                        <dd class="mt-1 text-slate-900">{{ $business['company_name'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-500">Profile button</dt>
                        <dd class="mt-1 break-all text-[#0082c9]">{{ $profileUrl }}</dd>
                    </div>
                </dl>

                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-6 text-emerald-800">
                    The subject, branded HTML, company details, and profile button are fixed to keep every message consistent.
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-black text-slate-950">Recent email history</h2>
                <p class="mt-1 text-sm text-slate-500">The latest company profile email delivery attempts.</p>
            </div>

            @if ($deliveries->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-slate-500">No company profile emails have been sent yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3 sm:px-6">Recipient</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Sent by</th>
                                <th class="px-5 py-3 sm:px-6">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($deliveries as $delivery)
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-slate-900 sm:px-6">{{ $delivery->recipient }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $delivery->status === 'sent' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ ucfirst($delivery->status) }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $delivery->user?->name ?? 'Unknown user' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-500 sm:px-6">{{ $delivery->created_at->timezone('Australia/Melbourne')->format('d M Y, g:i a') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($deliveries->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $deliveries->links() }}</div>
                @endif
            @endif
        </section>
    </div>
@endsection
