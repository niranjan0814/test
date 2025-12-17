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
        Schema::create('centers', function (Blueprint $table) {
    $table->id();
    $table->string('CSU_id')->unique()->nullable();
    $table->json('open_days')->nullable();

    $table->foreignId('branch_id')
          ->constrained('branches')
          ->cascadeOnDelete();

    $table->foreignId('staff_id')
          ->constrained('staffs')
          ->cascadeOnDelete();

    $table->string('center_name');
    $table->string('location')->nullable();
    $table->text('address')->nullable();
    $table->integer('group_count')->default(0);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centers');
    }
};
