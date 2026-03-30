<?php
// Quick diagnostic to check financial data
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FINANCE DATA DIAGNOSTIC ===\n\n";

// Check branches
$branches = DB::table('branches')->where('is_active', true)->get();
echo "Active Branches: " . count($branches) . "\n";
foreach ($branches as $b) {
    echo "  - {$b->name} (ID: {$b->id}, Budget: {$b->budget})\n";
}

// Check orders
echo "\n--- Orders ---\n";
$totalOrders = DB::table('orders')->count();
echo "Total Orders: {$totalOrders}\n";

$completedOrders = DB::table('orders')->whereIn('status', ['completed', 'approved'])->count();
echo "Completed/Approved Orders: {$completedOrders}\n";

$completedRevenue = DB::table('orders')->whereIn('status', ['completed', 'approved'])->sum('grand_total');
echo "Total Revenue (completed): ₱" . number_format($completedRevenue, 2) . "\n";

// By branch
echo "\n--- By Branch ---\n";
foreach ($branches as $b) {
    $branchOrders = DB::table('orders')
        ->where('branch_id', $b->id)
        ->whereIn('status', ['completed', 'approved'])
        ->count();
    $branchRevenue = DB::table('orders')
        ->where('branch_id', $b->id)
        ->whereIn('status', ['completed', 'approved'])
        ->sum('grand_total');
    echo "{$b->name}: {$branchOrders} orders, ₱" . number_format($branchRevenue, 2) . " revenue\n";
}

// Check users with finance access
echo "\n--- Finance Users ---\n";
$financeUsers = DB::table('users')
    ->whereIn('role', ['FINANCE_MANAGER', 'OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])
    ->get(['id', 'full_name', 'role', 'branch_id', 'email']);
foreach ($financeUsers as $u) {
    echo "{$u->full_name} ({$u->role}) - Branch: {$u->branch_id}, Email: {$u->email}\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
