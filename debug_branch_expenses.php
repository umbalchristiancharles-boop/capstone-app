<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SupplierOrder;
use App\Models\ProcurementRequest;
use App\Models\BudgetRequest;

$branchId = 31; // Dasma branch from earlier

echo "=== Branch 31 Expenses Debug ===\n\n";

// Supplier Orders
echo "Supplier Orders (fulfilled, on_delivery):\n";
$supplierOrders = SupplierOrder::where('branch_id', $branchId)
    ->whereIn('status', ['fulfilled', 'on_delivery', 'confirmed'])
    ->get(['id', 'status', 'price', 'quantity']);

if ($supplierOrders->isEmpty()) {
    echo "  No supplier orders found\n";
} else {
    foreach ($supplierOrders as $order) {
        echo "  ID {$order->id}: {$order->status} - {$order->quantity}x @ ₱{$order->price} = ₱" . ($order->price * $order->quantity) . "\n";
    }
}

$supplierTotal = (float) SupplierOrder::where('branch_id', $branchId)
    ->whereIn('status', ['fulfilled', 'on_delivery', 'confirmed'])
    ->selectRaw('SUM(price * quantity) as total')
    ->value('total') ?? 0;
echo "  TOTAL: ₱" . number_format($supplierTotal, 2) . "\n";

// Procurement Requests
echo "\nProcurement Requests (completed):\n";
$procurements = ProcurementRequest::where('branch_id', $branchId)
    ->where('status', 'completed')
    ->get(['id', 'status', 'total_amount']);

if ($procurements->isEmpty()) {
    echo "  No procurement requests found\n";
} else {
    foreach ($procurements as $proc) {
        echo "  ID {$proc->id}: {$proc->status} - ₱{$proc->total_amount}\n";
    }
}

$procurementTotal = (float) ProcurementRequest::where('branch_id', $branchId)
    ->where('status', 'completed')
    ->sum('total_amount');
echo "  TOTAL: ₱" . number_format($procurementTotal, 2) . "\n";

// Budget Requests
echo "\nBudget Requests (Approved, Budget Given):\n";
$budgets = BudgetRequest::where('branch_id', $branchId)
    ->whereIn('status', ['Approved', 'Budget Given'])
    ->get(['id', 'status', 'requested_amount']);

if ($budgets->isEmpty()) {
    echo "  No budget requests found\n";
} else {
    foreach ($budgets as $budget) {
        echo "  ID {$budget->id}: {$budget->status} - ₱{$budget->requested_amount}\n";
    }
}

$budgetTotal = (float) BudgetRequest::where('branch_id', $branchId)
    ->whereIn('status', ['Approved', 'Budget Given'])
    ->sum('requested_amount');
echo "  TOTAL: ₱" . number_format($budgetTotal, 2) . "\n";

echo "\n=== Grand Total ===\n";
$grandTotal = $supplierTotal + $procurementTotal + $budgetTotal;
echo "Total Expenses: ₱" . number_format($grandTotal, 2) . "\n";
