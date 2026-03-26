<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class MainBranchSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = config('chikintayo.default_password', 'Chikintayo_123');

        $mainBranch = Branch::updateOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Main Branch',
                'address' => 'HQ',
                'is_active' => 1,
                'is_main_branch' => 1,
                'budget' => 300000,
            ]
        );

        User::updateOrCreate(
            ['username' => 'admin_main_branch'],
            [
                'email' => null,
                'password' => $defaultPassword,
                'full_name' => 'Admin Main Branch',
                'role' => 'ADMIN',
                'department' => null,
                'branch_id' => $mainBranch->id,
                'is_active' => 1,
                'must_change_password' => 1,
            ]
        );

        User::updateOrCreate(
            ['username' => 'hr_main_branch'],
            [
                'email' => null,
                'password' => $defaultPassword,
                'full_name' => 'HR Main Branch',
                'role' => 'MANAGER',
                'department' => 'HR',
                'branch_id' => $mainBranch->id,
                'is_active' => 1,
                'must_change_password' => 1,
            ]
        );

        User::updateOrCreate(
            ['username' => 'finance_main_branch'],
            [
                'email' => null,
                'password' => $defaultPassword,
                'full_name' => 'Finance Main Branch',
                'role' => 'MANAGER',
                'department' => 'Finance',
                'branch_id' => $mainBranch->id,
                'is_active' => 1,
                'must_change_password' => 1,
            ]
        );

        User::updateOrCreate(
            ['username' => 'logistics_main_branch'],
            [
                'email' => null,
                'password' => $defaultPassword,
                'full_name' => 'Logistics Main Branch',
                'role' => 'MANAGER',
                'department' => 'Logistics',
                'branch_id' => $mainBranch->id,
                'is_active' => 1,
                'must_change_password' => 1,
            ]
        );
    }
}