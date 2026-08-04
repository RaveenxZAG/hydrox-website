@extends('layouts.app')
@section('title', 'Profile Updates')
@section('content')
    <x-card>
        @php
            $formatField = fn (string $field): string => [
                'abn' => 'ABN',
                'gst_registered' => 'GST Registered',
                'skills' => 'Cleaning Skills',
                'experience' => 'Previous Experience',
                'public_liability_insurance' => 'Public Liability Insurance',
                'workers_compensation_insurance' => 'Victorian Working with Children Check',
                'police_clearance' => 'Police Clearance',
                'driver_licence' => 'Driver Licence',
                'working_rights' => 'Working Rights / VISA document',
            ][$field] ?? str($field)->headline();

            $booleanFields = ['gst_registered'];

            $formatValue = function ($value, ?string $field = null) use ($booleanFields): string {
                if (in_array($field, $booleanFields, true)) {
                    return (bool) $value ? 'Yes' : 'No';
                }

                if (is_bool($value)) {
                    return $value ? 'Yes' : 'No';
                }

                if (is_array($value)) {
                    if (array_is_list($value)) {
                        return $value === [] ? 'Missing' : implode(', ', $value);
                    }

                    return $value['original_name'] ?? 'Uploaded file';
                }

                return filled($value) ? (string) $value : 'Missing';
            };

            $valuesMatch = function ($currentValue, $requestedValue, string $field) use ($booleanFields): bool {
                if (is_array($requestedValue) && array_key_exists('path', $requestedValue)) {
                    return false;
                }

                if (in_array($field, $booleanFields, true)) {
                    return (bool) $currentValue === (bool) $requestedValue;
                }

                if (is_array($currentValue) || is_array($requestedValue)) {
                    $currentList = collect(is_array($currentValue) ? $currentValue : [$currentValue])
                        ->filter(fn ($value) => filled($value))
                        ->map(fn ($value) => trim((string) $value))
                        ->sort()
                        ->values()
                        ->all();

                    $requestedList = collect(is_array($requestedValue) ? $requestedValue : [$requestedValue])
                        ->filter(fn ($value) => filled($value))
                        ->map(fn ($value) => trim((string) $value))
                        ->sort()
                        ->values()
                        ->all();

                    return $currentList === $requestedList;
                }

                return trim((string) ($currentValue ?? '')) === trim((string) ($requestedValue ?? ''));
            };
        @endphp
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="py-3 pr-4">Subcontractor</th>
                        <th class="py-3 pr-4">Requested Changes</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($requests as $request)
                        @php
                            $staff = $request->staffMember;
                            $documentFields = $staff?->documentFields() ?? [];
                            $displayChanges = collect($request->changes ?? [])
                                ->filter(function ($requestedValue, $field) use ($staff, $documentFields, $valuesMatch) {
                                    if (! $staff) {
                                        return true;
                                    }

                                    $currentValue = array_key_exists($field, $documentFields)
                                        ? $staff->documentName($field)
                                        : $staff->{$field};

                                    return ! $valuesMatch($currentValue, $requestedValue, (string) $field);
                                });
                        @endphp
                        <tr>
                            <td class="py-3 pr-4 font-semibold">{{ $request->staffMember?->fullName() }}</td>
                            <td class="py-3 pr-4">
                                @forelse ($displayChanges as $field => $requestedValue)
                                    @php
                                        $currentValue = array_key_exists($field, $documentFields)
                                            ? $staff?->documentName($field)
                                            : $staff?->{$field};
                                    @endphp
                                    <div class="mb-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                        <p class="font-semibold">{{ $formatField($field) }}</p>
                                        <div class="mt-2 grid gap-2 text-xs text-slate-600 md:grid-cols-2">
                                            <p><span class="font-semibold text-slate-500">Current:</span> {{ $formatValue($currentValue, $field) }}</p>
                                            <div>
                                                <p><span class="font-semibold text-slate-500">Requested:</span> {{ $formatValue($requestedValue, $field) }}</p>
                                                @if (is_array($requestedValue) && filled($requestedValue['path'] ?? null))
                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        <a class="inline-flex items-center rounded-lg border border-[#b8dff3] bg-white px-3 py-1.5 text-xs font-bold text-[#006da9] shadow-sm hover:bg-[#eaf6fc]" href="{{ route('staff-profile-changes.documents.show', [$request, $field]) }}" target="_blank" rel="noopener">
                                                            View document
                                                        </a>
                                                        <a class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50" href="{{ route('staff-profile-changes.documents.download', [$request, $field]) }}">
                                                            Download
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500">
                                        No changed information found in this request.
                                    </div>
                                @endforelse
                            </td>
                            <td class="py-3 pr-4"><span class="badge bg-slate-100 text-slate-700">{{ ucfirst($request->status) }}</span></td>
                            <td class="py-3 text-right">
                                @if ($request->status === 'pending')
                                    <div class="flex justify-end gap-3">
                                        <form method="POST" action="{{ route('staff-profile-changes.approve', $request) }}">@csrf<button class="font-semibold text-emerald-700">Approve</button></form>
                                        <form method="POST" action="{{ route('staff-profile-changes.reject', $request) }}">@csrf<button class="font-semibold text-red-600">Reject</button></form>
                                    </div>
                                @else
                                    <span class="text-slate-400">Reviewed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-slate-500">No subcontractor profile update requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $requests->links() }}</div>
    </x-card>
@endsection
