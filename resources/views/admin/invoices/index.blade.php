@extends('layouts.app')
@section('title', request('section') === 'sites' ? 'Work Log Sites' : 'Work Logs')
@section('content')
    @php
        $tabs = [
            'all' => 'All',
            'pending_review' => 'Pending Review',
            'ready_for_payment' => 'Ready for Payment',
            'paid' => 'Paid',
            'correction_required' => 'Correction Required',
        ];
        $previousMonth = $monthDate->copy()->subMonthNoOverflow()->format('Y-m');
        $nextMonth = $monthDate->copy()->addMonthNoOverflow()->format('Y-m');
        $weekdays = [
            'monday_hours' => 'Mon',
            'tuesday_hours' => 'Tue',
            'wednesday_hours' => 'Wed',
            'thursday_hours' => 'Thu',
            'friday_hours' => 'Fri',
            'saturday_hours' => 'Sat',
            'sunday_hours' => 'Sun',
        ];
        $weekdayNames = [
            'monday' => 'Mon',
            'tuesday' => 'Tue',
            'wednesday' => 'Wed',
            'thursday' => 'Thu',
            'friday' => 'Fri',
            'saturday' => 'Sat',
            'sunday' => 'Sun',
        ];
        $patterns = [
            'weekly' => 'Weekly',
            'fortnightly' => 'Fortnightly',
        ];
        $baseQuery = array_filter([
            'month' => $month,
            'search' => $search,
            'contractor' => $contractorId ?: null,
            'site' => $siteId ?: null,
        ]);
        $isSitesSection = false;
    @endphp

    <div class="invoice-shell -m-4 min-h-[calc(100vh-5rem)] bg-[#F8FAFC] p-4 font-sans text-slate-950 sm:-m-6 sm:p-6 lg:-m-8 lg:p-8">
        <div class="mx-auto grid max-w-[1480px] gap-6">
            @if (! empty($missingTables ?? []))
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-900 shadow-sm">
                    <p class="font-bold">Database update needed</p>
                    <p class="mt-1 leading-6">Subcontractor Work Logs cannot load yet because the live database is missing: <strong>{{ implode(', ', $missingTables) }}</strong>.</p>
                </div>
            @endif

            @if (! $isSitesSection)
                <section class="rounded-2xl border border-[#E5E7EB] bg-white p-6 shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                    <div class="flex flex-wrap items-start justify-between gap-5">
                        <div class="max-w-2xl">
                            <p class="text-sm font-bold uppercase tracking-wide text-[#0082c9]">Finance Queue</p>
                            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Monthly review queue</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Review monthly subcontractor work logs and prepare payments.</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a class="grid h-10 w-10 place-items-center rounded-xl border border-[#E5E7EB] bg-white text-slate-600 transition hover:bg-slate-50" href="{{ route('staff-invoices.index', ['month' => $previousMonth, 'status' => $status, 'search' => $search, 'contractor' => $contractorId ?: null, 'site' => $siteId ?: null]) }}" aria-label="Previous month">‹</a>
                            <form method="GET" action="{{ route('staff-invoices.index') }}" class="flex items-center gap-3 rounded-xl border border-[#E5E7EB] bg-[#F8FAFC] px-3 py-2">
                                <span class="text-sm font-bold text-slate-700">Month</span>
                                <input class="w-36 border-0 bg-transparent p-0 text-sm font-bold text-slate-950 focus:ring-0" type="month" name="month" value="{{ $month }}">
                                <input type="hidden" name="status" value="{{ $status }}">
                                @if ($search !== '')<input type="hidden" name="search" value="{{ $search }}">@endif
                                @if ($contractorId)<input type="hidden" name="contractor" value="{{ $contractorId }}">@endif
                                @if ($siteId)<input type="hidden" name="site" value="{{ $siteId }}">@endif
                                <button class="rounded-lg px-2 py-1 text-sm font-bold text-[#0082c9] hover:bg-[#0082c9]/10">Open</button>
                            </form>
                            <a class="grid h-10 w-10 place-items-center rounded-xl border border-[#E5E7EB] bg-white text-slate-600 transition hover:bg-slate-50" href="{{ route('staff-invoices.index', ['month' => $nextMonth, 'status' => $status, 'search' => $search, 'contractor' => $contractorId ?: null, 'site' => $siteId ?: null]) }}" aria-label="Next month">›</a>
                            <button type="button" class="rounded-xl bg-[#0082c9] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0082c9]">Export</button>
                        </div>
                    </div>
                </section>
            @endif

            @if ($isSitesSection)
                <section id="invoice-sites" class="rounded-2xl border border-[#E5E7EB] bg-white p-6 shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-black tracking-tight text-slate-950">Regular Site Roster</h2>
                            <p class="mt-1 text-sm text-slate-500">Add sites once, then set the weekly or fortnightly hours used for work log checking.</p>
                        </div>
                        <span class="rounded-full bg-[#0082c9]/10 px-3 py-1 text-xs font-bold text-[#0082c9] ring-1 ring-[#0082c9]/20">{{ $sites->count() }} sites</span>
                    </div>

                    <div class="mt-5 rounded-2xl border border-[#0082c9]/20 bg-[#0082c9]/5 p-4">
                        <div class="flex items-start gap-3">
                            <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-[#0082c9] text-sm font-black text-white">XL</div>
                            <div>
                                <h3 class="text-sm font-black text-slate-950">This roster powers the monthly Excel workbook</h3>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Update Site Codes here before staff download a new workbook. The workbook now uses a Site Code dropdown only; contractors enter Hours manually. Existing shift schedules remain available for internal roster planning.</p>
                                <p class="mt-1 text-xs font-bold text-[#0082c9]">Important: changing the hidden sheet in Excel does not create or update a site in this portal.</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('staff-invoices.sites.store') }}" class="mt-6 rounded-2xl border border-[#E5E7EB] bg-[#F8FAFC] p-4" x-data="{
                        pattern: 'weekly',
                        weekdayOrder: @js(array_keys($weekdayNames)),
                        weekdayLabels: @js($weekdayNames),
                        rosterDays: [{ weekday: 'monday', shifts: [{ label: 'Shift A', hours: '' }] }],
                        nextShiftLabel(shifts) { return 'Shift ' + String.fromCharCode(65 + shifts.length); },
                        addWeekday() {
                            const used = this.rosterDays.map(day => day.weekday);
                            const next = this.weekdayOrder.find(day => ! used.includes(day)) || 'monday';
                            this.rosterDays.push({ weekday: next, shifts: [{ label: 'Shift A', hours: '' }] });
                        },
                        addShift(day) { day.shifts.push({ label: this.nextShiftLabel(day.shifts), hours: '' }); }
                    }">
                        @csrf
                        <input type="hidden" name="validation_mode" value="auto">
                        <div class="grid gap-3 xl:grid-cols-[7rem_minmax(16rem,1fr)_9rem_11rem_10rem_auto]">
                            <input class="rounded-xl border-[#E5E7EB] text-sm font-bold uppercase shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" name="site_code" placeholder="{{ $nextSiteCode ?? 'CTC001' }}">
                            <input class="rounded-xl border-[#E5E7EB] text-sm shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" name="name" placeholder="Site name" required>
                            <select class="rounded-xl border-[#E5E7EB] text-sm shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" name="recurring_pattern" x-model="pattern">
                                @foreach ($patterns as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <input class="rounded-xl border-[#E5E7EB] text-sm shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" type="number" step="0.25" min="0" name="weekly_contract_hours" placeholder="Site weekly total" required>
                            <input class="rounded-xl border-[#E5E7EB] text-sm shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" type="date" name="fortnightly_anchor_date" title="Fortnightly anchor date" x-show="pattern === 'fortnightly'" x-cloak>
                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input class="rounded border-slate-300 text-[#0082c9] focus:ring-[#0082c9]" type="checkbox" name="active" value="1" checked> Active</label>
                        </div>
                        <div class="mt-4 rounded-2xl border border-[#E5E7EB] bg-white p-3">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-wide text-slate-500">Subcontractor payable roster</p>
                                    <p class="mt-1 text-xs text-slate-500">Add the reduced subcontractor hours used for work log checking. Site total is entered once above.</p>
                                </div>
                                <button type="button" class="rounded-xl border border-[#E5E7EB] bg-white px-3 py-2 text-xs font-bold text-[#0082c9] shadow-sm hover:bg-[#0082c9]/10" @click="addWeekday()">Add Weekday</button>
                            </div>
                            <div class="mt-3 grid gap-2">
                                <template x-for="(day, dayIndex) in rosterDays" :key="dayIndex">
                                    <div class="grid items-start gap-3 rounded-xl border border-[#E5E7EB] bg-[#F8FAFC] p-3 xl:grid-cols-[9rem_minmax(0,1fr)]">
                                        <div class="grid content-start gap-1.5">
                                            <label class="text-[10px] font-black uppercase tracking-wide text-slate-400">Weekday</label>
                                            <select class="w-full rounded-lg border-[#E5E7EB] text-sm font-bold shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" x-model="day.weekday" :name="`roster_days[${dayIndex}][weekday]`">
                                                @foreach ($weekdayNames as $weekday => $day)
                                                    <option value="{{ $weekday }}">{{ $day }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="w-fit rounded-lg px-2 py-1 text-xs font-bold text-red-600 hover:bg-red-50" @click="rosterDays.splice(dayIndex, 1)" x-show="rosterDays.length > 1">Remove day</button>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="mb-1.5 text-[10px] font-black uppercase tracking-wide text-slate-400">Subcontractor shifts and payable hours</p>
                                            <div class="flex flex-wrap items-stretch gap-2">
                                                <template x-for="(shift, shiftIndex) in day.shifts" :key="shiftIndex">
                                                    <div class="grid w-full gap-2 rounded-lg border border-[#E5E7EB] bg-white p-2 sm:w-[16rem] sm:grid-cols-[minmax(0,1fr)_5rem_2rem]">
                                                        <input class="min-w-0 rounded-lg border-[#E5E7EB] text-xs shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" x-model="shift.label" :name="`roster_days[${dayIndex}][shifts][${shiftIndex}][label]`" placeholder="Shift A">
                                                        <input class="rounded-lg border-[#E5E7EB] text-xs shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" type="number" step="0.25" min="0" max="24" x-model="shift.hours" :name="`roster_days[${dayIndex}][shifts][${shiftIndex}][hours]`" placeholder="Hours">
                                                        <button type="button" class="rounded-lg text-xs font-bold text-red-600 hover:bg-red-50" @click="day.shifts.splice(shiftIndex, 1)" x-show="day.shifts.length > 1">×</button>
                                                    </div>
                                                </template>
                                                <button type="button" class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-lg border border-dashed border-[#0082c9]/40 bg-[#0082c9]/5 px-3 text-xs font-bold text-[#0082c9] hover:border-[#0082c9] hover:bg-[#0082c9]/10" @click="addShift(day)">
                                                    <span class="text-base leading-none">+</span> Add Shift
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <button class="mt-4 rounded-xl bg-[#0082c9] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0082c9]">Add Site</button>
                    </form>

                    <div class="mt-6 grid gap-3">
                        @forelse ($sites as $site)
                            @php
                                $weekdayOrder = array_flip(array_keys($weekdayNames));
                                $existingRosterDays = $site->shifts
                                    ->sortBy(fn ($shift) => ($weekdayOrder[$shift->weekday] ?? 99).'-'.$shift->label)
                                    ->groupBy('weekday')
                                    ->map(fn ($shifts, $weekday) => [
                                        'weekday' => $weekday,
                                        'shifts' => $shifts
                                            ->sortBy('label')
                                            ->map(fn ($shift) => [
                                                'id' => $shift->id,
                                                'label' => $shift->label,
                                                'hours' => (float) $shift->hours,
                                            ])
                                            ->values(),
                                    ])
                                    ->sortBy(fn ($day) => $weekdayOrder[$day['weekday']] ?? 99)
                                    ->values();

                                if ($existingRosterDays->isEmpty()) {
                                    $existingRosterDays = collect([[
                                        'weekday' => 'monday',
                                        'shifts' => [[
                                            'label' => 'Shift A',
                                            'hours' => '',
                                        ]],
                                    ]]);
                                }

                                $weeklySiteHours = (float) $site->weekly_contract_hours;
                                $weeklyStaffHours = $site->staffHoursForWeek();
                                $weeklyFreeHours = $site->freeHoursForWeek();
                            @endphp
                            <div class="rounded-2xl border border-[#E5E7EB] bg-white p-4 shadow-sm" x-data="{
                                editing: false,
                                pattern: '{{ $site->recurring_pattern === 'fortnightly' ? 'fortnightly' : 'weekly' }}',
                                weekdayOrder: @js(array_keys($weekdayNames)),
                                weekdayLabels: @js($weekdayNames),
                                rosterDays: @js($existingRosterDays),
                                nextShiftLabel(shifts) { return 'Shift ' + String.fromCharCode(65 + shifts.length); },
                                addWeekday() {
                                    const used = this.rosterDays.map(day => day.weekday);
                                    const next = this.weekdayOrder.find(day => ! used.includes(day)) || 'monday';
                                    this.rosterDays.push({ weekday: next, shifts: [{ label: 'Shift A', hours: '' }] });
                                },
                                addShift(day) { day.shifts.push({ label: this.nextShiftLabel(day.shifts), hours: '' }); }
                            }">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-lg bg-[#0082c9]/10 px-2.5 py-1 text-xs font-black uppercase text-[#0082c9] ring-1 ring-[#0082c9]/15">{{ $site->site_code }}</span>
                                            <h3 class="truncate text-sm font-black text-slate-900">{{ $site->name }}</h3>
                                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $site->active ? 'bg-green-50 text-green-700 ring-1 ring-green-100' : 'bg-slate-100 text-slate-500 ring-1 ring-slate-200' }}">{{ $site->active ? 'Active' : 'Inactive' }}</span>
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 ring-1 ring-slate-200">{{ $site->patternLabel() }}</span>
                                            @if ($site->needsAnchorDate())
                                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-100">Anchor date needed</span>
                                            @endif
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
                                            <span>{{ $site->shifts->count() }} subcontractor shift{{ $site->shifts->count() === 1 ? '' : 's' }}</span>
                                            <span>Site {{ number_format($weeklySiteHours, 2) }} hrs/week</span>
                                            <span>Subcontractors {{ number_format($weeklyStaffHours, 2) }} hrs/week</span>
                                            <span class="font-bold text-[#0082c9]">Free {{ number_format($weeklyFreeHours, 2) }} hrs/week</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="rounded-xl border border-[#E5E7EB] bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50" @click="editing = ! editing" x-text="editing ? 'Close' : 'Edit'"></button>
                                        <form method="POST" action="{{ route('staff-invoices.sites.destroy', $site) }}" onsubmit="return confirm('Deactivate this work log site and notify assigned subcontractors?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-600 shadow-sm hover:bg-red-100">Deactivate</button>
                                        </form>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('staff-invoices.sites.update', $site) }}" class="mt-4 border-t border-[#E5E7EB] pt-4" x-show="editing" x-cloak>
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="validation_mode" value="auto">
                                <div class="grid gap-3 xl:grid-cols-[7rem_minmax(16rem,1fr)_9rem_11rem_10rem_auto]">
                                    <input class="rounded-xl border-[#E5E7EB] text-sm font-bold uppercase shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" name="site_code" value="{{ $site->site_code }}">
                                    <input class="rounded-xl border-[#E5E7EB] text-sm font-semibold shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" name="name" value="{{ $site->name }}">
                                    <select class="rounded-xl border-[#E5E7EB] text-sm shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" name="recurring_pattern" x-model="pattern">
                                        @foreach ($patterns as $value => $label)
                                            <option value="{{ $value }}" @selected(($site->recurring_pattern === 'fortnightly' ? 'fortnightly' : 'weekly') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input class="rounded-xl border-[#E5E7EB] text-sm shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" type="number" step="0.25" min="0" name="weekly_contract_hours" value="{{ $site->weekly_contract_hours }}" placeholder="Site weekly total" required>
                                    <input class="rounded-xl border-[#E5E7EB] text-sm shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" type="date" name="fortnightly_anchor_date" value="{{ $site->fortnightly_anchor_date?->toDateString() }}" title="Fortnightly anchor date" x-show="pattern === 'fortnightly'" x-cloak>
                                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input class="rounded border-slate-300 text-[#0082c9] focus:ring-[#0082c9]" type="checkbox" name="active" value="1" @checked($site->active)> Active</label>
                                </div>
                                <div class="mt-4 rounded-2xl border border-[#E5E7EB] bg-[#F8FAFC] p-3">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Subcontractor payable roster</p>
                                            <p class="mt-1 text-xs text-slate-500">Only enter the reduced subcontractor hours. Free hours are calculated from the site weekly total.</p>
                                        </div>
                                        <button type="button" class="rounded-xl border border-[#E5E7EB] bg-white px-3 py-2 text-xs font-bold text-[#0082c9] shadow-sm hover:bg-[#0082c9]/10" @click="addWeekday()">Add Weekday</button>
                                    </div>
                                    <div class="mt-3 grid gap-2">
                                        <template x-for="(day, dayIndex) in rosterDays" :key="dayIndex">
                                            <div class="grid items-start gap-3 rounded-xl border border-[#E5E7EB] bg-white p-3 xl:grid-cols-[9rem_minmax(0,1fr)]">
                                                <div class="grid content-start gap-1.5">
                                                    <label class="text-[10px] font-black uppercase tracking-wide text-slate-400">Weekday</label>
                                                    <select class="w-full rounded-lg border-[#E5E7EB] text-sm font-bold shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" x-model="day.weekday" :name="`roster_days[${dayIndex}][weekday]`">
                                                        @foreach ($weekdayNames as $weekday => $day)
                                                            <option value="{{ $weekday }}">{{ $day }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="w-fit rounded-lg px-2 py-1 text-xs font-bold text-red-600 hover:bg-red-50" @click="rosterDays.splice(dayIndex, 1)" x-show="rosterDays.length > 1">Remove day</button>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="mb-1.5 text-[10px] font-black uppercase tracking-wide text-slate-400">Subcontractor shifts and payable hours</p>
                                                    <div class="flex flex-wrap items-stretch gap-2">
                                                        <template x-for="(shift, shiftIndex) in day.shifts" :key="shiftIndex">
                                                    <div class="grid w-full gap-2 rounded-lg border border-[#E5E7EB] bg-[#F8FAFC] p-2 sm:w-[16rem] sm:grid-cols-[minmax(0,1fr)_5rem_2rem]">
                                                        <input type="hidden" :name="`roster_days[${dayIndex}][shifts][${shiftIndex}][id]`" :value="shift.id || ''">
                                                        <input class="min-w-0 rounded-lg border-[#E5E7EB] text-xs shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" x-model="shift.label" :name="`roster_days[${dayIndex}][shifts][${shiftIndex}][label]`" placeholder="Shift A">
                                                                <input class="rounded-lg border-[#E5E7EB] text-xs shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" type="number" step="0.25" min="0" max="24" x-model="shift.hours" :name="`roster_days[${dayIndex}][shifts][${shiftIndex}][hours]`" placeholder="Hours">
                                                                <button type="button" class="rounded-lg text-xs font-bold text-red-600 hover:bg-red-50" @click="day.shifts.splice(shiftIndex, 1)" x-show="day.shifts.length > 1">×</button>
                                                            </div>
                                                        </template>
                                                        <button type="button" class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-lg border border-dashed border-[#0082c9]/40 bg-[#0082c9]/5 px-3 text-xs font-bold text-[#0082c9] hover:border-[#0082c9] hover:bg-[#0082c9]/10" @click="addShift(day)">
                                                            <span class="text-base leading-none">+</span> Add Shift
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <button class="mt-4 rounded-xl border border-[#E5E7EB] bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Save Changes</button>
                                </form>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-[#CBD5E1] bg-[#F8FAFC] p-8 text-center text-sm text-slate-500">No work log sites yet. Add your first regular site above.</div>
                        @endforelse
                    </div>
                </section>
            @else
                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <a href="{{ route('staff-invoices.index', $baseQuery + ['status' => 'pending_review']) }}" class="group rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-[0_10px_30px_rgba(15,23,42,0.04)] transition hover:-translate-y-0.5 hover:border-amber-200">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-500">Needs Review</p>
                                <p class="mt-2 text-3xl font-black text-amber-600">{{ $pendingCount ?? 0 }}</p>
                                <p class="mt-1 text-sm text-slate-400">Work logs waiting for checks</p>
                            </div>
                            <span class="grid h-11 w-11 place-items-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M12 8v5l3 2"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                    </a>
                    <a href="{{ route('staff-invoices.index', $baseQuery + ['status' => 'ready_for_payment']) }}" class="group rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-[0_10px_30px_rgba(15,23,42,0.04)] transition hover:-translate-y-0.5 hover:border-green-200">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-500">Ready to Pay</p>
                                <p class="mt-2 text-3xl font-black text-green-600">{{ $readyCount ?? 0 }}</p>
                                <p class="mt-1 text-sm text-slate-400">Approved and waiting</p>
                            </div>
                            <span class="grid h-11 w-11 place-items-center rounded-xl bg-green-50 text-green-600 ring-1 ring-green-100">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="m8 12 3 3 5-6"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                    </a>
                    <div class="rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-500">Missing Work Logs</p>
                                <p class="mt-2 text-3xl font-black text-red-600">{{ $missingInvoiceCount ?? 0 }}</p>
                                <p class="mt-1 text-sm text-slate-400">Work-log-enabled subcontractors</p>
                            </div>
                            <span class="grid h-11 w-11 place-items-center rounded-xl bg-red-50 text-red-600 ring-1 ring-red-100">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('staff-invoices.index', $baseQuery + ['status' => 'paid']) }}" class="group rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-[0_10px_30px_rgba(15,23,42,0.04)] transition hover:-translate-y-0.5 hover:border-[#0082c9]/30">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-500">Paid This Month</p>
                                <p class="mt-2 text-3xl font-black text-[#0082c9]">{{ $paidCount ?? 0 }}</p>
                                <p class="mt-1 text-sm text-slate-400">Remittances available</p>
                            </div>
                            <span class="grid h-11 w-11 place-items-center rounded-xl bg-[#0082c9]/10 text-[#0082c9] ring-1 ring-[#0082c9]/20">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M3 7h18v12H3zM3 11h18M7 15h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                    </a>
                </section>

                <section class="rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                    <div class="border-b border-[#E5E7EB] p-4 sm:p-5">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($tabs as $tab => $label)
                                <a href="{{ route('staff-invoices.index', $baseQuery + ['status' => $tab]) }}" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $status === $tab ? 'bg-[#0082c9] text-white shadow-sm' : 'bg-[#F8FAFC] text-slate-600 ring-1 ring-[#E5E7EB] hover:bg-slate-100' }}">{{ $label }}</a>
                            @endforeach
                        </div>

                        <form method="GET" action="{{ route('staff-invoices.index') }}" class="mt-4 grid gap-3 xl:grid-cols-[1.4fr_12rem_12rem_12rem_auto]">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <label class="relative">
                                <span class="sr-only">Search work logs</span>
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none"><path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                <input class="w-full rounded-xl border-[#E5E7EB] py-2.5 pl-9 text-sm shadow-sm placeholder:text-slate-400 focus:border-[#0082c9] focus:ring-[#0082c9]" name="search" value="{{ $search }}" placeholder="Search subcontractor or work log ID">
                            </label>
                            <select class="rounded-xl border-[#E5E7EB] text-sm shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" name="contractor">
                                <option value="">All Contractors</option>
                                @foreach ($contractors as $contractor)
                                    <option value="{{ $contractor->id }}" @selected((int) $contractorId === $contractor->id)>{{ $contractor->fullName() }}</option>
                                @endforeach
                            </select>
                            <select class="rounded-xl border-[#E5E7EB] text-sm shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" name="site">
                                <option value="">All Sites</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}" @selected((int) $siteId === $site->id)>{{ $site->name }}</option>
                                @endforeach
                            </select>
                            <select class="rounded-xl border-[#E5E7EB] text-sm shadow-sm focus:border-[#0082c9] focus:ring-[#0082c9]" name="status">
                                @foreach ($tabs as $tab => $label)
                                    <option value="{{ $tab }}" @selected($status === $tab)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-xl bg-[#0082c9] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0082c9]">Apply Filters</button>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1180px] text-left text-sm">
                            <thead class="bg-[#F8FAFC] text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Work Log ID</th>
                                    <th class="px-5 py-3">Contractor</th>
                                    <th class="px-5 py-3">Work Log Month</th>
                                    <th class="px-5 py-3">Sites</th>
                                    <th class="px-5 py-3">Regular Hours</th>
                                    <th class="px-5 py-3">Other Work</th>
                                    <th class="px-5 py-3">Claimed Total</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3">Submitted</th>
                                    <th class="px-5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB] bg-white">
                                @forelse ($invoices as $invoice)
                                    @php
                                        $logs = $invoice->workLogs;
                                        $siteNames = $logs->pluck('site_name')->filter()->unique()->values();
                                        $regularLogs = $logs->where('work_type', 'regular');
                                        $regularClaimed = (float) $regularLogs->sum('hours');
                                        $regularExpected = $regularLogs
                                            ->groupBy(fn ($log) => $log->invoice_site_id ?: $log->site_name)
                                            ->sum(fn ($siteLogs) => $siteLogs->first()?->site ? $siteLogs->first()->site->expectedHoursForMonth($invoice->invoice_period) : 0);
                                        $otherAmount = (float) $logs->where('work_type', 'other_work')->sum('amount');
                                        $invoiceId = $invoice->invoice_reference ?: 'INV-'.str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT);
                                        $hoursOk = abs($regularClaimed - $regularExpected) < 0.01;
                                    @endphp
                                    <tr class="transition hover:bg-[#F8FAFC]">
                                        <td class="px-5 py-4 font-bold text-[#0082c9]">{{ $invoiceId }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="grid h-8 w-8 place-items-center rounded-full bg-[#0082c9]/10 text-xs font-black text-[#0082c9] ring-1 ring-[#0082c9]/20">{{ str($invoice->staffMember?->fullName() ?? 'U')->substr(0, 1)->upper() }}</span>
                                                <span class="font-semibold text-slate-900">{{ $invoice->staffMember?->fullName() }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-slate-600">{{ $invoice->invoice_period?->format('F Y') }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ $siteNames->count() }}</td>
                                        <td class="px-5 py-4">
                                            <span class="font-bold {{ $hoursOk ? 'text-green-700' : 'text-red-700' }}">{{ number_format($regularExpected, 2) }} / {{ number_format($regularClaimed, 2) }}</span>
                                            <span class="ml-2 text-xs text-slate-400">expected / claimed</span>
                                        </td>
                                        <td class="px-5 py-4 text-slate-700">${{ number_format($otherAmount, 2) }}</td>
                                        <td class="px-5 py-4 font-black text-slate-950">${{ number_format((float) $invoice->total_amount, 2) }}</td>
                                        <td class="px-5 py-4"><span class="badge {{ $invoice->statusBadgeClass() }}">{{ $invoice->statusLabel() }}</span></td>
                                        <td class="px-5 py-4 text-slate-600">{{ $invoice->submitted_at?->format('d M Y') ?: '-' }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex justify-end gap-2">
                                                <a class="rounded-xl bg-[#0082c9] px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#0082c9]" href="{{ route('staff-invoices.review', $invoice) }}">Review</a>
                                                @if ($invoice->remittance_path)
                                                    <a class="grid h-9 w-9 place-items-center rounded-xl border border-[#E5E7EB] bg-white text-slate-600 transition hover:border-[#0082c9] hover:text-[#0082c9]" href="{{ route('staff-invoices.remittance', $invoice) }}" aria-label="Download remittance">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="px-5 py-14 text-center text-slate-500">No work logs found for {{ $monthDate->format('F Y') }}.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#E5E7EB] px-5 py-4 text-sm text-slate-500">
                        <p>Showing {{ $invoices->count() }} work log{{ $invoices->count() === 1 ? '' : 's' }}</p>
                        <p>All amounts are inclusive of GST where applicable.</p>
                    </div>
                </section>

                <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-black tracking-tight text-slate-950">Site Reconciliation</h2>
                            <p class="mt-1 text-sm text-slate-500">Regular work only. Claimed hours combine every active subcontractor work log for this month; free hours show the reserve kept from contract hours.</p>
                        </div>
                        @if (($missingInvoiceCount ?? 0) > 0)
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">{{ $missingInvoiceCount }} missing subcontractor work log{{ $missingInvoiceCount === 1 ? '' : 's' }}</span>
                        @else
                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700 ring-1 ring-green-200">All subcontractor work logs received</span>
                        @endif
                    </div>
                    <div class="mt-5 overflow-x-auto rounded-2xl border border-[#E5E7EB]">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-[#F8FAFC] text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">Site</th>
                                    <th class="px-4 py-3">Pattern</th>
                                    <th class="px-4 py-3">Site Monthly Total</th>
                                    <th class="px-4 py-3">Subcontractor Roster</th>
                                    <th class="px-4 py-3">Claimed</th>
                                    <th class="px-4 py-3">Unclaimed</th>
                                    <th class="px-4 py-3">Free Hours</th>
                                    <th class="px-4 py-3">Variance</th>
                                    <th class="px-4 py-3">Flags</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB]">
                                @forelse ($siteSummaries as $row)
                                    @php
                                        $variance = (float) $row['variance'];
                                        $unclaimed = (float) ($row['unclaimed_hours'] ?? 0);
                                        $awaitingInvoices = ! ($allInvoicesSubmitted ?? false) && $unclaimed > 0.009;
                                    @endphp
                                    <tr class="transition hover:bg-[#F8FAFC]">
                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-slate-900">{{ $row['site']->displayName() }}</p>
                                            @if ($row['validation_warning'])
                                                <p class="mt-1 text-xs font-semibold text-amber-700">{{ $row['validation_warning'] }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 ring-1 ring-slate-200">{{ $row['pattern_label'] }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-slate-600">{{ number_format((float) ($row['contract_hours'] ?? $row['expected_hours']), 2) }}</td>
                                        <td class="px-4 py-4 text-slate-600">{{ number_format((float) $row['expected_hours'], 2) }}</td>
                                        <td class="px-4 py-4 text-slate-600">{{ number_format((float) $row['claimed_hours'], 2) }}</td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex max-w-[12rem] items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $awaitingInvoices ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-green-50 text-green-700 ring-green-200' }}">
                                                {{ number_format($unclaimed, 2) }}{{ $awaitingInvoices ? ' - Missing subcontractor work logs' : ($unclaimed > 0.009 ? ' unclaimed' : '') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-[#0082c9] ring-1 ring-[#0082c9]/20">{{ number_format((float) ($row['free_hours'] ?? 0), 2) }}</span>
                                        </td>
                                        <td class="px-4 py-4 font-bold {{ abs($variance) < 0.01 || ($variance < 0 && ! $awaitingInvoices) ? 'text-green-700' : 'text-amber-700' }}">{{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 2) }}</td>
                                        <td class="px-4 py-4 text-slate-600">{{ $row['flag_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="px-4 py-10 text-center text-slate-500">No site reconciliation data for this month.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection
