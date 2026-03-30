<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;

// Check all finance-related users
echo "=== All Finance-Related Users ===\n";
$users = User::whereIn('role', ['FINANCE_MANAGER', 'MANAGER', 'OWNER', 'SUPERADMIN', 'SUPER_ADMIN'])
    ->get(['id', 'email', 'full_name', 'role', 'branch_id']);

foreach ($users as $user) {
    $ordersCount = Order::where('branch_id', $user->branch_id)
        ->whereIn('status', ['completed', 'approved'])
        ->count();
    $revenue = Order::where('branch_id', $user->branch_id)
        ->whereIn('status', ['completed', 'approved'])
        ->sum('grand_total');
    
    echo "\nID {$user->id}: {$user->full_name}\n";
    echo "  Role: {$user->role}\n";
    echo "  Branch ID: " . ($user->branch_id ?? 'NULL') . "\n";
    echo "  Orders: {$ordersCount}\n";
    echo "  Revenue: ₱" . number_format($revenue, 2) . "\n";
}
