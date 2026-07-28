<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyInvoiceAiSummary extends Model
{
    protected $fillable = [
        'invoice_month',
        'monthly_xero_report_id',
        'provider',
        'status',
        'total_amount',
        'site_hour_summary',
        'invoice_summaries',
        'summary',
        'mismatch_notes',
        'error_message',
        'response',
        'uploaded_files',
        'started_at',
        'completed_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_month' => 'date',
            'total_amount' => 'decimal:2',
            'site_hour_summary' => 'array',
            'invoice_summaries' => 'array',
            'response' => 'array',
            'uploaded_files' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function xeroReport(): BelongsTo
    {
        return $this->belongsTo(MonthlyXeroReport::class, 'monthly_xero_report_id');
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'completed' => 'bg-emerald-50 text-emerald-800',
            'processing' => 'bg-cyan-50 text-cyan-800',
            'needs_review' => 'bg-amber-50 text-amber-800',
            'failed' => 'bg-rose-50 text-rose-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }
}
