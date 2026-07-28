<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAiReview extends Model
{
    protected $fillable = [
        'staff_invoice_submission_id',
        'monthly_xero_report_id',
        'provider',
        'status',
        'confidence',
        'summary',
        'response',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:2',
            'response' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StaffInvoiceSubmission::class, 'staff_invoice_submission_id');
    }

    public function xeroReport(): BelongsTo
    {
        return $this->belongsTo(MonthlyXeroReport::class, 'monthly_xero_report_id');
    }
}
