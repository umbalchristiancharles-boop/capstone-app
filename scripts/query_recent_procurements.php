<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $rows = DB::table('procurement_requests')->orderBy('created_at', 'desc')->limit(30)->get();
    foreach ($rows as $r) {
        echo "ID: {$r->id} | product_id: {$r->product_id} | branch_id: {$r->branch_id} | status: {$r->status} | qty: {$r->quantity} | price: {$r->price} | budget_amount: {$r->budget_amount} | receipt_confirmed: {$r->receipt_confirmed}\n";
    }
    if (count($rows)==0) echo "No procurement_requests found.\n";
} catch (Exception $e) {
    echo "Error querying DB: " . $e->getMessage() . "\n";
}
