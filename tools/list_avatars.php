<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$users = DB::table('users')->select('id','username','avatar_url')->get();

foreach ($users as $u) {
    echo sprintf("%d\t%s\t%s\n", $u->id, $u->username ?? '-', $u->avatar_url ?? 'NULL');
}
