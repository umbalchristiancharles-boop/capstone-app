<?php
/**
 * Script to fix manager/HR users with must_change_password=1
 * Run: php fix_manager_password.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== Fix Manager/HR Password Issues ===\n\n";

// Get default password from config
$defaultPassword = config('chikintayo.default_password', 'ChikinTayo_2526');
$hashedPassword = Hash::make($defaultPassword);

echo "Default password: {$defaultPassword}\n";
echo "Hashed: {$hashedPassword}\n\n";

// Find users with must_change_password=1
$users = DB::table('users')
    ->where('must_change_password', 1)
    ->get();

echo "Found " . count($users) . " users with must_change_password=1:\n";

foreach ($users as $user) {
    echo "- ID: {$user->id}, Username: {$user->username}, Role: {$user->role}\n";
}

if (count($users) > 0) {
    echo "\nUpdating these users...\n";

    // Update users to reset password to default and require change
    DB::table('users')
        ->where('must_change_password', 1)
        ->update([
            'password' => $hashedPassword,
            'must_change_password' => 0, // Allow login without password change
            'updated_at' => now(),
        ]);

    echo "Done! Updated " . count($users) . " users.\n";
} else {
    echo "\nNo users need fixing.\n";
}

// Also ensure all users have is_active = 1 for testing
echo "\n=== User Status ===\n";
$allUsers = DB::table('users')
    ->select('id', 'username', 'role', 'is_active', 'must_change_password')
    ->get();

foreach ($allUsers as $user) {
    echo "ID: {$user->id}, Username: {$user->username}, Role: {$user->role}, Active: {$user->is_active}, MustChange: {$user->must_change_password}\n";
}

echo "\n=== Complete ===\n";
