<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffBroadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'channels',
        'subject',
        'message',
        'recipient_count',
        'sent_count',
        'skipped_count',
        'failed_count',
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(StaffBroadcastRecipient::class);
    }
}
