<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$id = $argv[1] ?? null;
$status = $argv[2] ?? null;
if (!$id || !$status) {
    echo "Usage: php set_procurement_status.php <id> <status>\n";
    exit(1);
}

try {
    $r = DB::table('procurement_requests')->where('id', $id)->first();
    if (!$r) {
        echo "No procurement request with id={$id} found.\n";
        exit(1);
    }
    DB::table('procurement_requests')->where('id', $id)->update(['status' => $status, 'updated_at' => now()]);
    echo "Updated procurement request id={$id} to status={$status}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
