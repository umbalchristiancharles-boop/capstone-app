<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==========================================\n";
echo "FIXING ALL USER PASSWORDS\n";
echo "==========================================\n\n";

$passwordResets = [
    ['username' => 'admin', 'password' => 'Admin123!'],
    ['username' => 'Paul Berrer', 'password' => 'Paul@123'],
    ['username' => 'Gabby', 'password' => 'Gabby@123'],
    ['username' => 'manager', 'password' => 'Manager123!'],
    ['username' => 'staff', 'password' => 'Staff123!'],
];

foreach ($passwordResets as $reset) {
    $user = DB::table('users')->where('username', $reset['username'])->first();
    
    if (!$user) {
        echo "⚠️  User '{$reset['username']}' not found, skipping...\n";
        continue;
    }
    
    // Check if password already matches
    if (Hash::check($reset['password'], $user->password)) {
        echo "✓ {$reset['username']} - password already correct\n";
        continue;
    }
    
    // Update password
    DB::table('users')
        ->where('username', $reset['username'])
        ->update([
            'password' => Hash::make($reset['password']),
            'must_change_password' => true,
            'updated_at' => now(),
        ]);
    
    // Verify
    $user = DB::table('users')->where('username', $reset['username'])->first();
    $verified = Hash::check($reset['password'], $user->password);
    
    if ($verified) {
        echo "✓ {$reset['username']} - password reset to: {$reset['password']}\n";
    } else {
        echo "✗ {$reset['username']} - FAILED TO RESET PASSWORD!\n";
    }
}

echo "\n==========================================\n";
echo "TEST CREDENTIALS:\n";
echo "==========================================\n";
echo "Paul Berrer / Paul@123\n";
echo "admin / Admin123!\n";
echo "Gabby / Gabby@123\n";
echo "manager / Manager123!\n";
echo "staff / Staff123!\n";
echo "\n";
