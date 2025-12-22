<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Customer extends Model
{
    // Sri Lankan Location Constants
    const COUNTRY = 'Sri Lanka';
    
    const PROVINCES = [
        'Western',
        'Central',
        'Southern',
        'Northern',
        'Eastern',
        'North Western',
        'North Central',
        'Uva',
        'Sabaragamuwa',
    ];
    
    const DISTRICTS = [
        // Western Province
        'Colombo',
        'Gampaha',
        'Kalutara',
        // Central Province
        'Kandy',
        'Matale',
        'Nuwara Eliya',
        // Southern Province
        'Galle',
        'Matara',
        'Hambantota',
        // Northern Province
        'Jaffna',
        'Kilinochchi',
        'Mannar',
        'Vavuniya',
        'Mullaitivu',
        // Eastern Province
        'Batticaloa',
        'Ampara',
        'Trincomalee',
        // North Western Province
        'Kurunegala',
        'Puttalam',
        // North Central Province
        'Anuradhapura',
        'Polonnaruwa',
        // Uva Province
        'Badulla',
        'Monaragala',
        // Sabaragamuwa Province
        'Ratnapura',
        'Kegalle',
    ];
    
    const CITIES = [
        'Colombo',
        'Dehiwala-Mount Lavinia',
        'Moratuwa',
        'Negombo',
        'Gampaha',
        'Kalutara',
        'Kandy',
        'Matale',
        'Nuwara Eliya',
        'Galle',
        'Matara',
        'Hambantota',
        'Jaffna',
        'Kilinochchi',
        'Mannar',
        'Vavuniya',
        'Batticaloa',
        'Ampara',
        'Trincomalee',
        'Kurunegala',
        'Puttalam',
        'Anuradhapura',
        'Polonnaruwa',
        'Badulla',
        'Monaragala',
        'Ratnapura',
        'Kegalle',
    ];

    protected $fillable = [
        // Product / Location Details
        'location',
        'product_type',
        'base_product',
        'pcsu_csu_code',
        
        // Customer Personal Details
        'code_type',
        'customer_code',
        'gender',
        'title',
        'full_name',
        'initials',
        'first_name',
        'last_name',
        'date_of_birth',
        'civil_status',
        'religion',
        'mobile_no_1',
        'mobile_no_2',
        'ccl_mobile_no',
        'spouse_name',
        'health_info',
        'family_members_count',
        'customer_profile_image',
        'monthly_income',
        
        // Customer Address Details
        'address_type',
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'country',
        'province',
        'district',
        'city',
        'gs_division',
        'telephone',
        'preferred_address',
        
        // Business Details
        'ownership_type',
        'register_number',
        'business_name',
        'business_email',
        'business_duration',
        'business_place',
        'handled_by',
        'no_of_employees',
        'market_reputation',
        'sector',
        'sub_sector',
    ];

    protected $casts = [
        'health_info' => 'array',
        'date_of_birth' => 'date',
        'monthly_income' => 'decimal:2',
        'preferred_address' => 'boolean',
    ];
}
