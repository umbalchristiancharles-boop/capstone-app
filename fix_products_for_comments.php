<?php
/**
 * Quick fix script to publish products for the comments system
 * Run this to create test products or publish existing ones
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

echo "=== PRODUCT FIXER FOR COMMENTS SYSTEM ===\n\n";

// Check current status
$total = Product::count();
$active = Product::where('is_active', 1)->count();
$published = Product::where('is_published', 1)->count();
$activeAndPublished = Product::where('is_active', 1)->where('is_published', 1)->count();

echo "Current Status:\n";
echo "- Total products: $total\n";
echo "- Active products (is_active=1): $active\n";
echo "- Published products (is_published=1): $published\n";
echo "- Active AND Published: $activeAndPublished\n\n";

if ($activeAndPublished === 0) {
    echo "No products are both active and published! Fixing...\n\n";
    
    // Option 1: If there are any products, mark them as active and published
    if ($total > 0) {
        $updated = Product::update([
            'is_active' => 1,
            'is_published' => 1
        ]);
        echo "✓ Updated $updated existing products to be active and published\n";
    } else {
        echo "Creating test products...\n";
        
        // Create some test products based on the menu
        $testProducts = [
            ['name' => 'Fried Chicken Bundle', 'price' => 129.99, 'stock' => 50],
            ['name' => 'Ramen Bowl', 'price' => 89.99, 'stock' => 30],
            ['name' => 'Corn Dog', 'price' => 49.99, 'stock' => 40],
            ['name' => 'Ice Cream Sundae', 'price' => 59.99, 'stock' => 25],
            ['name' => 'Korean BBQ Set', 'price' => 299.99, 'stock' => 20],
        ];
        
        foreach ($testProducts as $product) {
            Product::create([
                'name' => $product['name'],
                'slug' => \Illuminate\Support\Str::slug($product['name']),
                'price' => $product['price'],
                'stock' => $product['stock'],
                'is_active' => 1,
                'is_published' => 1,
                'branch_id' => 1,
                'category' => 'Featured',
            ]);
            echo "✓ Created: {$product['name']}\n";
        }
    }
    
    echo "\n=== VERIFICATION ===\n";
    $newCount = Product::where('is_active', 1)->where('is_published', 1)->count();
    echo "Active and Published products now: $newCount\n";
    
    if ($newCount > 0) {
        echo "\n✓ FIXED! You can now submit comments on these products:\n";
        Product::where('is_active', 1)->where('is_published', 1)->select('id', 'name')->get()->each(function ($p) {
            echo "  - ({$p->id}) {$p->name}\n";
        });
    }
} else {
    echo "✓ Great! You have $activeAndPublished products ready for comments:\n";
    Product::where('is_active', 1)->where('is_published', 1)->select('id', 'name')->get()->each(function ($p) {
        echo "  - ({$p->id}) {$p->name}\n";
    });
}

echo "\n=== DONE ===\n";
