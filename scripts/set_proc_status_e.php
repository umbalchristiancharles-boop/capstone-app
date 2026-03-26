<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProcurementRequest;

$id = $argv[1] ?? null;
$status = $argv[2] ?? null;
if (!$id || !$status) {
    echo "Usage: php set_proc_status_e.php <id> <status>\n";
    exit(1);
}

try {
    $proc = ProcurementRequest::find($id);
    if (!$proc) {
        echo "No procurement request with id={$id} found.\n";
        exit(1);
    }
    $proc->status = $status;
    $proc->updated_at = now();
    $proc->save();
    echo "Updated procurement request id={$id} to status={$status}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
