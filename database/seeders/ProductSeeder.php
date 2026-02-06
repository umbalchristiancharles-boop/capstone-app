<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['id' => 1, 'name' => 'Yangyeom', 'slug' => 'yangyeom'],
            ['id' => 2, 'name' => 'Snow Cheese', 'slug' => 'snowcheese'],
            ['id' => 3, 'name' => 'Corndog', 'slug' => 'corndog'],
            ['id' => 4, 'name' => 'Pastries', 'slug' => 'pastries'],
            ['id' => 5, 'name' => 'Ramen', 'slug' => 'ramen'],
            ['id' => 6, 'name' => 'Ice Cream', 'slug' => 'icecream'],
        ];

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['id' => $product['id']],
                [
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
