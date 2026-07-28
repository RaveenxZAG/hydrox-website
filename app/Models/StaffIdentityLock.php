<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffIdentityLock extends Model
{
    protected $fillable = [
        'normalized_email',
        'normalized_mobile',
        'owner_type',
        'owner_id',
    ];
}
