<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\CustomerAccount;

try {
    $customers = User::where('role', 'customer')->get();
    
    echo "Customer accounts:\n";
    echo "==================\n\n";
    
    if ($customers->isEmpty()) {
        echo "No customer accounts found.\n";
    } else {
        foreach ($customers as $user) {
            echo "ID: {$user->id}\n";
            echo "Email: {$user->email}\n";
            echo "Username: {$user->username}\n";
            echo "Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
            echo "Created: {$user->created_at}\n";
            echo "-------------------\n";
        }
    }
    
    echo "\nYou can login with any of these customer accounts using their email and password.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
