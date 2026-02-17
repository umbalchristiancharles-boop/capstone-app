<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$username = 'owner_ph';
$newRole = 'OWNER';

$user = DB::table('users')->where('username', $username)->first();
if (! $user) {
    echo "No user found with username: $username\n";
    exit(1);
}

DB::table('users')->where('id', $user->id)->update([
    'role' => $newRole,
    'updated_at' => now(),
]);

echo "Role updated to $newRole for $username.\n";
