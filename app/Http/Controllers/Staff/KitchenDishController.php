<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\Product;
use App\Models\ProcurementRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KitchenDishController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Dish::with('ingredients.product');
        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        $dishes = $query->orderBy('created_at', 'desc')->get();
        return response()->json($dishes);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string',
            'ingredients' => 'nullable|array',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.brand' => 'nullable|string',
            'ingredients.*.unit' => 'nullable|string',
            'ingredients.*.per_serving' => 'nullable|numeric',
            'ingredients.*.product_id' => 'nullable|integer',
        ]);

        // Create dish with pending_approval status - requires owner confirmation before appearing in logistics
        $dish = Dish::create([
            'name' => $validated['name'],
            'created_by' => $user->id ?? null,
            'branch_id' => $user->branch_id ?? null,
            'status' => 'draft',
            'approval_status' => 'pending_approval',
        ]);

        // Create ingredients without creating placeholder products or flagging for logistics yet
        // The products and logistics requests will be handled after owner approves the dish
        $ingredients = $validated['ingredients'] ?? [];
        foreach ($ingredients as $ing) {
            $productId = $ing['product_id'] ?? null;
            $product = null;
            if ($productId) {
                $product = Product::find($productId);
                if (!$product) {
                    $productId = null;
                }
            }

            // Only create existing products, don't create placeholder products yet
            // Placeholders will be created when owner approves the dish

            DishIngredient::create([
                'dish_id' => $dish->id,
                'product_id' => $productId,
                'name' => $ing['name'],
                'brand' => $ing['brand'] ?? null,
                'unit' => $ing['unit'] ?? null,
                'per_serving' => $ing['per_serving'] ?? null,
            ]);
        }

        return response()->json(Dish::with('ingredients.product')->find($dish->id));
    }

    public function produce(Request $request, $id)
    {
        $request->validate(['servings' => 'required|integer|min:1']);

        $dish = Dish::with('ingredients.product')->findOrFail($id);
        $servings = (int)$request->input('servings');

        $shortages = [];
        $manual = [];

        \DB::beginTransaction();
        try {
            foreach ($dish->ingredients as $ing) {
                $per = (float)($ing->per_serving ?? 0);
                $required = $per * $servings;

                if ($per <= 0) {
                    $manual[] = [
                        'ingredient_id' => $ing->id,
                        'name' => $ing->name,
                        'note' => 'No numeric per_serving set; manual stock adjustment required',
                    ];
                    if ($ing->product) {
                        $ing->product->update(['logistics_request_available' => true]);
                    }
                    continue;
                }

                if ($ing->product) {
                    $product = $ing->product;

                    // If product is supplied per-pack, track piece-level consumption
                    $perPackMode = in_array($product->per_pack_or_individual, ['per_pack', 'both']);
                    $packQty = (float)($product->pack_quantity ?? 0);

                    if ($perPackMode && $packQty > 0) {
                        $openUsed = (float)($product->open_pack_used ?? 0);
                        // Total pieces available = (stock * packQty) - already consumed pieces from open pack
                        $totalPiecesAvailable = ($product->stock * $packQty) - $openUsed;

                        if ($totalPiecesAvailable >= $required) {
                            $totalAfter = $openUsed + $required;
                            $packsToConsume = (int)floor($totalAfter / $packQty);
                            $remainingOpenUsed = $totalAfter - ($packsToConsume * $packQty);

                            if ($packsToConsume > 0) {
                                $product->decrement('stock', $packsToConsume);
                            }

                            $product->open_pack_used = $remainingOpenUsed;
                            $product->save();
                        } else {
                            $product->update(['logistics_request_available' => true]);
                            $shortages[] = [
                                'product_id' => $product->id,
                                'name' => $product->name,
                                'required' => $required,
                                'available' => max(0, $totalPiecesAvailable),
                                'short' => max(0, $required - $totalPiecesAvailable),
                            ];
                        }
                    } else {
                        // Individual mode: stock stored as pieces
                        $have = (float)($product->stock ?? 0);
                        if ($have >= $required) {
                            $product->decrement('stock', $required);
                        } else {
                            $product->update(['logistics_request_available' => true]);
                            $shortages[] = [
                                'product_id' => $product->id,
                                'name' => $product->name,
                                'required' => $required,
                                'available' => $have,
                                'short' => max(0, $required - $have),
                            ];
                        }
                    }
                } else {
                    $shortages[] = [
                        'product_id' => null,
                        'name' => $ing->name,
                        'required' => $required,
                        'available' => 0,
                        'short' => $required,
                    ];
                }
            }

            if (!empty($shortages)) {
                \DB::rollBack();
                return response()->json(['ok' => false, 'message' => 'Insufficient stock for some ingredients', 'shortages' => $shortages, 'manual' => $manual], 400);
            }

            \DB::commit();
            return response()->json(['ok' => true, 'message' => 'Produced successfully, stocks updated', 'manual' => $manual]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['ok' => false, 'message' => 'Failed to produce servings', 'error' => $e->getMessage()], 500);
        }
    }

    public function markLowStock(Request $request, $ingredientId)
    {
        $user = $request->user();
        $validated = $request->validate([
            'unit' => 'nullable|in:pcs,g,kg,ml,l,pack'
        ]);

        $ing = DishIngredient::with('product')->findOrFail($ingredientId);

        // Ensure product exists; if not, create placeholder
        $product = $ing->product;
        if (!$product) {
            $slug = Str::slug($ing->name . '-' . time());
            $product = Product::create([
                'name' => $ing->name,
                'slug' => $slug,
                'price' => 0,
                'cost_price' => 0,
                'stock' => 0,
                'min_stock' => 0,
                'sku' => 'KITCHEN-'.time().'-'.mt_rand(100,999),
                'branch_id' => $user->branch_id,
                'supplier_name' => 'KITCHEN',
                'supplier_id' => null,
                'is_published' => false,
                'is_active' => true,
                'is_kitchen_dish' => false,
                'has_been_ordered' => false,
                'logistics_request_available' => true,
            ]);
            $ing->update(['product_id' => $product->id]);
        }

        $branchId = $product->branch_id ?? $user->branch_id ?? 1;

        // Instead of creating a procurement request directly from Kitchen, flag
        // the product so Logistics can review and create the procurement request.
        try {
            $product->update(['logistics_request_available' => true]);
        } catch (\Exception $e) {
            Log::warning('Failed to flag product for logistics request', ['product_id' => $product->id ?? null, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to flag low stock'], 500);
        }

        return response()->json(['ok' => true, 'message' => 'Low-stock flagged for Logistics review']);
    }
}
