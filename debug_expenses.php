<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SupplierOrder;
use App\Models\ProcurementRequest;
use App\Models\BudgetRequest;

echo "=== Supplier Orders ===\n";
$supplierOrders = SupplierOrder::selectRaw('status, COUNT(*) as count, SUM(price * quantity) as total')
    ->groupBy('status')
    ->get();

foreach ($supplierOrders as $row) {
    echo $row->status . ": " . $row->count . " orders, ₱" . number_format($row->total, 2) . "\n";
}

echo "\n=== Procurement Requests ===\n";
$procurements = ProcurementRequest::selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')
    ->groupBy('status')
    ->get();

foreach ($procurements as $row) {
    echo $row->status . ": " . $row->count . " requests, ₱" . number_format($row->total, 2) . "\n";
}

echo "\n=== Budget Requests ===\n";
$budgets = BudgetRequest::selectRaw('status, COUNT(*) as count, SUM(requested_amount) as total')
    ->groupBy('status')
    ->get();

foreach ($budgets as $row) {
    echo $row->status . ": " . $row->count . " requests, ₱" . number_format($row->total, 2) . "\n";
}

echo "\n=== Total Expenses by Source ===\n";
$supplierTotal = (float) SupplierOrder::whereIn('status', ['fulfilled', 'completed', 'confirmed'])
    ->selectRaw('SUM(price * quantity) as total')
    ->value('total') ?? 0;
echo "Supplier Orders: ₱" . number_format($supplierTotal, 2) . "\n";

$procurementTotal = (float) ProcurementRequest::whereIn('status', ['Approved', 'Confirmed', 'Completed', 'On Delivery', 'Delivered'])
    ->sum('total_amount');
echo "Procurement Requests: ₱" . number_format($procurementTotal, 2) . "\n";

$budgetTotal = (float) BudgetRequest::whereIn('status', ['Approved', 'Budget Given'])
    ->sum('requested_amount');
echo "Budget Requests: ₱" . number_format($budgetTotal, 2) . "\n";

echo "\nTotal Expenses: ₱" . number_format($supplierTotal + $procurementTotal + $budgetTotal, 2) . "\n";
