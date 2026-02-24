<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$username = $argv[1] ?? 'Robert';
$password = $argv[2] ?? null;
$password = $password ?: config('chikintayo.default_password');
$hashed = app('hash')->make($password);

use Illuminate\Support\Facades\DB;

$updated = DB::table('users')->where('username', $username)->update([
    'password' => $hashed,
    'must_change_password' => 1,
]);

echo json_encode(['result' => $updated ? 'ok' : 'not_updated', 'username' => $username]) . PHP_EOL;
