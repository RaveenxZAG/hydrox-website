<?php

namespace App\Http\Controllers;

use App\Models\SubcontractorDocumentVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubcontractorDocumentVersionController extends Controller
{
    public function show(SubcontractorDocumentVersion $version): BinaryFileResponse
    {
        abort_unless(Storage::disk($version->storage_disk)->exists($version->storage_path), 404);

        return response()->file(Storage::disk($version->storage_disk)->path($version->storage_path));
    }

    public function download(SubcontractorDocumentVersion $version): StreamedResponse
    {
        abort_unless(Storage::disk($version->storage_disk)->exists($version->storage_path), 404);

        return Storage::disk($version->storage_disk)->download($version->storage_path, $version->original_filename);
    }

    public function update(Request $request, SubcontractorDocumentVersion $version): RedirectResponse
    {
        $data = $request->validate([
            'review_status' => ['required', 'in:'.implode(',', SubcontractorDocumentVersion::REVIEW_STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'expiry_date' => ['nullable', 'date'],
        ]);
        $version->update($data + ['reviewed_by' => Auth::id(), 'reviewed_at' => now()]);

        return back()->with('status', 'Document review updated.');
    }

    public function archive(SubcontractorDocumentVersion $version): RedirectResponse
    {
        abort_unless($version->is_current && ! $version->archived_at, 404);
        $version->update(['is_current' => false, 'archived_at' => now()]);

        return back()->with('status', 'Document archived. Its file remains available in history.');
    }
}
