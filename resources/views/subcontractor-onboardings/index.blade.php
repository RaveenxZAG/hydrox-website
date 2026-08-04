@extends('layouts.app')
@section('title', 'Subcontractor Onboarding')
@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="py-3 pr-4">Business</th>
                        <th class="py-3 pr-4">Contact</th>
                        <th class="py-3 pr-4">Email</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 pr-4">Submitted</th>
                        <th class="py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($onboardings as $onboarding)
                        <tr>
                            <td class="py-3 pr-4 font-semibold">{{ $onboarding->legal_business_name ?: $onboarding->full_name }}</td>
                            <td class="py-3 pr-4">{{ $onboarding->contact_person ?: $onboarding->full_name }}</td>
                            <td class="py-3 pr-4">{{ $onboarding->email }}</td>
                            <td class="py-3 pr-4"><span class="badge {{ $onboarding->statusBadgeClass() }}">{{ $onboarding->status }}</span></td>
                            <td class="py-3 pr-4">{{ $onboarding->submitted_at?->format('d M Y') ?: $onboarding->created_at->format('d M Y') }}</td>
                            <td class="py-3 text-right"><a class="font-semibold text-[#006da9]" href="{{ route('subcontractor-onboardings.show', $onboarding) }}">Review</a></td>
                        </tr>
                    @empty
                        <tr><td class="py-6 text-slate-500" colspan="6">No subcontractor onboardings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $onboardings->links() }}</div>
    </x-card>
@endsection
