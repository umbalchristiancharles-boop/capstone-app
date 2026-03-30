<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\Expense;
use App\Models\User;

// Get first finance manager
$financeManager = User::where('role', 'FINANCE_MANAGER')->first() ?? 
                 User::where('role', 'MANAGER')->first() ??
                 User::where('role', 'OWNER')->first();

if (!$financeManager) {
    echo "No finance manager found\n";
    exit;
}

echo "=== Finance Manager ===\n";
echo "ID: " . $financeManager->id . "\n";
echo "Name: " . $financeManager->full_name . "\n";
echo "Branch ID: " . $financeManager->branch_id . "\n\n";

if (!$financeManager->branch_id) {
    echo "ERROR: Finance manager has no branch assigned!\n";
    exit;
}

$branchId = $financeManager->branch_id;

echo "=== Order Status Distribution ===\n";
$statusCounts = Order::selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->get();

foreach ($statusCounts as $row) {
    echo $row->status . ": " . $row->count . "\n";
}

echo "\n=== Orders for this branch ===\n";
$branchOrders = Order::where('branch_id', $branchId)
    ->selectRaw('status, COUNT(*) as count, SUM(grand_total) as total_revenue')
    ->groupBy('status')
    ->get();

foreach ($branchOrders as $row) {
    echo $row->status . ": " . $row->count . " orders, ₱" . number_format($row->total_revenue, 2) . "\n";
}

echo "\n=== Completed/Approved Orders for this branch (last 5) ===\n";
$completedOrders = Order::where('branch_id', $branchId)
    ->whereIn('status', ['completed', 'approved'])
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->select('id', 'order_code', 'status', 'grand_total', 'created_at')
    ->get();

if ($completedOrders->isEmpty()) {
    echo "No completed or approved orders found!\n";
} else {
    foreach ($completedOrders as $order) {
        echo "Order " . $order->order_code . ": " . $order->status . " - ₱" . number_format($order->grand_total, 2) . " (" . $order->created_at->format('Y-m-d H:i') . ")\n";
    }
}

echo "\n=== Total Revenue (completed/approved) ===\n";
$totalRevenue = Order::where('branch_id', $branchId)
    ->whereIn('status', ['completed', 'approved'])
    ->sum('grand_total');
echo "₱" . number_format($totalRevenue, 2) . "\n";

echo "\n=== Total Expenses ===\n";
$totalExpenses = Expense::where('branch_id', $branchId)->sum('amount');
echo "₱" . number_format($totalExpenses, 2) . "\n";
