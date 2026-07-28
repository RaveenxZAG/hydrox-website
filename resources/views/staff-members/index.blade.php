@extends('layouts.app')
@section('title', 'Subcontractors')
@section('content')
    <x-card>
        <div class="mb-5">
            <h2 class="text-lg font-bold">Subcontractors</h2>
            <p class="mt-1 text-sm text-slate-500">Approved Hydrox subcontractors appear here automatically and can be maintained directly in this portal.</p>
        </div>

        <form method="GET" class="mb-5 grid gap-3 md:grid-cols-[1fr_180px_auto]">
            <input class="input" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, email, mobile, or role">
            <select class="input" name="status">
                <option value="">All subcontractor statuses</option>
                @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'archived' => 'Archived'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn-primary">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-800">
                    <tr>
                        <th class="py-3 pr-4">Name</th>
                        <th class="py-3 pr-4">Email</th>
                        <th class="py-3 pr-4">Mobile</th>
                        <th class="py-3 pr-4">Security Role</th>
                        <th class="py-3 pr-4">Invoicing</th>
                        <th class="py-3 pr-4">Missing Info</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($staffMembers as $staff)
                        <tr>
                            <td class="py-3 pr-4 font-semibold">{{ $staff->fullName() }}</td>
                            <td class="py-3 pr-4">{{ $staff->email ?: 'Not recorded' }}</td>
                            <td class="py-3 pr-4">{{ $staff->mobile ?: 'Not recorded' }}</td>
                            <td class="py-3 pr-4">{{ $staff->securityRoleName($roleNames) }}</td>
                            <td class="py-3 pr-4">
                                @if ($staff->invoicing_enabled)
                                    <span class="badge bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">Enabled</span>
                                @else
                                    <span class="badge bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">Disabled</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                @if ($staff->missingInfoCount() === 0)
                                    <span class="badge bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">Complete</span>
                                @else
                                    <span class="badge bg-rose-50 text-rose-800 dark:bg-rose-950 dark:text-rose-200">{{ $staff->missingInfoCount() }} missing</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                <span class="badge {{ $staff->staff_status === 'active' ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' }}">{{ ucfirst($staff->staff_status ?: 'active') }}</span>
                            </td>
                            <td class="py-3 text-right">
                                <a class="font-semibold text-cyan-700 dark:text-cyan-300" href="{{ route('staff-members.show', $staff) }}">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-6 text-slate-500" colspan="8">No subcontractors found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $staffMembers->links() }}</div>
    </x-card>
@endsection
