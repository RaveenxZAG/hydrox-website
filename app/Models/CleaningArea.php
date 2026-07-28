<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CleaningArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'completion_report_id',
        'area_name',
        'description',
        'completion_notes',
        'photo_notes',
        'video_url',
        'sort_order',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CompletionReport::class, 'completion_report_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AreaPhoto::class);
    }

    public function beforePhotos(): HasMany
    {
        return $this->photos()->where('type', 'before');
    }

    public function afterPhotos(): HasMany
    {
        return $this->photos()->where('type', 'after');
    }
}
