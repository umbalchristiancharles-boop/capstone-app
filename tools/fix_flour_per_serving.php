<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = Illuminate\Support\Facades\DB::table('dish_ingredients')
    ->whereRaw('TRIM(UPPER(name)) = ?', ['FLOUR'])
    ->update(['per_serving' => 1]);

echo "updated_rows={$count}" . PHP_EOL;
