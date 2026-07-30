<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'reference',
        'source',
        'external_reference',
        'status',
        'customer_name',
        'email',
        'phone',
        'service',
        'services',
        'extras',
        'frequency',
        'schedule_flexible',
        'preferred_date',
        'preferred_time',
        'address',
        'suburb',
        'postcode',
        'notes',
        'payload',
        'finalized_at',
        'customer_email_sent_at',
        'admin_email_sent_at',
        'email_error',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'services' => 'array',
            'extras' => 'array',
            'schedule_flexible' => 'boolean',
            'payload' => 'array',
            'finalized_at' => 'datetime',
            'customer_email_sent_at' => 'datetime',
            'admin_email_sent_at' => 'datetime',
        ];
    }

    public function photos(): HasMany
    {
        return $this->hasMany(BookingPhoto::class);
    }
}
