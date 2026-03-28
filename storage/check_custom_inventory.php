<?php
// Direct database query to check custom user
require '../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$user = DB::table('users')->where('username', 'custom')->first();
if ($user) {
    echo "Found user: " . $user->username . "\n";
    echo "ID: " . $user->id . "\n";
    echo "Role: " . $user->role . "\n";
    echo "Department: " . $user->department . "\n";
    echo "Branch ID: " . $user->branch_id . "\n";
    echo "Permissions: " . $user->permissions . "\n";
    echo "\n";
    
    // Check products
    if ($user->branch_id) {
        $productCount = DB::table('products')->where('branch_id', $user->branch_id)->count();
        echo "Products in branch {$user->branch_id}: {$productCount}\n";
    }
}
