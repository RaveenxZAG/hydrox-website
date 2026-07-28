<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffOtp extends Model
{
    protected $fillable = [
        'staff_member_id',
        'normalized_mobile',
        'normalized_email',
        'delivery_channel',
        'otp_hash',
        'expires_at',
        'consumed_at',
        'attempts',
        'request_ip',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }
}
