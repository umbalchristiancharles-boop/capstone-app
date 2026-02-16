<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "Resetting OWNER password...\n";

$owner = DB::table('users')->where('role', 'OWNER')->first();
if (! $owner) {
    echo "No OWNER account found.\n";
    exit(1);
}

$new = 'ChikinTayo_2526';
DB::table('users')->where('id', $owner->id)->update([
    'password' => Hash::make($new),
    'must_change_password' => 1,
    'updated_at' => now(),
]);

$verify = DB::table('users')->where('id', $owner->id)->first();
if (Hash::check($new, $verify->password)) {
    echo "✓ Owner password reset to: $new\n";
    echo "Username/email: " . ($verify->username ?? $verify->email) . "\n";
    exit(0);
}

echo "✗ Failed to reset password.\n";
exit(1);
