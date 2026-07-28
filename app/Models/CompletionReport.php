<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompletionReport extends Model
{
    use HasFactory;

    public const CHECKLIST_ITEMS = [
        'Vacuum Floors',
        'Mop Floors',
        'Kitchen Cleaned',
        'Bathrooms Sanitised',
        'Toilets Cleaned',
        'Windows Cleaned',
        'Mirrors Cleaned',
        'Glass Doors',
        'Carpets Steam Cleaned',
        'Bins Emptied',
        'Cobwebs Removed',
        'Walls Spot Cleaned',
        'Furniture Wiped',
        'High Touch Areas Sanitised',
        'Outdoor Area Cleaned',
        'Final Inspection Passed',
    ];

    protected $fillable = [
        'report_number',
        'job_id',
        'completion_date',
        'technician',
        'weather',
        'overall_condition',
        'customer_present',
        'checklist',
        'work_summary',
        'recommendations',
        'internal_notes',
        'technician_name',
        'technician_signature_path',
        'technician_signed_at',
        'customer_name',
        'customer_signature_path',
        'customer_signed_at',
        'pdf_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'completion_date' => 'date',
            'customer_present' => 'boolean',
            'checklist' => 'array',
            'technician_signed_at' => 'datetime',
            'customer_signed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CompletionReport $report): void {
            $report->report_number ??= 'RPT-'.now()->format('Ymd').'-'.str_pad((string) ((static::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT);
        });
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(CleaningArea::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductUsed::class)->orderBy('id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(IssueFound::class)->orderBy('id');
    }
}
