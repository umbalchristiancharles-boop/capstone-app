<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Avoid duplicate insertion
        $exists = DB::table('users')->where('username', 'superadmin')->exists();
        if ($exists) {
            return;
        }

        DB::table('users')->insert([
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('SuperAdmin123!'),
            'full_name' => 'Super Administrator',
            'role' => 'SUPER_ADMIN',
            'department' => null,
            'branch_id' => null,
            'avatar_url' => null,
            'phone_number' => null,
            'address' => null,
            'is_active' => 1,
            'must_change_password' => 0,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
