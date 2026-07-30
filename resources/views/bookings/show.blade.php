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
        <div class="grid gap-6">
        <x-card>
            <h2 class="text-lg font-black">Customer and service details</h2>
            <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                @foreach ([
                    'Customer' => $booking->customer_name,
                    'Services' => collect($booking->services ?: [$booking->service])->join(', '),
                    'Extras' => collect($booking->extras)->join(', ') ?: 'None',
                    'Frequency' => str($booking->frequency ?: 'Not supplied')->replace('-', ' ')->title(),
                    'Email' => $booking->email,
                    'Phone' => $booking->phone,
                    'Preferred date' => $booking->schedule_flexible ? 'Flexible' : ($booking->preferred_date?->format('d M Y') ?: 'Not supplied'),
                    'Preferred time' => $booking->schedule_flexible ? 'Flexible' : ($booking->preferred_time ?: 'Not supplied'),
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
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black">Customer photos</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $booking->photos->count() }} of 20 photos supplied.</p>
                </div>
            </div>
            @if ($booking->photos->isNotEmpty())
                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($booking->photos as $photo)
                        <a class="group overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900"
                           href="{{ route('bookings.photos.show', [$booking, $photo]) }}" target="_blank" rel="noopener">
                            @if (in_array($photo->mime_type, ['image/heic', 'image/heif'], true))
                                <div class="grid aspect-square place-items-center p-4 text-center text-xs font-bold text-slate-500">Open phone photo</div>
                            @else
                                <img class="aspect-square w-full object-cover transition group-hover:scale-105"
                                     src="{{ route('bookings.photos.show', [$booking, $photo]) }}"
                                     alt="Booking photo {{ $loop->iteration }}">
                            @endif
                            <p class="truncate px-3 py-2 text-xs text-slate-500">{{ $photo->original_name }}</p>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="mt-5 text-sm text-slate-500">No photos were supplied with this request.</p>
            @endif
        </x-card>

        @if ($booking->notes)
            <x-card>
                <h2 class="text-lg font-black">Customer notes</h2>
                <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $booking->notes }}</p>
            </x-card>
        @endif
        </div>

        <x-card>
            <h2 class="text-lg font-black">Manage booking</h2>
            <form class="mt-5 grid gap-4" method="POST" action="{{ route('bookings.update', $booking) }}">
                @csrf
                @method('PATCH')
                <x-field label="Status" name="status">
                    <select class="input" name="status">
                        @foreach (['processing', 'contacted', 'quoted', 'confirmed', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($booking->status === $status)>{{ str($status)->headline() }}</option>
                        @endforeach
                    </select>
                </x-field>
                <x-field label="Internal notes" name="notes">
                    <textarea class="input min-h-36" name="notes">{{ old('notes', $booking->notes) }}</textarea>
                </x-field>
                <button class="btn-primary">Save changes</button>
            </form>

            <div class="mt-6 border-t border-slate-200 pt-5 dark:border-slate-800">
                <h3 class="text-sm font-black">Email delivery</h3>
                <dl class="mt-3 grid gap-2 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Customer receipt</dt><dd class="font-semibold">{{ $booking->customer_email_sent_at ? 'Sent '.$booking->customer_email_sent_at->format('d M, g:i a') : 'Not sent' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Hydrox alert</dt><dd class="font-semibold">{{ $booking->admin_email_sent_at ? 'Sent '.$booking->admin_email_sent_at->format('d M, g:i a') : 'Not sent' }}</dd></div>
                </dl>
                @if ($booking->email_error)
                    <p class="mt-3 rounded-xl bg-rose-50 p-3 text-xs leading-5 text-rose-700">{{ $booking->email_error }}</p>
                @endif
                <form class="mt-4" method="POST" action="{{ route('bookings.emails.resend', $booking) }}">
                    @csrf
                    <button class="btn-secondary w-full">Resend booking emails</button>
                </form>
            </div>
        </x-card>
    </div>
@endsection
