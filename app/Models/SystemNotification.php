<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SystemNotification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'action_url',
        'subject_type',
        'subject_id',
        'read_at',
        'emailed_at',
        'telegram_sent_at',
        'telegram_error',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'emailed_at' => 'datetime',
        'telegram_sent_at' => 'datetime',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }
}
