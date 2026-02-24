<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$username = $argv[1] ?? 'Robert';
$password = $argv[2] ?? null;
$password = $password ?: config('chikintayo.default_password');
$user = App\Models\User::where('username', $username)->first();
if (! $user) {
    echo json_encode(['error' => 'not_found', 'username' => $username]) . PHP_EOL;
    exit(0);
}

$user->password = app('hash')->make($password);
$user->must_change_password = 1;
$user->save();

echo json_encode(['result' => 'ok', 'username' => $user->username, 'id' => $user->id]) . PHP_EOL;
