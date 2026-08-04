@extends('layouts.app')
@section('title', 'Completion Reports')
@section('actions')<a class="btn-primary" href="{{ route('reports.create') }}">Job Completion Report</a>@endsection
@section('content')
    <x-card>
        <form class="mb-4 grid gap-2 lg:grid-cols-[1fr_180px_180px_auto]">
            <input class="input" name="search" value="{{ request('search') }}" placeholder="Search report, job, client">
            <input class="input" type="date" name="date" value="{{ request('date') }}">
            <select class="input" name="status"><option value="">All statuses</option>@foreach(['Draft','Generated'] as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select>
            <button class="btn-secondary">Filter</button>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-500"><tr><th class="py-3">Report</th><th>Client</th><th>Job</th><th>Date</th><th>Status</th><th></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($reports as $report)
                        <tr>
                            <td class="py-3 font-semibold">{{ $report->report_number }}</td>
                            <td>{{ $report->job?->customer?->customer_name ?: 'Missing client' }}</td>
                            <td>{{ $report->job?->job_number ?: 'Missing job' }}</td>
                            <td>{{ $report->completion_date?->format('d M Y') }}</td>
                            <td><span class="badge bg-[#eaf6fc] text-[#006da9]">{{ $report->status }}</span></td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a class="font-semibold text-[#006da9]" href="{{ route('reports.show', $report) }}">View</a>
                                    <form method="POST" action="{{ route('reports.destroy', $report) }}" onsubmit="return confirm('Delete report {{ addslashes($report->report_number) }}?') && confirm('Please confirm again. This delete action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-rose-600 hover:text-rose-700">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $reports->links() }}</div>
    </x-card>
@endsection
