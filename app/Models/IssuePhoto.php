<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuePhoto extends Model
{
    protected $fillable = ['issue_found_id', 'path', 'original_name', 'captured_at'];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
        ];
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(IssueFound::class, 'issue_found_id');
    }
}
