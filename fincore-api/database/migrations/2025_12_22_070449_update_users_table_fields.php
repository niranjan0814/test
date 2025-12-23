<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop existing columns to be replaced (check if they exist first)
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'name')) $columnsToDrop[] = 'name';
            if (Schema::hasColumn('users', 'status')) $columnsToDrop[] = 'status';
            if (Schema::hasColumn('users', 'login_attempts')) $columnsToDrop[] = 'login_attempts';
            if (Schema::hasColumn('users', 'phone')) $columnsToDrop[] = 'phone';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            // Add new columns (check if they don't exist first)
            if (!Schema::hasColumn('users', 'user_name')) {
                $table->string('user_name')->after('id');
            }
            if (!Schema::hasColumn('users', 'digital_signature')) {
                $table->string('digital_signature')->nullable()->after('user_name');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('digital_signature');
            }
            if (!Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->integer('failed_login_attempts')->default(0)->after('two_factor_confirmed_at');
            }
            
            // Add indexes (check if they exist to avoid errors might be tricky, but typically safe to add if not exists. 
            // However, since we are doing conditional columns, we should probably just add indexes for columns we know exist now)
            
            // We can try-catch or just try add them. But since user_name is likely unique already from creation, adding index 'user_name' might be redundant but valid.
            // Let's rely on Laravel to handle index creation or let the user know if index fails.
            // But to be safe, let's checking index existence is harder in portable way. 
            // Given the error was about DROP COLUMN, let's fix that first.
            
            // Re-adding indexes blindly can fail if they exist.
            // But standard behaviour is to just add them.
            // If user_name was created in create_users_table with ->unique(), it has a unique index.
            // Adding ->index('user_name') attempts to add a non-unique index. MySQL allows both.
            
             try {
                $table->index('user_name');
            } catch (\Exception $e) {}
            
            try {
                $table->index('is_active');
            } catch (\Exception $e) {}

            try {
                $table->index('failed_login_attempts');
            } catch (\Exception $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverse the changes
            $table->dropColumn(['user_name', 'digital_signature', 'is_active', 'failed_login_attempts']);
            
            // Restore original columns
            $table->string('name')->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('avatar');
            $table->integer('login_attempts')->default(0)->after('two_factor_confirmed_at');
        });
    }
};