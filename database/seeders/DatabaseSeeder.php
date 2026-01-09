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
        // Meron ka nang existing owner_admin sa users table (id = 1),
        // kaya hindi na tayo gagawa ng bagong user dito.

        $this->call([
            OrdersTableSeeder::class,
        ]);
    }
}
