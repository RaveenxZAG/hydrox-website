@extends('layouts.app')

@section('title', 'Bookings')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#0082c9]">Website booking requests</p>
            <p class="mt-2 text-sm text-slate-500">New bookings submitted from hydrox.au will appear here for review.</p>
        </div>
        <form class="grid gap-3 sm:grid-cols-[minmax(220px,1fr)_180px_auto]">
            <input class="input" name="search" value="{{ request('search') }}" placeholder="Search bookings">
            <select class="input" name="status">
                <option value="">All statuses</option>
                @foreach (['new', 'contacted', 'quoted', 'confirmed', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->headline() }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Filter</button>
        </form>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3">Preferred date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-900">
                            <td class="whitespace-nowrap px-4 py-4 font-bold text-[#0082c9]">{{ $booking->reference }}</td>
                            <td class="px-4 py-4">
                                <p class="font-semibold">{{ $booking->customer_name }}</p>
                                <p class="text-xs text-slate-500">{{ $booking->phone }} · {{ $booking->email }}</p>
                            </td>
                            <td class="px-4 py-4">{{ $booking->service }}</td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $booking->preferred_date?->format('d M Y') ?: 'Flexible' }}</td>
                            <td class="px-4 py-4"><span class="badge bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ str($booking->status)->headline() }}</span></td>
                            <td class="px-4 py-4 text-right"><a class="font-bold text-[#0082c9]" href="{{ route('bookings.show', $booking) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-12 text-center text-slate-500" colspan="6">No website bookings have been received yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $bookings->links() }}</div>
    </x-card>
@endsection
