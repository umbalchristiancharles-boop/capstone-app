<?php
// Check products stock levels for procurement
require 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Capsule\Manager as DBManager;

$capsule = new DBManager;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'chikintayo_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== PRODUCTS STOCK STATUS ===\n\n";

$products = DB::table('products')
    ->select('id', 'name', 'branch_id', 'stock', 'min_stock', 'is_published')
    ->orderBy('branch_id')
    ->orderBy('name')
    ->get();

foreach ($products as $p) {
    $status = ($p->stock <= ($p->min_stock ?? 10)) ? 'LOW STOCK' : 'OK';
    $color = $status === 'LOW STOCK' ? "\033[31m" : "\033[32m";
    $reset = "\033[0m";
    
    echo sprintf(
        "%sID:%d %s (Branch %d) %sStock:%d/%d %s%s%s\n",
        $color, $p->id, $p->name, $p->branch_id, 
        $status, $p->stock, $p->min_stock ?? 10, $reset
    );
}

echo "\nLOW STOCK products (ready for procurement button):\n";
$low = $products->where('stock', '<=', DB::raw('COALESCE(min_stock, 10)'));
foreach ($low as $p) {
    echo "  ID:{$p->id} {$p->name}\n";
}
?>

