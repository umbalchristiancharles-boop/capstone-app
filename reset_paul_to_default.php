<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$defaultPassword = 'ChikinTayo_2526';

// Reset Paul Berrer to default password
$user = DB::table('users')->where('username', 'Paul Berrer')->first();

if (!$user) {
    echo "❌ User not found!\n";
    exit(1);
}

echo "Current password checks:\n";
echo "  Paul@123: " . (Hash::check('Paul@123', $user->password) ? "✓" : "✗") . "\n";
echo "  ChikinTayo_2526: " . (Hash::check($defaultPassword, $user->password) ? "✓" : "✗") . "\n\n";

// Update to default password
DB::table('users')
    ->where('username', 'Paul Berrer')
    ->update([
        'password' => Hash::make($defaultPassword),
        'must_change_password' => true,
        'updated_at' => now(),
    ]);

echo "✓ Password reset to: {$defaultPassword}\n";

// Verify
$user = DB::table('users')->where('username', 'Paul Berrer')->first();
$verified = Hash::check($defaultPassword, $user->password);

echo "Verification: " . ($verified ? "✓ SUCCESS" : "✗ FAILED") . "\n\n";

echo "==========================================\n";
echo "LOGIN CREDENTIALS:\n";
echo "==========================================\n";
echo "Username: Paul Berrer\n";
echo "Password: {$defaultPassword}\n";
echo "\nAfter login, you'll be prompted to change your password.\n";
