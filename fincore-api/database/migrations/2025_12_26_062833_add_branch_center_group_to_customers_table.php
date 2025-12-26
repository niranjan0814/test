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
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('branch_id')->after('id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('center_id')->after('branch_id')->nullable()->constrained('centers')->cascadeOnDelete();
            $table->foreignId('grp_id')->after('center_id')->nullable()->constrained('groups')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['center_id']);
            $table->dropForeign(['grp_id']);
            $table->dropColumn(['branch_id', 'center_id', 'grp_id']);
        });
    }
};
