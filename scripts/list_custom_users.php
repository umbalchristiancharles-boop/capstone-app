<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::where('role', 'CUSTOM')->get();
$out = [];
foreach ($users as $u) {
    $out[] = [
        'id' => $u->id,
        'username' => $u->username,
        'branch_id' => $u->branch_id,
        'permissions' => $u->permissions,
    ];
}

echo json_encode($out, JSON_PRETTY_PRINT);
