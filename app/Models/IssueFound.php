<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssueFound extends Model
{
    protected $fillable = ['completion_report_id', 'issue_type', 'description', 'severity', 'photo_path', 'recommendation'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CompletionReport::class, 'completion_report_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(IssuePhoto::class);
    }
}
