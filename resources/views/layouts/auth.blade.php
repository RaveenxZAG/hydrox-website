<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=hydrox-1">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}?v=hydrox-1">
    <title>{{ config('app.name', 'Hydrox Portal') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $fullWidthAuthPage = View::hasSection('auth-full-width');
@endphp
<body class="{{ $fullWidthAuthPage ? 'min-h-screen bg-[#F8FAFC] text-slate-950' : 'min-h-screen bg-slate-100 text-slate-950' }}">
    @php
        $flashMessages = collect([
            session('status') ? ['type' => 'success', 'message' => session('status')] : null,
            session('error') ? ['type' => 'error', 'message' => session('error')] : null,
            $errors->any() && ! View::hasSection('hide-error-banner') ? ['type' => 'warning', 'message' => 'Please check the highlighted fields and try again.'] : null,
        ])->filter();
    @endphp

    <main class="{{ $fullWidthAuthPage ? 'min-h-screen' : 'min-h-screen px-4 py-8 sm:px-6 lg:px-8' }}">
        @if ($flashMessages->isNotEmpty())
            <div class="{{ $fullWidthAuthPage ? 'pointer-events-none fixed inset-x-0 top-4 z-50 mx-auto grid max-w-xl gap-3 px-4 sm:px-6 lg:px-8' : 'mx-auto mb-5 grid max-w-lg gap-3' }}">
                @foreach ($flashMessages as $flash)
                    @php
                        $flashStyles = match ($flash['type']) {
                            'success' => [
                                'title' => 'Success',
                                'icon' => 'M20 6 9 17l-5-5',
                                'outer' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
                                'iconWrap' => 'bg-emerald-100 text-emerald-700',
                            ],
                            'warning' => [
                                'title' => 'Check details',
                                'icon' => 'M12 8v4m0 4h.01M10.3 3.9 2.5 17.4A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.6L13.7 3.9a2 2 0 0 0-3.4 0Z',
                                'outer' => 'border-amber-200 bg-amber-50 text-amber-900',
                                'iconWrap' => 'bg-amber-100 text-amber-700',
                            ],
                            default => [
                                'title' => 'Something went wrong',
                                'icon' => 'M18 6 6 18M6 6l12 12',
                                'outer' => 'border-rose-200 bg-rose-50 text-rose-900',
                                'iconWrap' => 'bg-rose-100 text-rose-700',
                            ],
                        };
                    @endphp
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        class="rounded-2xl border px-4 py-3 text-sm shadow-sm {{ $fullWidthAuthPage ? 'pointer-events-auto shadow-lg' : '' }} {{ $flashStyles['outer'] }}"
                    >
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-xl {{ $flashStyles['iconWrap'] }}">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="{{ $flashStyles['icon'] }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold">{{ $flashStyles['title'] }}</p>
                                <p class="mt-0.5 leading-5 opacity-90">{{ $flash['message'] }}</p>
                            </div>
                            <button type="button" class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-lg leading-none opacity-60 transition hover:bg-white/60 hover:opacity-100" @click="show = false" aria-label="Close notification">&times;</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
