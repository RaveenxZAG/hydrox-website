<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeToggle">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=hydrox-1">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}?v=hydrox-1">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'Hydrox Portal') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
        $navItems = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'pattern' => 'dashboard', 'icon' => 'dashboard'],
            ['label' => 'Bookings', 'route' => 'bookings.index', 'pattern' => 'bookings.*', 'icon' => 'bookings'],
        ];

        $staffItems = [
            ['label' => 'Onboarding', 'route' => 'subcontractor-onboardings.index', 'pattern' => 'subcontractor-onboardings.*'],
            ['label' => 'Directory', 'route' => 'staff-members.index', 'pattern' => 'staff-members.*'],
            ['label' => 'Profile Requests', 'route' => 'staff-profile-changes.index', 'pattern' => 'staff-profile-changes.*'],
        ];

        $invoiceCounts = [
            'pending_review' => 0,
            'ready_for_payment' => 0,
            'paid' => 0,
            'correction_required' => 0,
        ];
        $invoiceMonthKey = preg_match('/^\d{4}-\d{2}$/', (string) request('month'))
            ? (string) request('month')
            : now('Australia/Melbourne')->subMonthNoOverflow()->format('Y-m');
        $invoiceMonthDate = \Carbon\Carbon::createFromFormat('Y-m', $invoiceMonthKey, 'Australia/Melbourne')->startOfMonth();

        if (\Illuminate\Support\Facades\Schema::hasTable('staff_invoice_submissions')) {
            $invoiceCounts['pending_review'] = \App\Models\StaffInvoiceSubmission::query()
                ->whereDate('invoice_period', $invoiceMonthDate->toDateString())
                ->whereIn('status', ['pending_review', 'resubmitted'])
                ->count();
            $invoiceCounts['ready_for_payment'] = \App\Models\StaffInvoiceSubmission::query()
                ->whereDate('invoice_period', $invoiceMonthDate->toDateString())
                ->where('status', 'ready_for_payment')
                ->count();
            $invoiceCounts['paid'] = \App\Models\StaffInvoiceSubmission::query()
                ->whereDate('invoice_period', $invoiceMonthDate->toDateString())
                ->where('status', 'paid')
                ->count();
            $invoiceCounts['correction_required'] = \App\Models\StaffInvoiceSubmission::query()
                ->whereDate('invoice_period', $invoiceMonthDate->toDateString())
                ->where('status', 'correction_required')
                ->count();
        }

        $settingsItems = [
            ['label' => 'Business Information', 'route' => 'settings.business', 'pattern' => 'settings.business*'],
            ['label' => 'Work Log Template', 'route' => 'invoice-template.edit', 'pattern' => 'invoice-template.*'],
            ['label' => 'System Maintenance', 'route' => 'settings.maintenance', 'pattern' => 'settings.maintenance'],
        ];

        $companyPortals = collect(config('company-portals', []))
            ->filter(fn (mixed $portal): bool => is_array($portal) && filled($portal['url'] ?? null))
            ->map(function (array $portal): array {
                $host = parse_url($portal['url'], PHP_URL_HOST);

                return [
                    ...$portal,
                    'host' => $host,
                    'current' => filled($host) && strcasecmp((string) request()->getHost(), (string) $host) === 0,
                ];
            });

        $sidebarIcon = function (string $name): string {
            $icons = [
                'dashboard' => '<path d="M3.5 10.5 12 3l8.5 7.5"/><path d="M5.5 9.5V20h5v-5h3v5h5V9.5"/>',
                'bookings' => '<path d="M7 3v3M17 3v3M4 9h16"/><rect x="4" y="5" width="16" height="16" rx="2"/><path d="m8 14 2.5 2.5L16 11"/>',
                'email' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
                'staff' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                'invoices' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/><path d="M9 9h1"/>',
                'jobs' => '<path d="M9 6h11"/><path d="M9 12h11"/><path d="M9 18h11"/><path d="m3.5 6 1 1 2-2"/><path d="m3.5 12 1 1 2-2"/><path d="m3.5 18 1 1 2-2"/>',
                'reports' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/>',
                'subcontractors' => '<path d="M8 7a4 4 0 1 0 8 0 4 4 0 0 0-8 0Z"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/><path d="m16.5 13.5 2 2 4-4"/>',
                'settings' => '<path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6l-.1.1a2 2 0 1 1-3.8 0L10 20a1.7 1.7 0 0 0-1-.6 1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1l-.1-.1a2 2 0 1 1 0-3.8L4 10a1.7 1.7 0 0 0 .6-1 1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6l.1-.1a2 2 0 1 1 3.8 0l.1.1a1.7 1.7 0 0 0 1 .6 1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.2.36.4.68.6 1l.1.1a2 2 0 1 1 0 3.8l-.1.1a1.7 1.7 0 0 0-.6 1Z"/>',
                'portals' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/>',
            ];

            return '<svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" aria-hidden="true" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'.($icons[$name] ?? $icons['dashboard']).'</svg>';
        };

        $systemNotifications = \App\Models\SystemNotification::query()
            ->unread()
            ->latest()
            ->limit(8)
            ->get();
        $systemNotificationCount = \App\Models\SystemNotification::unread()->count();
    @endphp

    <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-[#eef3f6] text-slate-950 dark:bg-slate-950 dark:text-slate-100">
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-slate-950/50 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col overflow-hidden border-r border-slate-200 bg-white text-slate-700 shadow-2xl shadow-slate-900/10 transition-transform duration-300 ease-out dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div class="h-1 bg-gradient-to-r from-[#0082c9] to-[#11d394]"></div>

            <div class="relative border-b border-slate-200 px-3 py-3 dark:border-slate-800">
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-1 items-center gap-3">
                        <span class="grid h-12 w-36 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <img class="h-full w-full rounded-lg object-contain" src="{{ asset('images/hydrox-logo.svg') }}" alt="Hydrox logo">
                        </span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-rose-900 dark:hover:bg-rose-950 dark:hover:text-rose-200">
                            Logout
                        </button>
                    </form>
                </div>

                <label class="mt-3 flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input class="w-full border-0 bg-transparent p-0 text-sm text-slate-800 placeholder:text-slate-400 focus:ring-0 dark:text-slate-100 dark:placeholder:text-slate-600" type="search" placeholder="Search">
                </label>

                <div class="mt-3 rounded-lg border border-[#0082c9]/15 bg-[#0082c9]/5 px-3 py-2 dark:bg-[#0082c9]/10">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#0082c9]">Workspace</p>
                    <p class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400">Bookings and business operations.</p>
                </div>
            </div>

            <nav class="relative min-h-0 flex-1 overflow-y-auto px-2.5 py-3">
                <p class="px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wide text-slate-400">Menu</p>

                <div class="grid gap-1">
                    @foreach ($navItems as $item)
                        @php $active = request()->routeIs($item['pattern']); @endphp
                        <a href="{{ route($item['route']) }}" class="group relative flex h-10 items-center gap-3 rounded-xl px-3 text-sm font-semibold transition {{ $active ? 'bg-[#0082c9]/10 text-slate-950 shadow-sm ring-1 ring-[#0082c9]/15 dark:text-white dark:ring-[#0082c9]/25' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white' }}">
                            @if ($active)
                                <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#0082c9]"></span>
                            @endif
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg {{ $active ? 'bg-white text-[#0082c9] shadow-sm dark:bg-slate-900 dark:text-cyan-300' : 'bg-slate-100 text-slate-500 group-hover:text-[#0082c9] dark:bg-slate-900 dark:text-slate-400' }}">
                                {!! $sidebarIcon($item['icon']) !!}
                            </span>
                            <span class="truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <div x-data="{ open: {{ request()->routeIs('emails.*') ? 'true' : 'false' }} }" class="mt-1">
                    @php $emailActive = request()->routeIs('emails.*'); @endphp
                    <button type="button" class="group relative flex h-10 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold transition {{ $emailActive ? 'bg-[#0082c9]/10 text-slate-950 shadow-sm ring-1 ring-[#0082c9]/15 dark:text-white dark:ring-[#0082c9]/25' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white' }}" @click="open = !open" :aria-expanded="open">
                        @if ($emailActive)
                            <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#0082c9]"></span>
                        @endif
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg {{ $emailActive ? 'bg-white text-[#0082c9] shadow-sm dark:bg-slate-900 dark:text-cyan-300' : 'bg-slate-100 text-slate-500 group-hover:text-[#0082c9] dark:bg-slate-900 dark:text-slate-400' }}">
                            {!! $sidebarIcon('email') !!}
                        </span>
                        <span class="min-w-0 flex-1 truncate">Email</span>
                        <span class="grid h-5 w-5 place-items-center rounded-md text-slate-400 transition-transform duration-200" :class="open ? 'rotate-90 text-[#0082c9]' : ''">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </button>
                    <div x-show="open" x-cloak class="ml-[26px] mt-1 grid gap-0.5 border-l border-slate-200 pl-4 dark:border-slate-800">
                        <a href="{{ route('emails.create') }}" class="relative flex h-8 items-center rounded-lg px-3 text-[13px] font-semibold transition {{ $emailActive ? 'bg-[#0082c9]/8 text-[#0082c9] dark:text-cyan-300' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-white' }}">
                            @if ($emailActive)
                                <span class="absolute -left-[17px] top-1/2 h-1.5 w-1.5 -translate-y-1/2 rounded-full bg-[#0082c9]"></span>
                            @endif
                            <span class="truncate">Send new email</span>
                        </a>
                    </div>
                </div>

                <div x-data="{ open: {{ request()->routeIs('subcontractor-onboardings.*') || request()->routeIs('staff-members.*') || request()->routeIs('staff-profile-changes.*') ? 'true' : 'false' }} }" class="mt-1">
                    @php $staffActive = request()->routeIs('subcontractor-onboardings.*') || request()->routeIs('staff-members.*') || request()->routeIs('staff-profile-changes.*'); @endphp
                    <button type="button" class="group relative flex h-10 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold transition {{ $staffActive ? 'bg-[#0082c9]/10 text-slate-950 shadow-sm ring-1 ring-[#0082c9]/15 dark:text-white dark:ring-[#0082c9]/25' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white' }}" @click="open = !open" :aria-expanded="open">
                        @if ($staffActive)
                            <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#0082c9]"></span>
                        @endif
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg {{ $staffActive ? 'bg-white text-[#0082c9] shadow-sm dark:bg-slate-900 dark:text-cyan-300' : 'bg-slate-100 text-slate-500 group-hover:text-[#0082c9] dark:bg-slate-900 dark:text-slate-400' }}">
                            {!! $sidebarIcon('staff') !!}
                        </span>
                        <span class="min-w-0 flex-1 truncate">Subcontractors</span>
                        <span class="grid h-5 w-5 place-items-center rounded-md text-slate-400 transition-transform duration-200" :class="open ? 'rotate-90 text-[#0082c9]' : ''">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </button>
                    <div x-show="open" x-cloak class="ml-[26px] mt-1 grid gap-0.5 border-l border-slate-200 pl-4 dark:border-slate-800">
                        @foreach ($staffItems as $item)
                            @php $active = request()->routeIs($item['pattern']); @endphp
                            <a href="{{ route($item['route']) }}" class="relative flex h-8 items-center rounded-lg px-3 text-[13px] font-semibold transition {{ $active ? 'bg-[#0082c9]/8 text-[#0082c9] dark:text-cyan-300' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-white' }}">
                                @if ($active)
                                    <span class="absolute -left-[17px] top-1/2 h-1.5 w-1.5 -translate-y-1/2 rounded-full bg-[#0082c9]"></span>
                                @endif
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div x-data="{ open: {{ request()->routeIs('staff-invoices.*') ? 'true' : 'false' }} }" class="mt-1">
                    @php
                        $invoiceActive = request()->routeIs('staff-invoices.*');
                        $invoiceStatus = request('status');
                        $invoiceLinks = [
                            [
                                'label' => 'All Work Logs',
                                'href' => route('staff-invoices.index', ['month' => $invoiceMonthKey]),
                                'active' => $invoiceActive && blank($invoiceStatus),
                                'count' => null,
                                'badge' => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
                            ],
                            [
                                'label' => 'Pending Review',
                                'href' => route('staff-invoices.index', ['month' => $invoiceMonthKey, 'status' => 'pending_review']),
                                'active' => $invoiceActive && $invoiceStatus === 'pending_review',
                                'count' => $invoiceCounts['pending_review'],
                                'badge' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100 dark:bg-amber-950 dark:text-amber-200 dark:ring-amber-900',
                            ],
                            [
                                'label' => 'Ready for Payment',
                                'href' => route('staff-invoices.index', ['month' => $invoiceMonthKey, 'status' => 'ready_for_payment']),
                                'active' => $invoiceActive && $invoiceStatus === 'ready_for_payment',
                                'count' => $invoiceCounts['ready_for_payment'],
                                'badge' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-950 dark:text-emerald-200 dark:ring-emerald-900',
                            ],
                            [
                                'label' => 'Paid',
                                'href' => route('staff-invoices.index', ['month' => $invoiceMonthKey, 'status' => 'paid']),
                                'active' => $invoiceActive && $invoiceStatus === 'paid',
                                'count' => $invoiceCounts['paid'],
                                'badge' => 'bg-[#0082c9]/10 text-[#0082c9] ring-1 ring-[#0082c9]/20 dark:bg-[#0082c9]/20 dark:text-cyan-200 dark:ring-[#0082c9]/30',
                            ],
                            [
                                'label' => 'Correction Required',
                                'href' => route('staff-invoices.index', ['month' => $invoiceMonthKey, 'status' => 'correction_required']),
                                'active' => $invoiceActive && $invoiceStatus === 'correction_required',
                                'count' => $invoiceCounts['correction_required'],
                                'badge' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-100 dark:bg-rose-950 dark:text-rose-200 dark:ring-rose-900',
                            ],
                        ];
                    @endphp
                    <button type="button" class="group relative flex h-10 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold transition {{ $invoiceActive ? 'bg-[#0082c9]/10 text-slate-950 shadow-sm ring-1 ring-[#0082c9]/15 dark:text-white dark:ring-[#0082c9]/25' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white' }}" @click="open = !open" :aria-expanded="open">
                        @if ($invoiceActive)
                            <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#0082c9]"></span>
                        @endif
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg {{ $invoiceActive ? 'bg-white text-[#0082c9] shadow-sm dark:bg-slate-900 dark:text-cyan-300' : 'bg-slate-100 text-slate-500 group-hover:text-[#0082c9] dark:bg-slate-900 dark:text-slate-400' }}">
                            {!! $sidebarIcon('invoices') !!}
                        </span>
                        <span class="min-w-0 flex-1 truncate">Work Logs</span>
                        <span class="grid h-5 w-5 place-items-center rounded-md text-slate-400 transition-transform duration-200" :class="open ? 'rotate-90 text-[#0082c9]' : ''">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </button>
                    <div x-show="open" x-cloak class="ml-[26px] mt-1 grid gap-0.5 border-l border-slate-200 pl-4 dark:border-slate-800">
                        @foreach ($invoiceLinks as $item)
                            <a href="{{ $item['href'] }}" class="relative flex h-8 items-center justify-between gap-2 rounded-lg px-3 text-[13px] font-semibold transition {{ $item['active'] ? 'bg-[#0082c9]/8 text-[#0082c9] dark:text-cyan-300' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-white' }}">
                                @if ($item['active'])
                                    <span class="absolute -left-[17px] top-1/2 h-1.5 w-1.5 -translate-y-1/2 rounded-full bg-[#0082c9]"></span>
                                @endif
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if (! is_null($item['count']) && $item['count'] > 0)
                                    <span class="grid h-5 min-w-5 shrink-0 place-items-center rounded-full bg-slate-100 px-1.5 text-[11px] font-bold text-slate-600 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">{{ $item['count'] > 99 ? '99+' : $item['count'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <div x-data="{ open: {{ request()->routeIs('invoice-template.*') || request()->routeIs('settings.*') ? 'true' : 'false' }} }" class="mt-1">
                    @php $settingsActive = request()->routeIs('invoice-template.*') || request()->routeIs('settings.*'); @endphp
                    <button type="button" class="group relative flex h-10 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold transition {{ $settingsActive ? 'bg-[#0082c9]/10 text-slate-950 shadow-sm ring-1 ring-[#0082c9]/15 dark:text-white dark:ring-[#0082c9]/25' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white' }}" @click="open = !open" :aria-expanded="open">
                        @if ($settingsActive)
                            <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#0082c9]"></span>
                        @endif
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg {{ $settingsActive ? 'bg-white text-[#0082c9] shadow-sm dark:bg-slate-900 dark:text-cyan-300' : 'bg-slate-100 text-slate-500 group-hover:text-[#0082c9] dark:bg-slate-900 dark:text-slate-400' }}">
                            {!! $sidebarIcon('settings') !!}
                        </span>
                        <span class="min-w-0 flex-1 truncate">Settings</span>
                        <span class="grid h-5 w-5 place-items-center rounded-md text-slate-400 transition-transform duration-200" :class="open ? 'rotate-90 text-[#0082c9]' : ''">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </button>
                    <div x-show="open" x-cloak class="ml-[26px] mt-1 grid gap-0.5 border-l border-slate-200 pl-4 dark:border-slate-800">
                        @foreach ($settingsItems as $item)
                            @php $active = request()->routeIs($item['pattern']); @endphp
                            <a href="{{ route($item['route']) }}" class="relative flex h-8 items-center rounded-lg px-3 text-[13px] font-semibold transition {{ $active ? 'bg-[#0082c9]/8 text-[#0082c9] dark:text-cyan-300' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-white' }}">
                                @if ($active)
                                    <span class="absolute -left-[17px] top-1/2 h-1.5 w-1.5 -translate-y-1/2 rounded-full bg-[#0082c9]"></span>
                                @endif
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

            </nav>

            <div x-data="{ portalsOpen: false }" class="relative border-t border-slate-200 px-3 pt-3 dark:border-slate-800">
                <button
                    type="button"
                    class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white"
                    @click="portalsOpen = ! portalsOpen"
                    :aria-expanded="portalsOpen"
                    aria-controls="company-portals-panel"
                >
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-cyan-50 text-[#0082c9] dark:bg-cyan-950 dark:text-cyan-300">
                        {!! $sidebarIcon('portals') !!}
                    </span>
                    <span class="min-w-0 flex-1 truncate">Company Links</span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="portalsOpen ? 'rotate-180 text-[#0082c9]' : ''" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>

                <div id="company-portals-panel" x-show="portalsOpen" x-cloak class="mt-2 grid gap-1.5 rounded-xl border border-slate-200 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-900">
                    @foreach ($companyPortals as $portal)
                        <a
                            class="group flex items-start gap-3 rounded-lg bg-white px-3 py-2.5 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:ring-cyan-300 dark:bg-slate-950 dark:ring-slate-800 dark:hover:ring-cyan-800"
                            href="{{ $portal['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg {{ $portal['current'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-cyan-50 text-[#0082c9] dark:bg-cyan-950 dark:text-cyan-300' }}">
                                @switch($portal['icon'])
                                    @case('admin')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20V8l8-5 8 5v12M9 20v-6h6v6M8 10h.01M16 10h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @break
                                    @case('profile')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                        @break
                                    @default
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 11v6m0-10h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                @endswitch
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center gap-2">
                                    <span class="truncate text-xs font-black text-slate-900 dark:text-white">{{ $portal['label'] }}</span>
                                    @if ($portal['current'])
                                        <span class="rounded-full bg-emerald-50 px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wide text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Current</span>
                                    @endif
                                </span>
                                <span class="mt-0.5 block text-[10px] leading-4 text-slate-500 dark:text-slate-400">{{ $portal['description'] }}</span>
                                <span class="mt-1 block truncate text-[10px] font-semibold text-[#0082c9] dark:text-cyan-300">{{ $portal['host'] }}</span>
                            </span>
                            <svg class="mt-1 h-3.5 w-3.5 shrink-0 text-slate-400 transition group-hover:text-[#0082c9]" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 5h5v5M11 13l8-8M19 13v6H5V5h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="relative p-3">
                <button type="button" class="flex h-10 w-full items-center justify-between rounded-xl bg-slate-50 px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" @click="toggle">
                    <span>Appearance</span>
                    <span x-text="dark ? 'Dark' : 'Light'" class="rounded-full bg-white px-2 py-1 text-xs text-slate-500 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-400 dark:ring-slate-800"></span>
                </button>
            </div>
        </aside>

        <main class="min-w-0 lg:pl-72">
            <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90">
                <div class="flex min-h-16 flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                            <button type="button" class="grid h-10 w-10 place-items-center rounded-lg border border-slate-200 bg-white text-xs font-black uppercase text-slate-700 shadow-sm lg:hidden dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200" @click="sidebarOpen = true" aria-label="Open menu">
                            Menu
                        </button>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">@yield('eyebrow', 'Hydrox Portal')</p>
                            <h1 class="truncate text-xl font-black tracking-tight sm:text-2xl">@yield('title', 'Dashboard')</h1>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div x-data="{ open: false }" class="relative">
                            <button type="button" class="relative grid h-10 w-10 place-items-center rounded-lg border shadow-sm transition dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 {{ $systemNotificationCount > 0 ? 'border-rose-200 bg-rose-50 text-rose-700 hover:border-rose-300 hover:bg-rose-100' : 'border-slate-200 bg-white text-slate-600 hover:border-[#0082c9]/30 hover:bg-[#0082c9]/10 hover:text-slate-950' }}" @click="open = !open" @click.outside="open = false" aria-label="Notifications">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M15 17H9m10-1.8c-.9-1-1.5-1.5-1.5-4.2a5.5 5.5 0 0 0-11 0c0 2.7-.6 3.2-1.5 4.2-.4.4-.1 1.1.5 1.1h13c.6 0 .9-.7.5-1.1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 20a2.2 2.2 0 0 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                @if ($systemNotificationCount > 0)
                                    <span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-950">
                                        {{ $systemNotificationCount > 9 ? '9+' : $systemNotificationCount }}
                                    </span>
                                @endif
                            </button>

                            <div x-show="open" x-cloak class="absolute right-0 z-30 mt-3 w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/15 dark:border-slate-800 dark:bg-slate-950">
                                <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-bold text-slate-950 dark:text-white">Notifications</p>
                                            <p class="text-xs text-slate-500">System alerts and submissions</p>
                                        </div>
                                        @if ($systemNotificationCount > 0)
                                            <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ $systemNotificationCount }} new</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="max-h-96 overflow-y-auto p-2">
                                    @forelse ($systemNotifications as $notification)
                                        <a href="{{ route('notifications.open', $notification) }}" class="block rounded-lg px-3 py-3 transition hover:bg-slate-50 dark:hover:bg-slate-900">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $notification->title }}</p>
                                                    @if ($notification->message)
                                                        <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $notification->message }}</p>
                                                    @endif
                                                </div>
                                                @if (! $notification->read_at)
                                                    <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-rose-600"></span>
                                                @endif
                                            </div>
                                            <p class="mt-2 text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                                        </a>
                                    @empty
                                        <div class="px-3 py-8 text-center">
                                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">No notifications</p>
                                            <p class="mt-1 text-xs text-slate-500">New submissions and system alerts will appear here.</p>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="border-t border-slate-200 p-2 dark:border-slate-800">
                                    @if ($systemNotificationCount > 0)
                                        <form method="POST" action="{{ route('notifications.read-all') }}">
                                            @csrf
                                            <button class="w-full rounded-lg px-3 py-2 text-center text-sm font-semibold text-[#0082c9] transition hover:bg-[#0082c9]/10">
                                                Mark all as read
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2 text-center text-sm font-semibold text-[#0082c9] transition hover:bg-[#0082c9]/10">
                                            Back to dashboard
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @yield('actions')
                    </div>
                </div>
            </header>

            @php
                $flashMessages = collect([
                    session('status') ? ['type' => 'success', 'message' => session('status')] : null,
                    session('error') ? ['type' => 'error', 'message' => session('error')] : null,
                    $errors->any() ? ['type' => 'warning', 'message' => 'Please check the highlighted fields and try again.'] : null,
                ])->filter();
            @endphp

            @if ($flashMessages->isNotEmpty())
                <div class="fixed left-1/2 top-20 z-50 grid w-[calc(100%-2rem)] max-w-xl -translate-x-1/2 gap-3">
                    @foreach ($flashMessages as $flash)
                        <div
                            x-data="{ show: true }"
                            x-init="setTimeout(() => show = false, 10000)"
                            x-show="show"
                            x-transition
                            class="rounded-xl border px-4 py-3 text-sm shadow-2xl shadow-slate-900/15 backdrop-blur {{ $flash['type'] === 'success' ? 'border-emerald-200 bg-emerald-50/95 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/95 dark:text-emerald-100' : ($flash['type'] === 'warning' ? 'border-amber-200 bg-amber-50/95 text-amber-800 dark:border-amber-800 dark:bg-amber-950/95 dark:text-amber-100' : 'border-rose-200 bg-rose-50/95 text-rose-800 dark:border-rose-800 dark:bg-rose-950/95 dark:text-rose-100') }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <p class="font-semibold">{{ $flash['message'] }}</p>
                                <button type="button" class="text-lg leading-none opacity-60 transition hover:opacity-100" @click="show = false" aria-label="Close notification">&times;</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="px-4 py-6 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
