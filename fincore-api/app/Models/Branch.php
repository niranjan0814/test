<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'branch_id',
        'branch_name',
        'location',
        'address',
        'staff_ids',
    ];

    protected $casts = [
        'staff_ids' => 'array',
    ];
}
