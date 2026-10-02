<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\Product;
use Illuminate\Support\Collection;

class DishIngredientInventory
{
    public static function convertQuantity(float $quantity, ?string $fromUnit, ?string $toUnit): ?float
    {
        $from = self::unitDefinition($fromUnit);
        $to = self::unitDefinition($toUnit);

        if ($from['group'] !== $to['group']) {
            return null;
        }

        return $quantity * $from['factor'] / $to['factor'];
    }

    public function calculate(Dish $dish, int $branchId, array $preferredProductIds = []): array
    {
        $cost = 0.0;
        $maxServings = null;

        foreach ($dish->ingredients->values() as $index => $ingredient) {
            $required = (float) ($ingredient->per_serving ?? 0);
            if ($required <= 0) {
                $required = 1;
            }
            $recipeUnit = $this->recipeUnit($ingredient);
            $candidates = $this->candidateProducts($ingredient, $branchId, false, $preferredProductIds[$index] ?? null);

            if ($candidates->isEmpty()) {
                return [0, 0.0];
            }

            $available = 0.0;
            $weightedUnitCost = 0.0;
            $costWeight = 0.0;
            $fallbackUnitCosts = [];

            foreach ($candidates as $product) {
                $unit = $this->inventoryUnit($product, $ingredient);
                $availableInRecipeUnit = $this->availableInRecipeUnit($product, $unit, $recipeUnit);
                $costPerRecipeUnit = $this->costPerRecipeUnit($product, $unit, $recipeUnit);

                if ($availableInRecipeUnit === null || $costPerRecipeUnit === null) {
                    continue;
                }

                $available += $availableInRecipeUnit;
                $weightedUnitCost += $costPerRecipeUnit * max($availableInRecipeUnit, 0);
                $costWeight += max($availableInRecipeUnit, 0);
                $fallbackUnitCosts[] = $costPerRecipeUnit;
            }

            if (!$fallbackUnitCosts) {
                return [0, 0.0];
            }

            $possible = (int) floor($available / $required);
            $maxServings = $maxServings === null ? $possible : min($maxServings, $possible);
            $unitCost = $costWeight > 0
                ? $weightedUnitCost / $costWeight
                : array_sum($fallbackUnitCosts) / count($fallbackUnitCosts);
            $cost += $unitCost * $required;
        }

        return [(int) ($maxServings ?? 0), $cost];
    }

    public function consume(Dish $dish, int $branchId, int $servings): bool
    {
        foreach ($dish->ingredients as $ingredient) {
            $perServing = (float) ($ingredient->per_serving ?? 0);
            if ($perServing <= 0) {
                $perServing = 1;
            }
            $requiredInRecipeUnit = $perServing * $servings;

            $recipeUnit = $this->recipeUnit($ingredient);
            $candidates = $this->candidateProducts($ingredient, $branchId, true);
            $needed = $requiredInRecipeUnit;

            foreach ($candidates as $product) {
                $unit = $this->inventoryUnit($product, $ingredient);
                $factor = self::convertQuantity(1, $unit, $recipeUnit);
                if ($factor === null || $factor <= 0) {
                    continue;
                }

                $packMode = in_array($product->per_pack_or_individual, ['per_pack', 'both'], true)
                    && (float) ($product->pack_quantity ?? 0) > 0;
                $packQuantity = $packMode ? (float) $product->pack_quantity : 1.0;
                $openUsed = (float) ($product->open_pack_used ?? 0);
                $availableInInventoryUnit = max(0, ((float) $product->stock * $packQuantity) - $openUsed);
                $availableInRecipeUnit = $availableInInventoryUnit * $factor;
                $takeInRecipeUnit = min($needed, $availableInRecipeUnit);
                $takeInInventoryUnit = $takeInRecipeUnit / $factor;

                if ($takeInInventoryUnit <= 0) {
                    continue;
                }

                $afterUse = $openUsed + $takeInInventoryUnit;
                $wholePacksUsed = (int) floor(($afterUse + 0.0000001) / $packQuantity);
                $product->stock = max(0, (int) $product->stock - $wholePacksUsed);
                $product->open_pack_used = max(0, $afterUse - ($wholePacksUsed * $packQuantity));
                $product->save();
                Product::recomputeRealStockForGroup($branchId, $product->sku, $product->name);

                $needed -= $takeInRecipeUnit;
                if ($needed <= 0.000001) {
                    break;
                }
            }

            if ($needed > 0.000001) {
                return false;
            }
        }

        return true;
    }

