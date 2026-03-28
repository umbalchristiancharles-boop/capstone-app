<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use Carbon\Carbon;

// Update products with categories and expiration dates
$updates = [
    29 => ['category' => 'Condiment', 'expires_at' => Carbon::now()->addDays(90)],
    38 => ['category' => 'Vegetable', 'expires_at' => Carbon::now()->addDays(60)],
    45 => ['category' => 'Meat', 'expires_at' => Carbon::now()->addDays(30)],
    46 => ['category' => 'Grain', 'expires_at' => Carbon::now()->addDays(180)],
    47 => ['category' => 'Meat', 'expires_at' => Carbon::now()->addDays(30)],
    48 => ['category' => 'Meat', 'expires_at' => Carbon::now()->addDays(30)],
    49 => ['category' => 'Beverage', 'expires_at' => Carbon::now()->addDays(365)],
    50 => ['category' => 'Spice', 'expires_at' => Carbon::now()->addDays(365)],
    51 => ['category' => 'Condiment', 'expires_at' => Carbon::now()->addDays(90)],
];

$count = 0;
foreach ($updates as $productId => $data) {
    $product = Product::find($productId);
    if ($product) {
        $product->update($data);
        echo "✓ Updated {$product->name} - Category: {$data['category']}, Expires: {$data['expires_at']}\n";
        $count++;
    }
}

echo "\n✓ Successfully updated $count products with categories and expiration dates!\n";

// Display all products
echo "\n=== All Products Updated ===\n";
Product::all(['id', 'name', 'category', 'expires_at'])->each(function($p) {
    echo "{$p->id}: {$p->name} | Category: {$p->category} | Expires: {$p->expires_at}\n";
});
