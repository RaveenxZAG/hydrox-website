<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AreaPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'cleaning_area_id',
        'type',
        'path',
        'original_name',
        'notes',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(CleaningArea::class, 'cleaning_area_id');
    }
}
