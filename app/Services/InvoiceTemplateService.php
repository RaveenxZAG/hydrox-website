<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceTemplateService
{
    private const META_PATH = 'invoice-template.json';

    /**
     * @return array{path: string, original_name: string, size: int|null, mime_type: string|null, uploaded_at: string}|null
     */
    public function current(): ?array
    {
        if (! Storage::disk('local')->exists(self::META_PATH)) {
            return null;
        }

        $meta = json_decode(Storage::disk('local')->get(self::META_PATH), true);

        if (! is_array($meta) || empty($meta['path']) || ! Storage::disk('public')->exists($meta['path'])) {
            return null;
        }

        return $meta;
    }

    public function store(UploadedFile $file): array
    {
        $this->delete();

        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $filename = Str::uuid().($extension ? ".{$extension}" : '');
        $path = $file->storeAs('invoice-templates', $filename, 'public');

        $meta = [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'uploaded_at' => now()->toDateTimeString(),
        ];

        Storage::disk('local')->put(self::META_PATH, json_encode($meta, JSON_PRETTY_PRINT));

        return $meta;
    }

    public function delete(): void
    {
        $current = $this->current();

        if ($current) {
            Storage::disk('public')->delete($current['path']);
        }

        Storage::disk('local')->delete(self::META_PATH);
    }
}
