<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = DB::table('users')->whereIn('username', ['Gabby', 'manager', 'staff'])->get(['id', 'username', 'email', 'role']);

echo "Found users:\n";
foreach ($users as $u) {
    echo "  ID: $u->id | Username: '$u->username' | Role: $u->role\n";
}

// Now reset Gabby's password
$gabby = DB::table('users')->where('username', 'Gabby')->first();
if ($gabby) {
    DB::table('users')->where('id', $gabby->id)->update([
        'password' => Hash::make('Gabby@123'),
        'must_change_password' => true,
        'updated_at' => now(),
    ]);
    
    $verify = DB::table('users')->where('id', $gabby->id)->first();
    echo "\n✓ Gabby password reset: " . (Hash::check('Gabby@123', $verify->password) ? "SUCCESS" : "FAILED") . "\n";
}

// Reset manager password
$manager = DB::table('users')->where('username', 'manager')->first();
if ($manager) {
    DB::table('users')->where('id', $manager->id)->update([
        'password' => Hash::make('Manager123!'),
        'must_change_password' => true,
        'updated_at' => now(),
    ]);
    
    $verify = DB::table('users')->where('id', $manager->id)->first();
    echo "✓ manager password reset: " . (Hash::check('Manager123!', $verify->password) ? "SUCCESS" : "FAILED") . "\n";
}

// Reset staff password
$staff = DB::table('users')->where('username', 'staff')->first();
if ($staff) {
    DB::table('users')->where('id', $staff->id)->update([
        'password' => Hash::make('Staff123!'),
        'must_change_password' => true,
        'updated_at' => now(),
    ]);
    
    $verify = DB::table('users')->where('id', $staff->id)->first();
    echo "✓ staff password reset: " . (Hash::check('Staff123!', $verify->password) ? "SUCCESS" : "FAILED") . "\n";
}
