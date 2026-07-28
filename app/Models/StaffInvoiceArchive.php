<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffInvoiceArchive extends Model
{
    protected $fillable = [
        'staff_invoice_submission_id',
        'version',
        'status',
        'total_amount',
        'original_filename',
        'storage_path',
        'work_logs',
        'reason',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'work_logs' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StaffInvoiceSubmission::class, 'staff_invoice_submission_id');
    }
}
