<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'username' => 'admin_main',
            'email' => 'admin_main@example.com',
            'password' => Hash::make('AdminMain123!'),
            'full_name' => 'Main Admin',
            'role' => 'ADMIN',
            'department' => 'HR',
            'branch_id' => null,
            'avatar_url' => null,
            'phone_number' => '09171234567',
            'address' => 'Admin HQ',
            'is_active' => 1,
            'must_change_password' => 0,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
