<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Reset Paul Berrer's password to Paul@123
$newPassword = 'Paul@123';
$hashedPassword = Hash::make($newPassword);

DB::table('users')
    ->where('username', 'Paul Berrer')
    ->update([
        'password' => $hashedPassword,
        'must_change_password' => true,
        'updated_at' => now(),
    ]);

echo "Password for 'Paul Berrer' has been reset to: {$newPassword}\n";
echo "User must change password on next login.\n";

// Verify the change
$user = DB::table('users')->where('username', 'Paul Berrer')->first();
$verified = Hash::check($newPassword, $user->password);
echo "\nVerification: " . ($verified ? "✓ Password reset successful!" : "✗ Failed!") . "\n";
