@extends('layouts.app')
@section('title', 'Site Assign')
@section('content')
    <div class="grid gap-3">
        <section class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-black uppercase tracking-wide text-[#0082c9]">Work Logs</p>
                    <h2 class="mt-1 text-xl font-black">Site Assign</h2>
                    <p class="mt-0.5 text-sm text-slate-500">Choose a site to manage its shift assignments.</p>
                </div>
                <span class="rounded-full bg-[#eaf6fc] px-3 py-1 text-xs font-bold text-[#006da9]">{{ $sites->count() }} active sites</span>
            </div>
        </section>

        <section>
            <div class="grid items-start gap-3 xl:grid-cols-2">
                @forelse ($sites as $site)
                    @php
                        $assignedNames = $site->shifts
                            ->flatMap(fn ($shift) => $shift->activeAssignments)
                            ->map(fn ($assignment) => $assignment->staffMember?->fullName())
                            ->filter()
                            ->unique()
                            ->sort()
                            ->values();
                    @endphp
                    <a href="{{ route('site-assignments.show', $site) }}" class="group grid min-h-24 grid-cols-[4.5rem_minmax(0,1fr)_auto] items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 shadow-sm transition hover:border-[#b8dff3] hover:bg-[#eaf6fc]/60">
                        <span class="inline-flex h-8 w-full items-center justify-center rounded-lg bg-[#0082c9]/10 px-2 text-xs font-black tracking-wide text-[#0082c9] ring-1 ring-[#0082c9]/15">{{ $site->site_code }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-base font-black text-slate-950">{{ $site->name }}</span>
                            <span class="mt-0.5 flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-slate-500">
                                <span>{{ $site->shifts_count }} shift{{ $site->shifts_count === 1 ? '' : 's' }}</span>
                                <span>{{ $site->active_assignments_count }} assignment{{ $site->active_assignments_count === 1 ? '' : 's' }}</span>
                                <span>{{ $site->patternLabel() }}</span>
                            </span>
                            @if ($assignedNames->isNotEmpty())
                                <span class="mt-1.5 flex flex-wrap items-center gap-1">
                                    @foreach ($assignedNames->take(3) as $name)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-[#eaf6fc] px-2 py-0.5 text-xs font-bold text-[#07527d] ring-1 ring-[#d4edf9]">
                                            <span class="grid h-4 w-4 place-items-center rounded-full bg-[#0082c9] text-[8px] font-black text-white">{{ str($name)->substr(0, 1)->upper() }}</span>
                                            {{ $name }}
                                        </span>
                                    @endforeach
                                    @if ($assignedNames->count() > 3)
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">+{{ $assignedNames->count() - 3 }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="mt-1.5 block text-xs font-medium text-slate-400">No subcontractors assigned</span>
                            @endif
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="hidden text-xs font-bold text-slate-500 transition group-hover:text-[#0082c9] sm:inline">Manage</span>
                            <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-[#0082c9]" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </a>
                @empty
                    <p class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-sm text-slate-500 xl:col-span-2">No active sites are configured.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
