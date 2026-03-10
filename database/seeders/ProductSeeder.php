<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = 14; // Use branch ID 14 (CHKIN TAYO DASMARINAS)

        $products = [
            ['id' => 1, 'name' => 'Yangyeom', 'slug' => 'yangyeom', 'price' => 150.00, 'stock' => 100, 'sku' => 'YANG-001'],
            ['id' => 2, 'name' => 'Snow Cheese', 'slug' => 'snowcheese', 'price' => 180.00, 'stock' => 100, 'sku' => 'SNOW-001'],
            ['id' => 3, 'name' => 'Corndog', 'slug' => 'corndog', 'price' => 120.00, 'stock' => 100, 'sku' => 'CORN-001'],
            ['id' => 4, 'name' => 'Pastries', 'slug' => 'pastries', 'price' => 80.00, 'stock' => 100, 'sku' => 'PAST-001'],
            ['id' => 5, 'name' => 'Ramen', 'slug' => 'ramen', 'price' => 200.00, 'stock' => 100, 'sku' => 'RAMEN-001'],
            ['id' => 6, 'name' => 'Ice Cream', 'slug' => 'icecream', 'price' => 60.00, 'stock' => 100, 'sku' => 'ICE-001'],
        ];

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['id' => $product['id']],
                [
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'sku' => $product['sku'],
                    'branch_id' => $branchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
