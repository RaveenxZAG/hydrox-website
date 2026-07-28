<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'preferred_date',
        'preferred_time',
        'address',
        'suburb',
        'postcode',
        'notes',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'payload' => 'array',
        ];
    }
}
