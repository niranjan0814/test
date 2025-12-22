<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            // Product / Location Details
            $table->string('location')->nullable();
            $table->string('product_type')->nullable();
            $table->string('base_product')->nullable();
            $table->string('pcsu_csu_code')->nullable();
            
            // Customer Personal Details (Required for initial creation)
            $table->string('code_type');
            $table->string('customer_code')->unique();
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->string('title');
            $table->string('full_name');
            $table->string('initials');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->enum('civil_status', ['Single', 'Married', 'Divorced', 'Widowed']);
            $table->string('religion');
            $table->string('mobile_no_1');
            $table->string('mobile_no_2')->nullable();
            $table->string('ccl_mobile_no')->nullable();
            $table->string('spouse_name')->nullable();
            $table->json('health_info')->nullable();
            $table->integer('family_members_count')->nullable();
            $table->string('customer_profile_image')->nullable();
            $table->decimal('monthly_income', 15, 2)->nullable();
            
            // Customer Address Details (Required for initial creation)
            $table->string('address_type');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('address_line_3')->nullable();
            $table->string('country');
            $table->string('province');
            $table->string('district');
            $table->string('city');
            $table->string('gs_division'); // Grama Sevaka Division
            $table->string('telephone')->nullable();
            $table->boolean('preferred_address')->default(false);
            
            // Business Details (All nullable - can be added later)
            $table->string('ownership_type')->nullable();
            $table->string('register_number')->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_email')->nullable();
            $table->string('business_duration')->nullable();
            $table->string('business_place')->nullable();
            $table->string('handled_by')->nullable();
            $table->integer('no_of_employees')->nullable();
            $table->string('market_reputation')->nullable();
            $table->string('sector')->nullable();
            $table->string('sub_sector')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
