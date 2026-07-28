@extends('layouts.app')

@section('title', $booking->reference)

@section('content')
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#0082c9]">Booking request</p>
            <h1 class="mt-1 text-3xl font-black">{{ $booking->reference }}</h1>
            <p class="mt-2 text-sm text-slate-500">Received {{ $booking->created_at->format('d M Y, g:i a') }} from {{ $booking->source }}.</p>
        </div>
        <a class="btn-secondary" href="{{ route('bookings.index') }}">Back</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <x-card>
            <h2 class="text-lg font-black">Customer and service details</h2>
            <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                @foreach ([
                    'Customer' => $booking->customer_name,
                    'Service' => $booking->service,
                    'Email' => $booking->email,
                    'Phone' => $booking->phone,
                    'Preferred date' => $booking->preferred_date?->format('d M Y') ?: 'Flexible',
                    'Preferred time' => $booking->preferred_time ?: 'Flexible',
                    'Address' => collect([$booking->address, $booking->suburb, $booking->postcode])->filter()->join(', ') ?: 'Not supplied',
                    'Website reference' => $booking->external_reference ?: 'Not supplied',
                ] as $label => $value)
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $label }}</dt>
                        <dd class="mt-1 font-semibold">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>

        <x-card>
            <h2 class="text-lg font-black">Manage booking</h2>
            <form class="mt-5 grid gap-4" method="POST" action="{{ route('bookings.update', $booking) }}">
                @csrf
                @method('PATCH')
                <x-field label="Status" name="status">
                    <select class="input" name="status">
                        @foreach (['new', 'contacted', 'quoted', 'confirmed', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($booking->status === $status)>{{ str($status)->headline() }}</option>
                        @endforeach
                    </select>
                </x-field>
                <x-field label="Internal notes" name="notes">
                    <textarea class="input min-h-36" name="notes">{{ old('notes', $booking->notes) }}</textarea>
                </x-field>
                <button class="btn-primary">Save changes</button>
            </form>
        </x-card>
    </div>
@endsection
