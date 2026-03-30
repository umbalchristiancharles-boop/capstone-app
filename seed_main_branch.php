<?php
// Create sample orders for Main Branch
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Create 5 sample completed orders for Main Branch (ID: 32)
for ($i = 1; $i <= 5; $i++) {
    $subtotal = 1000 + (rand(0, 500) * 0.1);
    $vat = $subtotal * 0.12;
    $total = $subtotal + $vat;
    
    DB::table('orders')->insert([
        'order_code' => 'ORD-MAIN-' . str_pad($i, 4, '0', STR_PAD_LEFT),
        'branch_id' => 32,
        'customer_name' => 'Test Customer ' . $i,
        'status' => 'completed',
        'subtotal' => $subtotal,
        'discount_percent' => 0,
        'discount_amount' => 0,
        'vat_percent' => 12,
        'vat_amount' => $vat,
        'grand_total' => $total,
        'amount_paid' => $total,
        'change_amount' => 0,
        'completed_at' => Carbon::now(),
        'completed_by' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
    ]);
}

echo "✅ Created 5 test orders for Main Branch\n";

// Show updated data
$revenue = DB::table('orders')
    ->where('branch_id', 32)
    ->whereIn('status', ['completed', 'approved'])
    ->sum('grand_total');
$count = DB::table('orders')
    ->where('branch_id', 32)
    ->whereIn('status', ['completed', 'approved'])
    ->count();

echo "Main Branch now has: $count orders, ₱" . number_format($revenue, 2) . " revenue\n";
