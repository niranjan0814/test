<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentProduct extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'investment_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'interest_rate',
        'age_limited',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'interest_rate' => 'decimal:2',
        'age_limited' => 'integer',
    ];
}
