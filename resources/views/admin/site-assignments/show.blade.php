@extends('layouts.app')
@section('title', $site->name)
@section('content')
    @php
        $weekdayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $shiftsByDay = $site->shifts
            ->sortBy(fn ($shift) => array_search($shift->weekday, $weekdayOrder, true).'-'.$shift->label)
            ->groupBy('weekday');
        $notificationGroups = $recentDeliveries
            ->groupBy(fn ($delivery) => implode('-', [
                $delivery->site_shift_assignment_id,
                $delivery->event_type,
                $delivery->attempted_at?->format('YmdHi'),
            ]))
            ->values();
    @endphp

    <div class="grid gap-3">
        <section class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <div class="flex flex-wrap items-center gap-2">
                <a class="mr-1 inline-flex items-center gap-1 text-xs font-bold text-[#0082c9] hover:underline" href="{{ route('site-assignments.index') }}">← All Sites</a>
                <span class="rounded-md bg-[#0082c9]/10 px-2 py-1 text-xs font-black text-[#0082c9]">{{ $site->site_code }}</span>
                <h2 class="min-w-0 flex-1 truncate text-xl font-black">{{ $site->name }}</h2>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-700">{{ $site->shifts->count() }} shifts</span>
            </div>
            <p class="mt-1 text-sm text-slate-500">Open a shift to view or change its assigned subcontractors.</p>
        </section>

        <div class="grid items-start gap-3 xl:grid-cols-2">
            @forelse ($shiftsByDay as $weekday => $shifts)
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50 px-4 py-2">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-black">{{ ucfirst($weekday) }}</h2>
                        <span class="text-xs font-semibold text-slate-400">{{ $shifts->count() }} shift{{ $shifts->count() === 1 ? '' : 's' }}</span>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($shifts as $shift)
                        @php
                            $assignedIds = $shift->activeAssignments->pluck('staff_member_id')->map(fn ($id) => (int) $id)->all();
                            $assignedNames = $shift->activeAssignments
                                ->map(fn ($assignment) => $assignment->staffMember?->fullName())
                                ->filter()
                                ->values();
                        @endphp
                        <div x-data="{ open: false, search: '' }">
                            <button type="button" class="group flex w-full items-center gap-3 px-4 py-2.5 text-left transition hover:bg-cyan-50/60" @click="open = ! open" :aria-expanded="open">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-cyan-50 text-[#0082c9]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-base font-black">{{ $shift->label }}</span>
                                    <span class="block text-xs text-slate-500">{{ number_format((float) $shift->hours, 2) }} hours · {{ count($assignedIds) }} assigned</span>
                                    @if ($assignedNames->isNotEmpty())
                                        <span class="block truncate text-xs font-semibold text-[#0082c9]">{{ $assignedNames->implode(', ') }}</span>
                                    @endif
                                </span>
                                <span class="hidden text-xs font-bold text-[#0082c9] sm:inline" x-text="open ? 'Close' : 'Assign'"></span>
                                <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="open ? 'rotate-180 text-[#0082c9]' : ''" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>

                            <form method="POST" action="{{ route('site-assignments.update', $shift) }}" x-show="open" x-cloak class="border-t border-slate-100 bg-slate-50 px-4 py-3" data-submitting-text="Saving assignments...">
                                @csrf
                                @method('PUT')
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <input class="input max-w-md" type="search" x-model.debounce.150ms="search" placeholder="Search subcontractors">
                                    <button class="rounded-xl bg-[#0082c9] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#0082c9]">Save Assignments</button>
                                </div>
                                <div class="mt-2 grid max-h-64 gap-1 overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 sm:grid-cols-2">
                                    @forelse ($staffMembers as $member)
                                        @php $searchText = str($member->fullName().' '.$member->email.' '.$member->mobile)->lower(); @endphp
                                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-lg px-3 py-2 hover:bg-cyan-50" data-search="{{ $searchText }}" x-show="search.trim() === '' || $el.dataset.search.includes(search.toLowerCase().trim())">
                                            <span class="min-w-0"><span class="block truncate text-sm font-bold">{{ $member->fullName() }}</span><span class="block truncate text-xs text-slate-500">{{ $member->email ?: $member->mobile ?: 'No contact recorded' }}</span></span>
                                            <input class="rounded border-slate-300 text-[#0082c9] focus:ring-[#0082c9]" type="checkbox" name="staff_ids[]" value="{{ $member->id }}" @checked(in_array($member->id, $assignedIds, true))>
                                        </label>
                                    @empty
                                        <p class="p-4 text-sm text-slate-500">No active subcontractors available.</p>
                                    @endforelse
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
            @empty
                <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 xl:col-span-2">No active shifts are configured for this site.</section>
            @endforelse
        </div>

        <section x-data="{ open: false }" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <button type="button" class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition hover:bg-slate-50" @click="open = ! open" :aria-expanded="open">
                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-cyan-50 text-[#0082c9]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 17H9m10-1.8c-.9-1-1.5-1.5-1.5-4.2a5.5 5.5 0 0 0-11 0c0 2.7-.6 3.2-1.5 4.2-.4.4-.1 1.1.5 1.1h13c.6 0 .9-.7.5-1.1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M10 20a2.2 2.2 0 0 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-black">Recent Notifications</span>
                    <span class="mt-0.5 block text-xs text-slate-500">{{ $notificationGroups->count() }} recent assignment event{{ $notificationGroups->count() === 1 ? '' : 's' }}</span>
                </span>
                <span class="text-xs font-bold text-[#0082c9]" x-text="open ? 'Hide' : 'Show'"></span>
                <svg class="h-4 w-4 text-slate-400 transition-transform" :class="open ? 'rotate-180 text-[#0082c9]' : ''" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div x-show="open" x-cloak class="overflow-x-auto border-t border-slate-100 px-4 pb-3">
                <table class="min-w-full text-left text-xs">
                    <thead class="uppercase text-slate-400"><tr><th class="py-2 pr-4">Subcontractor</th><th class="py-2 pr-4">Shift</th><th class="py-2 pr-4">Event</th><th class="py-2 pr-4">Delivery</th><th class="py-2">Time</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($notificationGroups as $deliveries)
                            @php $delivery = $deliveries->first(); @endphp
                            <tr>
                                <td class="py-2 pr-4 font-bold">{{ $delivery->assignment?->staffMember?->fullName() }}</td>
                                <td class="py-2 pr-4">{{ ucfirst($delivery->assignment?->shift?->weekday) }} · {{ $delivery->assignment?->shift?->label }}</td>
                                <td class="py-2 pr-4">{{ ucfirst($delivery->event_type) }}</td>
                                <td class="py-2 pr-4">
                                    <span class="flex flex-wrap gap-1">
                                        @foreach ($deliveries as $channelDelivery)
                                            <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $channelDelivery->status === 'sent' ? 'bg-green-50 text-green-700' : ($channelDelivery->status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ strtoupper($channelDelivery->channel) }} {{ ucfirst($channelDelivery->status) }}</span>
                                        @endforeach
                                    </span>
                                </td>
                                <td class="whitespace-nowrap py-2 text-slate-500">{{ $delivery->attempted_at?->format('d M, H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-5 text-slate-500">No assignment notifications for this site yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
