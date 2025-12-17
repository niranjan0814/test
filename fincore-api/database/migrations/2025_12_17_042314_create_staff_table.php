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
        Schema::create('staffs', function (Blueprint $table) {
    $table->id();
    $table->string('staff_id')->unique()->nullable();
    $table->string('email_id')->unique();
    $table->string('account_status')->default('active');
    $table->string('contact_no')->nullable();
    $table->string('full_name');
    $table->string('name_with_initial')->nullable();
    $table->text('address')->nullable();
    $table->string('nic')->nullable();
    $table->json('work_info')->nullable();
    $table->integer('age')->nullable();
    $table->string('profile_image')->nullable();
    $table->string('gender')->nullable();
    $table->decimal('monthly_target_amount', 10, 2)->nullable();
    $table->integer('monthly_target_count')->nullable();
    $table->json('complaints')->nullable();
    $table->decimal('basic_salary', 10, 2)->nullable();
    $table->json('leave_details')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
