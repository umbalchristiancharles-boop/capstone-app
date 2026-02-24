<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'full_name' => 'Admin User',
            'role' => 'OWNER',
            'department' => 'HR',
            'branch_id' => null,
            'avatar_url' => null,
            'phone_number' => '1234567890',
            'address' => '123 Main St',
            'is_active' => 1,
            'must_change_password' => 0,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
