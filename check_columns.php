<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $columns = DB::select('SHOW COLUMNS FROM product_comments');
    
    echo "Columns in product_comments table:\n";
    echo "====================================\n\n";
    
    foreach ($columns as $col) {
        echo "{$col->Field} - {$col->Type}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
