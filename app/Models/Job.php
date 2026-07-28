<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_number',
        'customer_id',
        'cleaning_service',
        'booking_date',
        'start_time',
        'finish_time',
        'technician',
        'priority',
        'status',
        'internal_notes',
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
            'booking_date' => 'date',
            'servicem8_synced_at' => 'datetime',
            'servicem8_edit_date' => 'datetime',
            'servicem8_dirty' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Job $job): void {
            $job->job_number ??= 'JOB-'.now()->format('Ymd').'-'.str_pad((string) ((static::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT);
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(CompletionReport::class);
    }

    public function isServiceM8Imported(): bool
    {
        return filled($this->servicem8_uuid);
    }
}
