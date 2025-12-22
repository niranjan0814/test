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
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->text('product_details')->nullable();
            $table->string('term_type');
            $table->string('regacine')->nullable();
            $table->decimal('interest_rate', 5, 2); // e.g., 12.50%
            $table->decimal('loan_limited_amount', 15, 2)->nullable();
            $table->decimal('loan_amount', 15, 2);
            $table->integer('loan_term'); // in months or years
            $table->integer('customer_age_limited')->nullable();
            $table->decimal('customer_monthly_income', 15, 2)->nullable();
            $table->decimal('guarantor_monthly_income', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