    private function candidateProducts(DishIngredient $ingredient, int $branchId, bool $lock = false, ?int $preferredProductId = null): Collection
    {
        if ($preferredProductId !== null) {
            $query = Product::where('branch_id', $branchId)
                ->where('is_active', 1)
                ->where('id', $preferredProductId);
            if ($lock) {
                $query->lockForUpdate();
            }

            return $query->get();
        }

        $name = trim((string) $ingredient->name);
        $sku = $ingredient->product?->sku;
        $query = Product::where('branch_id', $branchId)
            ->where('is_active', 1)
            ->where(function ($query) use ($sku, $name) {
                if ($sku) {
                    $query->orWhere('sku', $sku);
                }
                $query->orWhere('name', 'like', '%' . str_replace(' ', '%', $name) . '%');
            });

        if ($lock) {
            $query->lockForUpdate();
        }

        $products = $query->get();
        $normalizedName = preg_replace('/[^A-Z0-9]+/', '', strtoupper($name));
        $exactMatches = $products->filter(function (Product $product) use ($normalizedName) {
            return preg_replace('/[^A-Z0-9]+/', '', strtoupper((string) $product->name)) === $normalizedName;
        });

        return $exactMatches->isNotEmpty() ? $exactMatches : $products;
    }

    private function recipeUnit(DishIngredient $ingredient): string
    {
        return trim((string) ($ingredient->unit ?: $ingredient->product?->unit ?: 'pcs'));
    }

    private function inventoryUnit(Product $product, DishIngredient $ingredient): string
    {
        $hasPackQuantity = in_array($product->per_pack_or_individual, ['per_pack', 'both'], true)
            && (float) ($product->pack_quantity ?? 0) > 0;

        return trim((string) ($hasPackQuantity
            ? ($product->pack_unit ?: $product->unit ?: $ingredient->product?->unit ?: $ingredient->unit ?: 'pcs')
            : ($product->unit ?: $product->pack_unit ?: $ingredient->product?->unit ?: $ingredient->unit ?: 'pcs')));
    }

    private function availableInRecipeUnit(Product $product, string $inventoryUnit, string $recipeUnit): ?float
    {
        $factor = self::convertQuantity(1, $inventoryUnit, $recipeUnit);
        if ($factor === null) {
            return null;
        }

        $packMode = in_array($product->per_pack_or_individual, ['per_pack', 'both'], true)
            && (float) ($product->pack_quantity ?? 0) > 0;
        $packQuantity = $packMode ? (float) $product->pack_quantity : 1.0;
        $inventoryAvailable = max(0, ((float) $product->stock * $packQuantity) - (float) ($product->open_pack_used ?? 0));

        return $inventoryAvailable * $factor;
    }

    private function costPerRecipeUnit(Product $product, string $inventoryUnit, string $recipeUnit): ?float
    {
        $factor = self::convertQuantity(1, $inventoryUnit, $recipeUnit);
        if ($factor === null || $factor <= 0) {
            return null;
        }

        $unitCost = (float) ($product->cost_price ?? $product->price ?? 0);
        if (in_array($product->per_pack_or_individual, ['per_pack', 'both'], true)
            && (float) ($product->pack_quantity ?? 0) > 0) {
            $unitCost /= (float) $product->pack_quantity;
        }

        return $unitCost / $factor;
    }

    private static function unitDefinition(?string $unit): array
    {
        $normalized = strtolower(trim((string) $unit));

        $units = [
            'mg' => ['group' => 'mass', 'factor' => 0.001],
            'g' => ['group' => 'mass', 'factor' => 1],
            'gram' => ['group' => 'mass', 'factor' => 1],
            'grams' => ['group' => 'mass', 'factor' => 1],
            'kg' => ['group' => 'mass', 'factor' => 1000],
            'kilogram' => ['group' => 'mass', 'factor' => 1000],
            'kilograms' => ['group' => 'mass', 'factor' => 1000],
            'ml' => ['group' => 'volume', 'factor' => 1],
            'milliliter' => ['group' => 'volume', 'factor' => 1],
            'milliliters' => ['group' => 'volume', 'factor' => 1],
            'l' => ['group' => 'volume', 'factor' => 1000],
            'liter' => ['group' => 'volume', 'factor' => 1000],
            'liters' => ['group' => 'volume', 'factor' => 1000],
            'pcs' => ['group' => 'count', 'factor' => 1],
            'pc' => ['group' => 'count', 'factor' => 1],
            'piece' => ['group' => 'count', 'factor' => 1],
            'pieces' => ['group' => 'count', 'factor' => 1],
            'unit' => ['group' => 'count', 'factor' => 1],
            'units' => ['group' => 'count', 'factor' => 1],
        ];

        return $units[$normalized] ?? ['group' => 'unit:' . $normalized, 'factor' => 1];
    }
}