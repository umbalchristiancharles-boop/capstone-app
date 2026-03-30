<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProcurementRequest;
use App\Models\SupplierOrder;

echo "=== ProcurementRequest Status Values ===\n";
$statuses = ProcurementRequest::select('status')->distinct()->get();
foreach ($statuses as $row) {
    echo "- '" . $row->status . "'\n";
}

echo "\n=== SupplierOrder Status Values ===\n";
$statuses = SupplierOrder::select('status')->distinct()->get();
foreach ($statuses as $row) {
    echo "- '" . $row->status . "'\n";
}

// Test what would be included
echo "\n=== Test Expense Calculation ===\n";
$procurements = ProcurementRequest::withoutGlobalScopes()
    ->get()
    ->groupBy('status')
    ->map(fn($items) => count($items));
    
foreach ($procurements as $status => $count) {
    echo $status . ": $count\n";
}
