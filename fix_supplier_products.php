<?php
/**
 * Fix existing supplier products: set supplier_id=137 (Umberto), is_active=1 for branch 28
 */
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== FIX SUPPLIER PRODUCTS ===\n";

$branchId = 28;
$supplierId = 137; // Umberto

// Find potential supplier products
$potential = Product::where('branch_id', $branchId)
    ->where(function($q) {
        $q->whereNotNull('supplier_name')
          ->where('supplier_name', '!=', '')
          ->orWhereNotNull('supplier_id');
    })
    ->where('is_active', 0)
    ->orWhereNull('supplier_id')
    ->get();
/** @var \Illuminate\Database\Eloquent\Collection|Product[] $potential */
echo "Found " . $potential->count() . " products to fix:\n";
foreach ($potential as $p) {
    echo "- ID {$p->id}: {$p->name} (active={$p->is_active}, supplier_id={$p->supplier_id}, name='{$p->supplier_name}')\n";
}

if ($potential->count() > 0) {
    $updated = 0;
    foreach ($potential as $p) {
        $p->supplier_id = $supplierId;
        $p->is_active = 1;
        $p->save();
        $updated++;
        echo "Fixed product ID {$p->id}\n";
    }
    echo "Updated $updated products.\n";
} else {
    echo "No products need fixing.\n";
}

echo "\n=== DONE ===\n";
?>

