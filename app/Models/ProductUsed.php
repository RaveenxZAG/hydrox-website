<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUsed extends Model
{
    protected $fillable = ['completion_report_id', 'product_name', 'quantity', 'equipment_used', 'notes'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CompletionReport::class, 'completion_report_id');
    }
}
