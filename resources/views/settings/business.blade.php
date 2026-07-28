@extends('layouts.app')

@section('title', 'Business Information')

@section('content')
    <form method="POST" action="{{ route('settings.business.update') }}" class="mx-auto grid max-w-6xl gap-5">
        @csrf

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#0082c9]">Settings</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950 dark:text-white">Company profile</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage the company identity, contact details, operating hours, and office address.</p>
            </div>
            <button class="btn-primary">Save Business Information</button>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 sm:p-6">
            <div class="border-b border-slate-100 pb-4 dark:border-slate-800">
                <h2 class="text-lg font-black text-slate-950 dark:text-white">Company Details</h2>
                <p class="mt-1 text-sm text-slate-500">Primary business and accounts contact information.</p>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <x-field label="Company Name" name="company_name" class="md:col-span-2">
                    <input class="input" name="company_name" value="{{ old('company_name', $business['company_name']) }}" required>
                </x-field>

                <x-field label="ABN Number" name="abn">
                    <input class="input" name="abn" value="{{ old('abn', $business['abn']) }}" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" required>
                </x-field>

                <x-field label="Website Address" name="website">
                    <input class="input" type="url" name="website" value="{{ old('website', $business['website']) }}" placeholder="https://example.com">
                </x-field>

                <x-field label="Email Address" name="email">
                    <input class="input" type="email" name="email" value="{{ old('email', $business['email']) }}" required>
                </x-field>

                <x-field label="Accounts / Billing Email Address" name="billing_email">
                    <input class="input" type="email" name="billing_email" value="{{ old('billing_email', $business['billing_email']) }}" required>
                </x-field>

                <x-field label="Phone" name="phone">
                    <input class="input" type="tel" name="phone" value="{{ old('phone', $business['phone']) }}" inputmode="tel">
                </x-field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Primary Mobile" name="mobile_primary">
                        <input class="input" type="tel" name="mobile_primary" value="{{ old('mobile_primary', $business['mobile_primary']) }}" inputmode="tel">
                    </x-field>
                    <x-field label="Secondary Mobile" name="mobile_secondary">
                        <input class="input" type="tel" name="mobile_secondary" value="{{ old('mobile_secondary', $business['mobile_secondary']) }}" inputmode="tel">
                    </x-field>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 sm:p-6">
            <div class="border-b border-slate-100 pb-4 dark:border-slate-800">
                <h2 class="text-lg font-black text-slate-950 dark:text-white">Office Location</h2>
                <p class="mt-1 text-sm text-slate-500">Address shown on business documents and correspondence.</p>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <x-field label="Address Line 1" name="address_line_1">
                    <input class="input" name="address_line_1" value="{{ old('address_line_1', $business['address_line_1']) }}" required>
                </x-field>
                <x-field label="Address Line 2" name="address_line_2">
                    <input class="input" name="address_line_2" value="{{ old('address_line_2', $business['address_line_2']) }}">
                </x-field>
                <x-field label="Address Line 3" name="address_line_3" class="md:col-span-2">
                    <input class="input" name="address_line_3" value="{{ old('address_line_3', $business['address_line_3']) }}">
                </x-field>
                <x-field label="City" name="city">
                    <input class="input" name="city" value="{{ old('city', $business['city']) }}" required>
                </x-field>
                <x-field label="State" name="state">
                    <input class="input" name="state" value="{{ old('state', $business['state']) }}" required>
                </x-field>
                <x-field label="Postcode" name="postcode">
                    <input class="input" name="postcode" value="{{ old('postcode', $business['postcode']) }}" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required>
                </x-field>
                <x-field label="Country" name="country">
                    <input class="input" name="country" value="{{ old('country', $business['country']) }}" required>
                </x-field>
            </div>
        </section>

        <div class="flex justify-end">
            <button class="btn-primary">Save Business Information</button>
        </div>
    </form>
@endsection
