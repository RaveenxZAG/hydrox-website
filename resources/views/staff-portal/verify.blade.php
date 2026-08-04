@extends('layouts.auth')
@section('hide-error-banner', true)
@section('content')
    <section class="mx-auto max-w-lg rounded-2xl border border-slate-200 bg-white p-7 shadow-xl">
        <div class="mb-6 text-center">
            <img class="mx-auto h-14 w-44 object-contain" src="{{ asset('images/hydrox-logo.svg') }}" alt="Hydrox Facility Management">
            <h1 class="mt-4 text-2xl font-black">Enter Verification Code</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">The code expires after 5 minutes. If it does not work, request a new code.</p>
            @if (app()->environment('local') && session('staff_otp_test_code'))
                <p class="mt-4 rounded-xl border border-[#b8dff3] bg-[#eaf6fc] px-4 py-3 text-sm font-bold text-[#07527d]">
                    Local test code: {{ session('staff_otp_test_code') }}
                </p>
            @endif
        </div>
        <form method="POST" action="{{ route('staff-portal.check-code') }}" class="grid gap-4" data-no-draft="true" data-submitting-text="Verifying...">
            @csrf
            <x-field label="Verification Code" name="code">
                <input class="input text-center text-2xl font-black tracking-[0.4em] @error('code') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" data-draft-skip="true" required autofocus>
            </x-field>
            <button class="btn-primary w-full">Verify & Continue</button>
        </form>
        <div class="mt-5 flex flex-wrap items-center justify-center gap-3 text-sm">
            <a class="font-semibold text-[#0082c9]" href="{{ route('staff-portal.login', ['action' => session('staff_portal_action', 'invoice')]) }}">Request a new code</a>
            <span class="text-slate-300">·</span>
            <a class="font-semibold text-slate-500" href="{{ route('login') }}">Back to Home</a>
        </div>
    </section>
@endsection
