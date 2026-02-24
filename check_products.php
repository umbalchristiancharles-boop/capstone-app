<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

try {
    $products = Product::all();
    echo "Total products: " . $products->count() . "\n";
    
    if ($products->isEmpty()) {
        echo "No products found. Creating sample products...\n\n";
        
        $productNames = ['Yangyeom', 'Snow Cheese', 'Corndog', 'Pastries', 'Ramen', 'Ice Cream'];
        
        foreach ($productNames as $name) {
            $slug = strtolower(str_replace(' ', '-', $name));
            Product::create([
                'name' => $name,
                'slug' => $slug
            ]);
        }
        
        echo "Sample products created!\n";
    }
    
    echo "\nProducts in database:\n";
    Product::all()->each(function ($p) {
        echo "ID: {$p->id}, Name: {$p->name}, Slug: {$p->slug}\n";
    });

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
