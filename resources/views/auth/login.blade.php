@extends('layouts.auth')
@section('auth-full-width', true)
@section('hide-error-banner', true)

@section('content')
    <div class="min-h-screen overflow-hidden bg-[#f4f8fb] text-[#07142e]">
        <section class="relative isolate overflow-hidden bg-[#06142d] text-white">
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_78%_18%,rgba(17,211,148,0.2),transparent_28%),radial-gradient(circle_at_58%_95%,rgba(0,130,201,0.32),transparent_42%)]"></div>
            <div class="absolute -right-40 -top-44 -z-10 h-[38rem] w-[38rem] rounded-full border border-white/10"></div>
            <div class="absolute -right-16 -top-20 -z-10 h-[27rem] w-[27rem] rounded-full border border-[#11d394]/20"></div>
            <div class="absolute right-20 top-12 -z-10 h-[17rem] w-[17rem] rounded-full border border-[#0082c9]/35"></div>
            <div class="absolute bottom-0 left-0 -z-10 h-24 w-full bg-[linear-gradient(178deg,transparent_0%,transparent_48%,#f4f8fb_49%,#f4f8fb_100%)]"></div>

            <header class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-5 py-5 sm:px-8 lg:px-12">
                <a class="flex items-center gap-4" href="https://hydrox.au">
                    <span class="grid h-12 w-32 place-items-center rounded-2xl bg-white px-3 shadow-xl shadow-black/10">
                        <img class="h-full w-full object-contain" src="{{ asset('images/hydrox-logo.svg') }}" alt="Hydrox Facility Management">
                    </span>
                    <span class="hidden sm:block">
                        <span class="block text-sm font-black tracking-tight">Hydrox Facility Management</span>
                        <span class="mt-0.5 block text-xs font-semibold text-white/55">Secure business portal</span>
                    </span>
                </a>

                <div class="inline-flex items-center gap-3 rounded-full border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-bold text-white/75 backdrop-blur">
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-[#11d394]/15 text-[#76edbf]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="3" stroke="currentColor" stroke-width="2"/><path d="M8.5 10V7.5a3.5 3.5 0 1 1 7 0V10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    Secure portal
                </div>
            </header>

            <div class="mx-auto grid max-w-7xl gap-10 px-5 pb-32 pt-12 sm:px-8 sm:pt-16 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:px-12 lg:pb-40 lg:pt-20">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-[#76edbf]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#11d394] shadow-[0_0_12px_#11d394]"></span>
                        Hydrox booking portal
                    </div>
                    <h1 class="mt-6 text-5xl font-black leading-[0.98] tracking-[-0.055em] sm:text-6xl lg:text-7xl">
                        Bookings, organised.<br>
                        <span class="bg-gradient-to-r from-[#39aee9] via-[#31d3bc] to-[#7bea9b] bg-clip-text text-transparent">Operations, in control.</span>
                    </h1>
                    <p class="mt-7 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">
                        A secure workspace for receiving, reviewing and managing Hydrox service bookings from one clear dashboard.
                    </p>
                    <div class="mt-8">
                        <button type="button" class="inline-flex items-center gap-3 rounded-full bg-gradient-to-r from-[#0082c9] to-[#11d394] px-6 py-3.5 text-sm font-black text-white shadow-xl shadow-cyan-950/30 transition hover:-translate-y-0.5 hover:shadow-2xl" data-admin-login-open>
                            Sign in to the portal
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>

                <div class="relative hidden min-h-[25rem] lg:block">
                    <div class="absolute inset-8 rounded-[3rem] border border-white/10 bg-white/[0.04] backdrop-blur"></div>
                    <div class="absolute left-0 top-16 w-64 -rotate-3 rounded-[2rem] border border-white/15 bg-white/10 p-5 shadow-2xl shadow-black/20 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-[#0082c9]/20 text-[#39aee9]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M8 3h8v4H8zM6 5H5a2 2 0 0 0-2 2v12h18V7a2 2 0 0 0-2-2h-1M8 12h8M8 16h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                            <span class="rounded-full bg-[#11d394]/15 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-[#76edbf]">Live</span>
                        </div>
                        <p class="mt-5 text-xs font-bold uppercase tracking-[0.16em] text-white/45">New enquiries</p>
                        <p class="mt-1 text-xl font-black">Bookings in one place</p>
                    </div>
                    <div class="absolute bottom-10 right-0 w-72 rotate-3 rounded-[2rem] border border-white/15 bg-gradient-to-br from-white/15 to-white/[0.04] p-5 shadow-2xl shadow-black/20 backdrop-blur-xl">
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-[#11d394]/15 text-[#76edbf]"><svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"><path d="M7 3v3m10-3v3M4 9h16M6 5h12a2 2 0 0 1 2 2v13H4V7a2 2 0 0 1 2-2Zm3 8h2m3 0h2m-7 4h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></div>
                        <p class="mt-5 text-xs font-bold uppercase tracking-[0.16em] text-white/45">Workflow</p>
                        <p class="mt-1 text-xl font-black">Review every request</p>
                    </div>
                    <div class="absolute left-[34%] top-[34%] grid h-36 w-64 place-items-center rounded-[2rem] border border-white/70 bg-white px-6 shadow-[0_0_80px_rgba(17,211,148,0.25)]">
                        <img class="h-full w-full object-contain" src="{{ asset('images/hydrox-logo.svg') }}" alt="Hydrox Facility Management">
                    </div>
                </div>
            </div>
        </section>

        <section class="relative z-10 mx-auto max-w-7xl px-5 pb-12 pt-10 sm:px-8 sm:pt-12 lg:px-12">
            <div class="mb-6">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-[#0082c9]">For subcontractors</p>
                <h2 class="mt-2 text-3xl font-black tracking-[-0.035em] text-[#07142e]">Team access and resources</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Use the team portal for work logs and profile updates, or submit an application to work with Hydrox.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <a class="group rounded-[2rem] border border-slate-200 bg-white p-7 shadow-[0_18px_60px_rgba(15,37,62,0.08)] transition hover:-translate-y-1 hover:shadow-[0_24px_70px_rgba(15,37,62,0.12)]" href="{{ route('staff-portal.login') }}">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#e9f8fb] text-[#0082c9]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 7a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                    <h3 class="mt-5 text-xl font-black tracking-tight">Existing subcontractors</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Submit work logs or update your company and compliance details.</p>
                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-black text-[#0073b4]">Open team portal <span class="transition group-hover:translate-x-1">→</span></span>
                </a>

                <a class="group rounded-[2rem] border border-slate-200 bg-white p-7 shadow-[0_18px_60px_rgba(15,37,62,0.08)] transition hover:-translate-y-1 hover:shadow-[0_24px_70px_rgba(15,37,62,0.12)]" href="{{ route('subcontractor-onboardings.create') }}">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#e8fbf4] text-[#079c70]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M8 4h8M9 2h6v4H9zM7 4H5a2 2 0 0 0-2 2v15h12l6-6V6a2 2 0 0 0-2-2h-2M7 10h10M7 14h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    <h3 class="mt-5 text-xl font-black tracking-tight">Work with Hydrox</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Send your business, insurance and availability details securely.</p>
                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-black text-[#079c70]">Start application <span class="transition group-hover:translate-x-1">→</span></span>
                </a>
            </div>

            <div class="mt-6 grid overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_60px_rgba(15,37,62,0.06)] md:grid-cols-[1fr_auto] md:items-center">
                <div class="flex items-center gap-4 p-6">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-[#e9f8fb] text-[#0082c9]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Zm3 5h6m-6 4h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    <div>
                        <h3 class="font-black text-[#07142e]">Need the monthly work log template?</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $invoiceTemplate ? 'Download the current approved Hydrox template before completing your claim.' : 'A new template will be available here shortly.' }}</p>
                    </div>
                </div>
                @if ($invoiceTemplate)
                    <a class="m-4 inline-flex items-center justify-center rounded-full bg-[#07142e] px-6 py-3 text-sm font-black text-white transition hover:bg-[#0082c9]" href="{{ route('invoice-template.download') }}">Download template</a>
                @else
                    <span class="m-4 inline-flex items-center justify-center rounded-full bg-slate-100 px-6 py-3 text-sm font-bold text-slate-400">Coming soon</span>
                @endif
            </div>
        </section>

        <footer class="border-t border-slate-200 bg-white/70">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-12">
                <p>&copy; {{ now()->year }} Hydrox Facility Management. All rights reserved.</p>
                <div class="flex items-center gap-5">
                    <a class="font-bold transition hover:text-[#0082c9]" href="tel:0418222477">0418 222 477</a>
                    <a class="font-bold transition hover:text-[#0082c9]" href="https://hydrox.au">hydrox.au</a>
                </div>
            </div>
        </footer>
    </div>

    <div class="{{ $errors->any() ? 'grid' : 'hidden' }} fixed inset-0 z-50 place-items-center bg-[#06142d]/80 px-4 backdrop-blur-md" data-admin-login-modal>
        <div class="relative w-full max-w-md overflow-hidden rounded-[2rem] border border-white/20 bg-white shadow-2xl shadow-black/30" data-admin-login-panel>
            <div class="h-2 bg-gradient-to-r from-[#0082c9] to-[#11d394]"></div>
            <div class="p-7 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-[#0082c9]">Booking management</p>
                        <h2 class="mt-2 text-3xl font-black tracking-tight text-[#07142e]">Portal sign in</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Sign in to manage Hydrox bookings and operations.</p>
                    </div>
                    <button type="button" class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-xl text-slate-500 transition hover:bg-slate-200 hover:text-slate-900" data-admin-login-close aria-label="Close">&times;</button>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="mt-7 grid gap-5" autocomplete="off">
                    @csrf
                    <x-field label="Email address" name="email">
                        <input class="input rounded-xl" type="email" name="email" value="{{ old('email') }}" autocomplete="off" data-lpignore="true" data-1p-ignore required>
                    </x-field>
                    <x-field label="Password" name="password">
                        <input class="input rounded-xl" type="password" name="password" autocomplete="new-password" data-lpignore="true" data-1p-ignore required>
                    </x-field>
                    <label class="flex items-center gap-2.5 text-sm font-semibold text-slate-600"><input class="rounded border-slate-300 text-[#0082c9] focus:ring-[#0082c9]" type="checkbox" name="remember"> Keep me signed in</label>
                    <button class="inline-flex w-full items-center justify-center gap-3 rounded-xl bg-gradient-to-r from-[#0082c9] to-[#11b985] px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-cyan-900/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                        Sign in securely
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if (session('onboarding_success'))
        <div class="fixed inset-0 z-50 grid place-items-center bg-[#06142d]/80 px-4 backdrop-blur-md" data-onboarding-success-modal>
            <div class="w-full max-w-lg rounded-[2rem] bg-white p-8 text-center shadow-2xl" data-onboarding-success-panel>
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-[#e7fbf4] text-[#0aa276]"><svg class="h-9 w-9" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                <p class="mt-5 text-xs font-black uppercase tracking-[0.18em] text-[#0082c9]">Application received</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-[#07142e]">Thank you for applying</h2>
                <p class="mt-4 leading-7 text-slate-500">The Hydrox team will review your information and contact you with the next steps.</p>
                <button type="button" class="mt-6 rounded-full bg-[#07142e] px-7 py-3 text-sm font-black text-white" data-onboarding-success-close>Close</button>
            </div>
        </div>
    @endif

    <script>
        (() => {
            const modal = document.querySelector('[data-admin-login-modal]');
            const panel = document.querySelector('[data-admin-login-panel]');
            const openButtons = document.querySelectorAll('[data-admin-login-open]');
            const closeButton = document.querySelector('[data-admin-login-close]');
            if (!modal || !panel || !openButtons.length || !closeButton) return;

            const open = () => {
                modal.classList.remove('hidden');
                modal.classList.add('grid');
                modal.querySelector('input[name="email"]')?.focus();
            };
            const close = () => {
                modal.classList.add('hidden');
                modal.classList.remove('grid');
            };

            openButtons.forEach((button) => button.addEventListener('click', open));
            closeButton.addEventListener('click', close);
            modal.addEventListener('click', (event) => {
                if (!panel.contains(event.target)) close();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') close();
            });
        })();

        (() => {
            const modal = document.querySelector('[data-onboarding-success-modal]');
            const panel = document.querySelector('[data-onboarding-success-panel]');
            const closeButton = document.querySelector('[data-onboarding-success-close]');
            if (!modal || !panel || !closeButton) return;
            const close = () => modal.remove();
            closeButton.addEventListener('click', close);
            modal.addEventListener('click', (event) => {
                if (!panel.contains(event.target)) close();
            });
        })();
    </script>
@endsection
