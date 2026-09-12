<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your session has expired</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-950">
    <main class="grid min-h-screen place-items-center px-4 py-10">
        <section class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xl">
            <img class="mx-auto h-14 w-44 object-contain" src="{{ asset('images/hydrox-logo.svg') }}" alt="Hydrox Facility Management">
            <p class="mt-6 text-sm font-bold uppercase tracking-wide text-[#0082c9]">Session expired</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight">Your session has expired</h1>
            <p class="mt-4 text-sm leading-6 text-slate-500">
                For security, this page expired because it was left open for too long. Please restart the verification process.
            </p>
            <div class="mt-7 grid gap-3 sm:grid-cols-2">
                <a class="btn-primary" href="{{ route('home') }}">Return to Homepage</a>
                <a class="btn-secondary" href="{{ route('contact') }}">Contact Support</a>
            </div>
        </section>
    </main>
</body>
</html>
