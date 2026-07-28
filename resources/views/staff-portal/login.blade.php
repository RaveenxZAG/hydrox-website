@extends('layouts.auth')
@section('hide-error-banner', true)
@section('content')
    <section class="mx-auto max-w-lg rounded-2xl border border-slate-200 bg-white p-7 shadow-xl">
        <div class="mb-6 text-center">
            <img class="mx-auto h-14 w-44 object-contain" src="{{ asset('images/hydrox-logo.svg') }}" alt="Hydrox Facility Management">
            <h1 class="mt-4 text-2xl font-black">{{ $action === 'profile' ? 'Update Subcontractor Profile' : 'Upload Monthly Work Log' }}</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Enter your registered mobile number or email address to receive a secure verification code.</p>
        </div>
        <form method="POST" action="{{ route('staff-portal.request-code') }}" class="grid gap-4" data-submitting-text="Sending...">
            @csrf
            <x-field label="Mobile Number or Email" name="identifier">
                <input class="input" name="identifier" value="{{ old('identifier') }}" placeholder="0412 345 678 or name@example.com" required>
            </x-field>
            <button class="btn-primary w-full">Send Verification Code</button>
        </form>
        <div class="mt-5 text-center">
            <a class="text-sm font-semibold text-[#0082c9]" href="{{ route('login') }}">Back to Home</a>
        </div>
    </section>
@endsection
