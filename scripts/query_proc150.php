<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $r = DB::table('procurement_requests')->where('id', 150)->first();
    if ($r) {
        echo "Found procurement request:\n";
        print_r($r);
    } else {
        echo "No procurement request with id=150 found.\n";
    }
} catch (Exception $e) {
    echo "Error querying DB: " . $e->getMessage() . "\n";
}
