<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateMissingDishProducts extends Migration
{
    public function up()
    {
        // For all approved dishes, ensure a representative product exists so Admins can publish it
        $dishes = DB::table('dishes')->where('approval_status', 'approved')->get();

        foreach ($dishes as $dish) {
            $exists = DB::table('products')
                ->where('branch_id', $dish->branch_id)
                ->whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($dish->name))])
                ->exists();

            if ($exists) continue;

            // Compute simple cost and stock based on ingredients if available
            $ingredients = DB::table('dish_ingredients')->where('dish_id', $dish->id)->get();
            $costSum = 0.0;
            $maxServings = null;

            foreach ($ingredients as $ing) {
                if (empty($ing->product_id) || empty($ing->per_serving) || $ing->per_serving <= 0) {
                    $maxServings = 0;
                    break;
                }
                $prod = DB::table('products')->where('id', $ing->product_id)->first();
                if (!$prod) { $maxServings = 0; break; }
                $perServing = (float) ($ing->per_serving ?? 1);
                $perPackMode = in_array($prod->per_pack_or_individual, ['per_pack', 'both']);
                $packQty = (float) ($prod->pack_quantity ?? 0);
                if ($perPackMode && $packQty > 0) {
                    $openUsed = (float) ($prod->open_pack_used ?? 0);
                    $totalPieces = (($prod->stock ?? 0) * $packQty) - $openUsed;
                    $possibleByIng = (int) floor($totalPieces / max(1, $perServing));
                } else {
                    $possibleByIng = (int) floor(((float) ($prod->stock ?? 0)) / max(1, $perServing));
                }
                $maxServings = is_null($maxServings) ? $possibleByIng : min($maxServings, $possibleByIng);
                $unitCost = (float) ($prod->cost_price ?? $prod->price ?? 0);
                $costSum += ($unitCost * (float) $ing->per_serving);
            }

            $maxServings = (int) ($maxServings ?? 0);
            $sellingPrice = 0;
            if ($maxServings > 0 && $costSum > 0) {
                $sellingPrice = round($costSum * 1.20, 2);
            }

            // generate SKU
            $skuBase = strtoupper(substr(preg_replace('/[^A-Z0-9]+/i', '', $dish->name), 0, 8));
            if ($skuBase === '') $skuBase = 'DISH';
            do {
                $sku = $skuBase . '-' . strtoupper(Str::random(4));
            } while (DB::table('products')->where('sku', $sku)->exists());

            DB::table('products')->insert([
                'name' => $dish->name,
                'slug' => Str::slug($dish->name),
                'price' => $sellingPrice,
                'cost_price' => $costSum > 0 ? round($costSum, 2) : null,
                'stock' => $maxServings,
                'min_stock' => 0,
                'sku' => $sku,
                'branch_id' => $dish->branch_id,
                'is_published' => 0,
                'is_dish_product' => 1,
                'has_been_ordered' => 0,
                'is_active' => 1,
                'is_kitchen_dish' => 1,
                'logistics_request_available' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        // noop: don't remove products automatically on rollback
    }
}
