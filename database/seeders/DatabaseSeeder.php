<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TestUserSeeder::class,
            AdminUserSeeder::class,
            SuperAdminSeeder::class,
            MainBranchSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
