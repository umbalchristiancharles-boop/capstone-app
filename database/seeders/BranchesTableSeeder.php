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
                'code' => 'DASMA_MAIN',
                'name' => 'Dasmariñas Cavite Main Branch',
                'address' => 'Dasmariñas, Cavite',
                'is_active' => 1,
            ],
            [
                'code' => 'GENTRI',
                'name' => 'General Trias',
                'address' => 'General Trias, Cavite',
                'is_active' => 1,
            ],
            [
                'code' => 'QC',
                'name' => 'Quezon City Branch',
                'address' => 'Quezon City, Metro Manila',
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
