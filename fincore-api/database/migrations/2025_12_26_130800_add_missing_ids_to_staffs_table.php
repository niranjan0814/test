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
        Schema::table('staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('staffs', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('staffs', 'center_id')) {
                $table->unsignedBigInteger('center_id')->nullable()->after('branch_id');
            }

            // Add foreign key relationships
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('set null');

            $table->foreign('center_id')
                ->references('id')
                ->on('centers')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['center_id']);
            $table->dropColumn(['branch_id', 'center_id']);
        });
    }
};
