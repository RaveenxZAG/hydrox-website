<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContact extends Model
{
    protected $fillable = [
        'customer_id',
        'servicem8_uuid',
        'name',
        'role',
        'phone',
        'mobile',
        'email',
        'is_primary',
        'is_billing',
        'is_servicem8_imported',
        'servicem8_edit_date',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_billing' => 'boolean',
            'is_servicem8_imported' => 'boolean',
            'servicem8_edit_date' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
