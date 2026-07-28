<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffInvoiceWorkLog extends Model
{
    protected $fillable = [
        'staff_invoice_submission_id',
        'staff_member_id',
        'invoice_site_id',
        'invoice_site_shift_id',
        'work_date',
        'site_name',
        'shift_label',
        'service_m8_job_code',
        'work_type',
        'hours',
        'amount',
        'notes',
        'row_number',
        'status',
        'flag_reason',
        'approved_amount',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'hours' => 'decimal:2',
            'amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    protected function siteName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value === null
                ? null
                : preg_replace('/^ServiceM8\s+Job\s+Job(?=\s*#)/i', 'Work Reference', $value),
        );
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StaffInvoiceSubmission::class, 'staff_invoice_submission_id');
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(InvoiceSite::class, 'invoice_site_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(InvoiceSiteShift::class, 'invoice_site_shift_id');
    }

    public function isFlagged(): bool
    {
        return $this->status === 'flagged';
    }
}
