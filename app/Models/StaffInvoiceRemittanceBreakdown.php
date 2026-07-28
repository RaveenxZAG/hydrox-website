<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffInvoiceRemittanceBreakdown extends Model
{
    protected $fillable = [
        'staff_invoice_submission_id',
        'staff_member_id',
        'approved_total',
        'fuel_percentage',
        'fuel_amount',
        'tools_materials_percentage',
        'tools_materials_amount',
        'tools_split_percentage',
        'tools_amount',
        'materials_split_percentage',
        'materials_amount',
        'labour_percentage',
        'labour_amount',
        'generated_at',
        'generation_status',
    ];

    protected function casts(): array
    {
        return [
            'approved_total' => 'decimal:2',
            'fuel_percentage' => 'decimal:4',
            'fuel_amount' => 'decimal:2',
            'tools_materials_percentage' => 'decimal:4',
            'tools_materials_amount' => 'decimal:2',
            'tools_split_percentage' => 'decimal:4',
            'tools_amount' => 'decimal:2',
            'materials_split_percentage' => 'decimal:4',
            'materials_amount' => 'decimal:2',
            'labour_percentage' => 'decimal:4',
            'labour_amount' => 'decimal:2',
            'generated_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StaffInvoiceSubmission::class, 'staff_invoice_submission_id');
    }
}
