@extends('layouts.app')

@section('title', 'Dashboard')
@section('actions')
    <a class="btn-secondary" href="{{ route('bookings.index') }}">View Bookings</a>
@endsection

@section('content')
    <div class="hydrox-hero mb-6 rounded-2xl p-7 text-white">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-white/75">Today at a glance</p>
        <h2 class="mt-2 text-3xl font-black tracking-tight">Operations overview</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-white/85">Review new website bookings, manage subcontractors, and keep work logs moving from one secure portal.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            'Processing Requests' => $stats['new_bookings'],
            'Upcoming Bookings' => $stats['upcoming_bookings'],
            'Confirmed Bookings' => $stats['confirmed_bookings'],
            'Active Subcontractors' => $stats['active_subcontractors'],
        ] as $label => $value)
            <x-card>
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-3 text-3xl font-bold">{{ $value }}</p>
            </x-card>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            'Work Log Period' => $invoiceStats['period'],
            'Work Logs Submitted' => $invoiceStats['submitted'],
            'Missing Work Logs' => $invoiceStats['missing'],
        ] as $label => $value)
            <x-card>
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-3 text-2xl font-bold">{{ $value }}</p>
            </x-card>
        @endforeach
    </div>

    <div class="mt-6">
        <x-card>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-bold">Recent Bookings</h2>
                <a class="text-sm font-semibold text-[#0082c9]" href="{{ route('bookings.index') }}">View all</a>
            </div>
            <div class="divide-y divide-slate-200">
                @forelse ($recentBookings as $booking)
                    <a href="{{ route('bookings.show', $booking) }}" class="block py-3 hover:text-[#0082c9]">
                        <div class="flex justify-between gap-4">
                            <span class="font-semibold">{{ $booking->customer_name }} · {{ $booking->service }}</span>
                            <span class="badge bg-slate-100 text-slate-700">{{ str($booking->status)->headline() }}</span>
                        </div>
                        <p class="text-sm text-slate-500">{{ $booking->reference }} · {{ $booking->preferred_date?->format('d M Y') ?: 'Flexible date' }}</p>
                    </a>
                @empty
                    <p class="py-6 text-sm text-slate-500">No website bookings have been received yet.</p>
                @endforelse
            </div>
        </x-card>
    </div>
@endsection
