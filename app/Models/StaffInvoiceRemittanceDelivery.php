<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffInvoiceRemittanceDelivery extends Model
{
    protected $fillable = [
        'staff_invoice_submission_id',
        'channel',
        'recipient',
        'status',
        'attempt_type',
        'error_message',
        'attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StaffInvoiceSubmission::class, 'staff_invoice_submission_id');
    }
}
