<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$username = $argv[1] ?? 'Robert';
$user = App\Models\User::where('username', $username)->first();
if (! $user) {
    echo json_encode(['error' => 'not_found', 'username' => $username]) . PHP_EOL;
    exit(0);
}

$default = config('chikintayo.default_password');
$hashCheck = app('hash')->check($default, $user->password);

echo json_encode([
    'id' => $user->id,
    'username' => $user->username,
    'role' => $user->role,
    'department' => $user->department,
    'password' => $user->password,
    'is_active' => $user->is_active,
    'must_change_password' => $user->must_change_password,
    'hash_check' => $hashCheck,
]) . PHP_EOL;
