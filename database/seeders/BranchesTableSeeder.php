<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'code' => 'QC_MAIN',
                'name' => 'Quezon City Main Branch',
                'address' => 'Quezon City, Metro Manila',
                'is_active' => 1,
            ],
            [
                'code' => 'MAKATI',
                'name' => 'Makati Branch',
                'address' => 'Makati City, Metro Manila',
                'is_active' => 1,
            ],
            [
                'code' => 'BGC',
                'name' => 'BGC Branch',
                'address' => 'Bonifacio Global City, Taguig',
                'is_active' => 1,
            ],
            [
                'code' => 'PASIG',
                'name' => 'Pasig Branch',
                'address' => 'Pasig City, Metro Manila',
                'is_active' => 1,
            ],
            [
                'code' => 'MANILA',
                'name' => 'Manila Branch',
                'address' => 'Manila City',
                'is_active' => 1,
            ],
        ];

        foreach ($branches as $branch) {
            $exists = DB::table('branches')->where('code', $branch['code'])->exists();

            if (!$exists) {
                DB::table('branches')->insert([
                    'code' => $branch['code'],
                    'name' => $branch['name'],
                    'address' => $branch['address'],
                    'is_active' => $branch['is_active'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
