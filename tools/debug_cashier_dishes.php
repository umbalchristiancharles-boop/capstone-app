<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$branchId = 31;

$dishes = App\Models\Dish::where('branch_id', $branchId)
    ->where('status', 'active')
    ->with(['ingredients.product'])
    ->orderBy('name')
    ->get();

foreach ($dishes as $dish) {
    $costSum = 0.0;
    $maxServings = null;
    $available = true;

    echo "Dish: {$dish->name}\n";

    foreach ($dish->ingredients as $ing) {
        $perServing = (float) ($ing->per_serving ?? 0);
        $ingProd = $ing->product;

        if (!$ingProd || $perServing <= 0) {
            $available = false;
            echo "  - {$ing->name}: invalid link or per_serving ({$perServing})\n";
            continue;
        }

        $possibleByIng = (int) floor(((float) $ingProd->stock) / $perServing);
        $maxServings = is_null($maxServings) ? $possibleByIng : min($maxServings, $possibleByIng);

        $unitCost = (float) ($ingProd->cost_price ?? $ingProd->price ?? 0);
        $costSum += ($unitCost * $perServing);

        echo "  - {$ingProd->name}: stock={$ingProd->stock}, per_serving={$perServing}, possible={$possibleByIng}\n";
    }

    $maxServings = (int) ($maxServings ?? 0);
    echo "  => available=" . ($available ? 'yes' : 'no') . ", max_servings={$maxServings}, computed_cost=" . round($costSum, 2) . "\n\n";
}
