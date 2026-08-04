@extends('layouts.app')
@section('title', 'Settings')
@section('content')
    <div class="mb-6">
        <p class="text-sm font-semibold uppercase tracking-wide text-[#0082c9]">Work Log Template</p>
        <h2 class="mt-1 text-xl font-bold">Public work log template download</h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-500">Update the file available from the public login page for subcontractors or visitors to download.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
        <x-card>
            <h2 class="mb-4 text-lg font-bold">Current Template</h2>
            @if ($invoiceTemplate)
                <div class="grid gap-3 text-sm">
                    <p><strong>File:</strong> {{ $invoiceTemplate['original_name'] }}</p>
                    <p><strong>Uploaded:</strong> {{ $invoiceTemplate['uploaded_at'] }}</p>
                    <div class="flex flex-wrap gap-3">
                        <a class="btn-primary" href="{{ route('invoice-template.download') }}">Download</a>
                        <form method="POST" action="{{ route('invoice-template.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn-secondary" onclick="return confirm('Remove the work log template?')">Remove Template</button>
                        </form>
                    </div>
                </div>
            @else
                <p class="text-sm text-slate-500">No work log template is currently available for public download.</p>
            @endif
        </x-card>

        <x-card>
            <h2 class="mb-4 text-lg font-bold">Upload Replacement</h2>
            <form method="POST" action="{{ route('invoice-template.update') }}" enctype="multipart/form-data" class="grid gap-4">
                @csrf
                <x-field label="Work Log Template File" name="template">
                    <input class="input file:mr-3 file:rounded-md file:border-0 file:bg-[#eaf6fc] file:px-3 file:py-2 file:text-[#006da9]" type="file" name="template" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv" required>
                </x-field>
                <button class="btn-primary w-fit">Upload Template</button>
            </form>
        </x-card>
    </div>
@endsection
