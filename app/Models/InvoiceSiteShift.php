<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceSiteShift extends Model
{
    protected $fillable = [
        'invoice_site_id',
        'weekday',
        'label',
        'contract_hours',
        'hours',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'contract_hours' => 'decimal:2',
            'hours' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(InvoiceSite::class, 'invoice_site_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SiteShiftAssignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->assignments()->where('active', true);
    }
}
