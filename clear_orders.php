<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// First delete order_items (child table) due to foreign key constraint
DB::table('order_items')->delete();

// Then delete orders (parent table)
DB::table('orders')->delete();

echo "✓ All orders and order items cleared!\n";

