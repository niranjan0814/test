<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // No connection to Staff table as requested
        User::updateOrCreate(
            ['user_name' => 'SA0001'], // ✅ Changed from admin_username
            [
                'role'              => 'super_admin',
                'email'             => 'admin@fincore.com', // Optional email
                'password'          => 'S@1234admin',
                'digital_signature' => Hash::make('SA0001'), // Renamed field
                'is_active'         => true,
                'failed_login_attempts' => 0,
            ]
        );

        $this->command->info('Super Admin user created successfully.');
    }
}
