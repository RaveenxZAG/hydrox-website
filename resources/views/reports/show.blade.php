@extends('layouts.app')
@section('title', 'Hydrox Facility Management Quality Service Completion Report')
@section('actions')
    <a class="btn-secondary" href="{{ route('reports.edit', $report) }}">Edit</a>
    <a class="btn-primary" href="{{ route('reports.download', $report) }}">Download PDF</a>
@endsection
@section('content')
    @php
        $job = $report->job;
        $customer = $job?->customer;
        $address = $customer ? collect([$customer->address, $customer->suburb, $customer->state, $customer->postcode])->filter()->join(', ') : '';
    @endphp

    <div class="grid gap-6 xl:grid-cols-3">
        <x-card class="xl:col-span-2">
            <h2 class="text-lg font-bold">{{ $customer?->customer_name ?: 'Missing client' }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $report->report_number }} · {{ $job?->cleaning_service ?: 'Missing service' }} · {{ $job?->job_number ?: 'Missing job' }}</p>
            <div class="mt-5 grid gap-4 text-sm sm:grid-cols-3">
                <div><dt class="text-slate-500">Completed</dt><dd>{{ $report->completion_date?->format('d M Y') }}</dd></div>
                <div><dt class="text-slate-500">Prepared By</dt><dd>{{ $report->technician }}</dd></div>
                <div><dt class="text-slate-500">Condition</dt><dd>{{ $report->overall_condition }}</dd></div>
                <div><dt class="text-slate-500">Client Present</dt><dd>{{ $report->customer_present ? 'Yes' : 'No' }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd>{{ $report->status }}</dd></div>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div><h3 class="font-semibold">Work Summary</h3><p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $report->work_summary ?: 'Not recorded.' }}</p></div>
                <div><h3 class="font-semibold">Recommendations</h3><p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $report->recommendations ?: 'Not recorded.' }}</p></div>
            </div>
        </x-card>
        <x-card>
            <h2 class="font-bold">Checklist</h2>
            <div class="mt-4 grid gap-2 text-sm">
                @forelse ($report->checklist ?? [] as $item)
                    <span class="rounded-lg bg-[#eaf6fc] px-3 py-2 text-[#07527d]">{{ $item }}</span>
                @empty
                    <span class="text-sm text-slate-500">No checklist items selected.</span>
                @endforelse
            </div>
        </x-card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-card class="xl:col-span-2">
            <h2 class="text-lg font-bold">Client and Service Information</h2>
            <dl class="mt-5 grid gap-4 text-sm md:grid-cols-2">
                <div><dt class="text-slate-500">Client</dt><dd>{{ $customer?->customer_name ?: 'Missing client' }}</dd></div>
                <div><dt class="text-slate-500">Company</dt><dd>{{ $customer?->company ?: 'N/A' }}</dd></div>
                <div><dt class="text-slate-500">Phone</dt><dd>{{ $customer?->phone ?: 'N/A' }}</dd></div>
                <div><dt class="text-slate-500">Email</dt><dd>{{ $customer?->email ?: 'N/A' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-slate-500">Address</dt><dd>{{ $address ?: 'N/A' }}</dd></div>
                <div><dt class="text-slate-500">Service</dt><dd>{{ $job?->cleaning_service ?: 'Missing service' }}</dd></div>
                <div><dt class="text-slate-500">Job Date</dt><dd>{{ $job?->booking_date?->format('d M Y') ?: 'N/A' }}</dd></div>
                <div><dt class="text-slate-500">Start Time</dt><dd>{{ $job?->start_time ?: 'N/A' }}</dd></div>
                <div><dt class="text-slate-500">Finish Time</dt><dd>{{ $job?->finish_time ?: 'N/A' }}</dd></div>
            </dl>
        </x-card>

        <x-card>
            <h2 class="text-lg font-bold">Declaration</h2>
            <dl class="mt-5 grid gap-4 text-sm">
                <div><dt class="text-slate-500">Prepared By</dt><dd>{{ $report->technician_name ?: $report->technician }}</dd></div>
                <div><dt class="text-slate-500">Signed Date</dt><dd>{{ $report->technician_signed_at?->format('d M Y H:i') ?: $report->completion_date?->format('d M Y') }}</dd></div>
            </dl>
            @if ($report->technician_signature_path)
                <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-2">
                    <img class="h-28 w-full rounded-md object-contain" src="{{ route('media.show', ['path' => $report->technician_signature_path]) }}" alt="Prepared by signature">
                </div>
            @endif
        </x-card>
    </div>

    @if ($report->issues->isNotEmpty())
        <div class="mt-6 grid gap-4">
            <h2 class="text-lg font-bold">Issues Found</h2>
            @foreach ($report->issues as $issue)
                <x-card>
                    <dl class="grid gap-4 text-sm md:grid-cols-3">
                        <div><dt class="text-slate-500">Type</dt><dd>{{ $issue->issue_type }}</dd></div>
                        <div><dt class="text-slate-500">Severity</dt><dd>{{ $issue->severity }}</dd></div>
                        <div><dt class="text-slate-500">Recommendation</dt><dd>{{ $issue->recommendation ?: 'N/A' }}</dd></div>
                        <div class="md:col-span-3"><dt class="text-slate-500">Description</dt><dd class="whitespace-pre-line">{{ $issue->description ?: 'N/A' }}</dd></div>
                    </dl>
                    @if ($issue->photos->isNotEmpty())
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($issue->photos as $photo)
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-2">
                                    <img class="h-32 w-full rounded-md object-contain" src="{{ route('media.show', ['path' => $photo->path]) }}" alt="{{ $issue->issue_type }} issue photo">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-card>
            @endforeach
        </div>
    @endif

    <div class="mt-6 grid gap-6">
        <h2 class="text-lg font-bold">Cleaning Areas</h2>
        @foreach ($report->areas as $area)
            <x-card class="overflow-hidden">
                <div class="mb-4">
                    <h2 class="text-lg font-bold">{{ $area->area_name }}</h2>
                    <p class="break-words text-sm text-slate-500">{{ $area->description }}</p>
                </div>
                <div class="grid gap-5 lg:grid-cols-2">
                    @foreach ([['Before', $area->beforePhotos], ['After', $area->afterPhotos]] as [$label, $photos])
                        <div>
                            <h3 class="mb-3 font-semibold">{{ $label }}</h3>
                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @forelse ($photos as $photo)
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2">
                                        <img class="h-36 w-full rounded-md object-contain" src="{{ route('media.show', ['path' => $photo->path]) }}" alt="{{ $area->area_name }} {{ strtolower($label) }} photo">
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">No {{ strtolower($label) }} photos.</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
                @if ($area->photo_notes)
                    <p class="mt-4 break-words whitespace-pre-line text-sm text-slate-600"><strong>Photo Notes:</strong> {{ $area->photo_notes }}</p>
                @endif
                @if ($area->video_url)
                    <a class="mt-4 inline-flex rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-[#006da9] hover:bg-[#eaf6fc]" href="{{ $area->video_url }}" target="_blank" rel="noopener">
                        Open Videos
                    </a>
                @endif
                <p class="mt-4 break-words whitespace-pre-line text-sm text-slate-600"><strong>Completion Notes:</strong> {{ $area->completion_notes ?: 'Not recorded.' }}</p>
            </x-card>
        @endforeach
    </div>

    @if ($report->internal_notes)
        <x-card class="mt-6">
            <h2 class="text-lg font-bold">Internal Notes</h2>
            <p class="mt-4 whitespace-pre-line text-sm text-slate-600">{{ $report->internal_notes }}</p>
        </x-card>
    @endif
@endsection
