<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubcontractorDocument extends Model
{
    protected $fillable = ['subcontractor_onboarding_id', 'staff_member_id', 'category'];

    public function onboarding(): BelongsTo
    {
        return $this->belongsTo(SubcontractorOnboarding::class, 'subcontractor_onboarding_id');
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SubcontractorDocumentVersion::class)->orderByDesc('version_number');
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(SubcontractorDocumentVersion::class)->where('is_current', true)->whereNull('archived_at')->latestOfMany('version_number');
    }
}
