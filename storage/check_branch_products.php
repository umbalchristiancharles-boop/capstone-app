<?php
require '../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$products = DB::table('products')->where('branch_id', 38)->get();
echo "Products in branch 38:\n";
foreach ($products as $p) {
    echo json_encode($p, JSON_PRETTY_PRINT) . "\n";
}
