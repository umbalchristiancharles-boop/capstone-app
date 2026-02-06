<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = DB::table('users')->where('username', 'Paul Berrer')->first();

if (!$user) {
    echo "User 'Paul Berrer' not found!\n";
    exit(1);
}

echo "User found:\n";
echo "  ID: {$user->id}\n";
echo "  Username: {$user->username}\n";
echo "  Email: {$user->email}\n";
echo "  Role: {$user->role}\n";
echo "  Password Hash: {$user->password}\n\n";

// Test various passwords
$testPasswords = [
    'Paul@123',
    'paul@123',
    'ChikinTayo_2526',
    'Paul Berrer',
    'paulberrer',
];

echo "Testing passwords:\n";
foreach ($testPasswords as $testPass) {
    $match = Hash::check($testPass, $user->password);
    echo "  '{$testPass}': " . ($match ? "✓ MATCH" : "✗ no match") . "\n";
}
