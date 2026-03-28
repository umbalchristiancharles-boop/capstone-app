<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\User;

// Find a CUSTOM user with inventory module
$customUser = User::where('role', 'CUSTOM')->first();

if ($customUser) {
    echo "Found CUSTOM user:\n";
    echo "  ID: " . $customUser->id . "\n";
    echo "  Username: " . $customUser->username . "\n";
    echo "  Email: " . $customUser->email . "\n";
    echo "  Branch ID: " . ($customUser->branch_id ?? 'NULL') . "\n";
    echo "  Department: " . $customUser->department . "\n";
    echo "  Permissions: " . $customUser->permissions . "\n";
    echo "  Is Active: " . ($customUser->is_active ? 'YES' : 'NO') . "\n";
    
    // Try to parse permissions
    if ($customUser->permissions) {
        $perms = json_decode($customUser->permissions, true);
        echo "  Parsed Permissions: " . print_r($perms, true);
    }
} else {
    echo "No CUSTOM user found\n";
}

// Also check for inventory branch products
if ($customUser && $customUser->branch_id) {
    $productCount = \App\Models\Product::where('branch_id', $customUser->branch_id)->count();
    echo "\nProducts in branch {$customUser->branch_id}: " . $productCount . "\n";
}
