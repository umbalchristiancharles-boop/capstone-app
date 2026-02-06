<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = DB::table('users')
    ->whereNull('deleted_at')
    ->orderBy('role')
    ->orderBy('username')
    ->get(['id', 'username', 'email', 'role', 'full_name', 'is_active']);

echo "==========================================\n";
echo "ACTIVE USERS IN SYSTEM\n";
echo "==========================================\n\n";

foreach ($users as $user) {
    echo "Username: {$user->username}\n";
    echo "  Email: {$user->email}\n";
    echo "  Full Name: {$user->full_name}\n";
    echo "  Role: {$user->role}\n";
    echo "  Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
    echo "  ---\n";
}

echo "\n==========================================\n";
echo "RECOMMENDED TEST CREDENTIALS\n";
echo "==========================================\n";
echo "After password reset, use these:\n\n";
echo "Paul Berrer / Paul@123\n";
echo "admin / Admin123! (if exists)\n";
echo "\n";
