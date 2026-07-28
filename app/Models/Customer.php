<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_number',
        'customer_name',
        'company',
        'phone',
        'email',
        'address',
        'suburb',
        'state',
        'postcode',
        'notes',
        'servicem8_uuid',
        'servicem8_synced_at',
        'servicem8_edit_date',
        'servicem8_dirty',
        'servicem8_sync_status',
        'servicem8_sync_message',
    ];

    protected function casts(): array
    {
        return [
            'servicem8_synced_at' => 'datetime',
            'servicem8_edit_date' => 'datetime',
            'servicem8_dirty' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer): void {
            $customer->customer_number ??= 'CUS-'.now()->format('Y').'-'.str_pad((string) ((static::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
        });
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    public function isServiceM8Imported(): bool
    {
        return filled($this->servicem8_uuid);
    }
}
