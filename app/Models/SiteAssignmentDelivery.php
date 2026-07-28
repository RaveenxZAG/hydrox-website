<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteAssignmentDelivery extends Model
{
    protected $fillable = [
        'site_shift_assignment_id',
        'event_type',
        'channel',
        'recipient',
        'status',
        'message',
        'error_message',
        'attempted_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(SiteShiftAssignment::class, 'site_shift_assignment_id');
    }
}
