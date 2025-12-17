<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $fillable = [
        'staff_id',
        'email_id',
        'account_status',
        'contact_no',
        'full_name',
        'name_with_initial',
        'address',
        'nic',
        'work_info',
        'age',
        'profile_image',
        'gender',
        'monthly_target_amount',
        'monthly_target_count',
        'companies',
        'basic_salary',
        'leave_details',
    ];

    protected $casts = [
        'companies' => 'array',
        'leave_details' => 'array',
        'monthly_target_amount' => 'decimal:2',
        'basic_salary' => 'decimal:2',
    ];
}
