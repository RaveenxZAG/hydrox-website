@extends('layouts.app')
@section('title', 'Settings')
@section('content')
    <div class="mb-6">
        <p class="text-sm font-semibold uppercase tracking-wide text-[#0082c9]">System Maintenance</p>
        <h2 class="mt-1 text-xl font-bold">Cache and temporary data</h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-500">
            Use these tools when the live site shows old pages, old settings, or stuck temporary uploads.
        </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-card>
            <h2 class="mb-3 text-lg font-bold">Clear Cache</h2>
            <p class="text-sm leading-6 text-slate-500">
                Clears Laravel app, view, route, config, and optimization cache. This is safe and does not remove business records.
            </p>
            <form method="POST" action="{{ route('settings.clear-cache') }}" class="mt-5">
                @csrf
                <button class="btn-primary" onclick="return confirm('Clear system cache?')">Clear Cache</button>
            </form>
        </x-card>

        <x-card>
            <h2 class="mb-3 text-lg font-bold">Clear Temporary Data</h2>
            <p class="text-sm leading-6 text-slate-500">
                Clears temporary report uploads and PDF cache files. This will not delete saved reports, report photos, clients, jobs, or subcontractor applications.
            </p>
            <form method="POST" action="{{ route('settings.clear-temporary-data') }}" class="mt-5">
                @csrf
                <button class="btn-secondary" onclick="return confirm('Clear temporary upload and PDF cache data?')">Clear Temporary Data</button>
            </form>
        </x-card>
    </div>
@endsection
