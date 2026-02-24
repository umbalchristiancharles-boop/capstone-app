<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$columns = DB::select('DESCRIBE product_comments');
foreach ($columns as $col) {
    if ($col->Field === 'rating') {
        echo "Rating column:\n";
        echo "  Type: " . $col->Type . "\n";
        echo "  Null: " . $col->Null . "\n";
        echo "  Default: " . $col->Default . "\n";
    }
}
