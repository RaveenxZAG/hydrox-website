<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteShiftAssignment extends Model
{
    protected $fillable = [
        'invoice_site_shift_id',
        'staff_member_id',
        'assigned_by_user_id',
        'active',
        'assigned_at',
        'unassigned_at',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'assigned_at' => 'datetime',
            'unassigned_at' => 'datetime',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(InvoiceSiteShift::class, 'invoice_site_shift_id');
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(SiteAssignmentDelivery::class);
    }
}
