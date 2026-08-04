<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubcontractorDocumentVersion extends Model
{
    public const REVIEW_STATUSES = ['Pending Review', 'Approved', 'Rejected', 'Expired', 'Replacement Required'];

    protected $fillable = [
        'subcontractor_document_id', 'version_number', 'original_filename', 'storage_disk', 'storage_path',
        'mime_type', 'file_size', 'expiry_date', 'review_status', 'admin_notes', 'uploaded_by',
        'reviewed_by', 'reviewed_at', 'replacement_reason', 'is_current', 'archived_at',
    ];

    protected function casts(): array
    {
        return ['expiry_date' => 'date', 'reviewed_at' => 'datetime', 'archived_at' => 'datetime', 'is_current' => 'boolean'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(SubcontractorDocument::class, 'subcontractor_document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
