<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;

class SuperAdminStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Staff::create([
            'staff_id' => 'SA%$001',
            'email_id' => 'superadmin@fincore.com',
            'full_name' => 'Super Admin',
            'account_status' => 'active',
        ]);
    }
}
