<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StaffInvoiceSubmission extends Model
{
    protected $fillable = [
        'staff_member_id',
        'invoice_period',
        'version',
        'total_amount',
        'approved_total',
        'invoice_reference',
        'original_filename',
        'storage_path',
        'file_size',
        'mime_type',
        'checksum',
        'status',
        'correction_reason',
        'correction_instructions',
        'correction_due_at',
        'submitted_at',
        'submitted_ip',
        'reviewed_at',
        'ready_for_payment_at',
        'paid_at',
        'payment_reference',
        'remittance_path',
        'remittance_servicem8_attachment_uuid',
    ];

    protected function casts(): array
    {
        return [
            'invoice_period' => 'date',
            'total_amount' => 'decimal:2',
            'approved_total' => 'decimal:2',
            'correction_due_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'ready_for_payment_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function aiReview(): HasOne
    {
        return $this->hasOne(InvoiceAiReview::class);
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(StaffInvoiceWorkLog::class);
    }

    public function archives(): HasMany
    {
        return $this->hasMany(StaffInvoiceArchive::class);
    }

    public function remittanceDeliveries(): HasMany
    {
        return $this->hasMany(StaffInvoiceRemittanceDelivery::class);
    }

    public function remittanceBreakdown(): HasOne
    {
        return $this->hasOne(StaffInvoiceRemittanceBreakdown::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending_review' => 'Pending Review',
            'correction_required' => 'Correction Required',
            'resubmitted' => 'Resubmitted',
            'ready_for_payment' => 'Ready for Payment',
            'paid' => 'Paid',
            'submitted' => 'Submitted',
            default => str((string) $this->status)->headline()->toString(),
        };
    }

    public function storedFilename(): string
    {
        return $this->storage_path ? basename($this->storage_path) : $this->original_filename;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'bg-[#0082c9]/10 text-[#0082c9] ring-1 ring-[#0082c9]/20',
            'ready_for_payment' => 'bg-green-50 text-green-700 ring-1 ring-green-100',
            'correction_required' => 'bg-red-50 text-red-700 ring-1 ring-red-100',
            'resubmitted', 'pending_review' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
            default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
        };
    }
}
