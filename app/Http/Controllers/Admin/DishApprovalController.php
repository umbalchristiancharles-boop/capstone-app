<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DishApprovalController extends Controller
{
    /**
     * Get all pending dishes awaiting owner approval
     */
    public function pendingDishes(Request $request)
    {
        $user = $request->user();
        
        // Owner can only see dishes from their branch
        $query = Dish::where('approval_status', 'pending_approval')
            ->with('ingredients.product', 'creator');
        
        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        
        $dishes = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'ok' => true,
            'data' => $dishes,
            'count' => $dishes->count(),
        ]);
    }

    /**
     * Get all approved dishes
     */
    public function approvedDishes(Request $request)
    {
        $user = $request->user();
        
        $query = Dish::where('approval_status', 'approved')
            ->with('ingredients.product', 'creator', 'approver');
        
        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        
        $dishes = $query->orderBy('approved_at', 'desc')->get();
        
        return response()->json([
            'ok' => true,
            'data' => $dishes,
            'count' => $dishes->count(),
        ]);
    }

    /**
     * Approve a pending dish and trigger ingredient/product creation for logistics
     */
    public function approveDish(Request $request, $id)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'publish_products' => 'sometimes|boolean',
            'per_pack_or_individual' => 'sometimes|in:individual,per_pack,both',
            'pack_quantity' => 'nullable|numeric|min:0',
            'pack_unit' => 'nullable|string|max:80',
        ]);

        $dish = Dish::with('ingredients.product')->findOrFail($id);

        // Verify the dish is pending approval
        if ($dish->approval_status !== 'pending_approval') {
            return response()->json([
                'ok' => false,
                'message' => 'This dish is not pending approval',
            ], 400);
        }

        // Verify branch authorization
        if ($user->branch_id && $dish->branch_id !== $user->branch_id) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized to approve dishes from another branch',
            ], 403);
        }

        try {
            $role = strtoupper($user->role ?? '') ;
            $canPublish = in_array($role, ['ADMIN', 'SUPER_ADMIN']);
            $autoPublishOnOwner = ($role === 'OWNER');
            \DB::beginTransaction();

            // Mark dish as approved
            $dish->update([
                'approval_status' => 'approved',
                'status' => 'active',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'approval_notes' => $validated['notes'] ?? null,
            ]);

            // Now create placeholder products for ingredients that don't have products
            // but first attempt tolerant matching to existing supplier products to avoid duplicates.
            foreach ($dish->ingredients as $ing) {
                // If ingredient already linked, ensure it's flagged for logistics
                if ($ing->product_id && $ing->product) {
                    try {
                        $ing->product->update(['logistics_request_available' => true]);
                    } catch (\Exception $e) {
                        Log::warning('Failed to flag existing ingredient product for logistics', ['ingredient_id' => $ing->id, 'error' => $e->getMessage()]);
                    }
                    continue;
                }

                // Attempt to find matching products in branch by SKU, exact name, or fuzzy token match
                try {
                    $branchId = $dish->branch_id ?? null;
                    $nameRaw = trim((string) $ing->name);
                    $nameUpper = strtoupper($nameRaw);
                    $normalized = preg_replace('/[^A-Z0-9]+/', '', $nameUpper);

                    $candidates = Product::where('branch_id', $branchId)->where('is_active', 1)
                        ->where(function ($q) use ($ing, $nameRaw) {
                            if (!empty($ing->product_id) && !is_null($ing->product_id)) {
                                $q->orWhere('id', $ing->product_id);
                            }
                            $q->orWhere('sku', $ing->product?->sku ?? '');
                            $q->orWhere('name', 'like', '%' . str_replace(' ', '%', $nameRaw) . '%');
                        })
                        ->get();

                    // Normalize candidate names and pick best match: prefer published, then highest stock
                    $matches = $candidates->filter(function ($p) use ($normalized) {
                        $pn = strtoupper($p->name ?? '');
                        $pnNorm = preg_replace('/[^A-Z0-9]+/', '', $pn);
                        return $pnNorm === $normalized || similar_text($pn, $normalized) > 0 || soundex($pn) === soundex($normalized);
                    });

                    if ($matches->isEmpty() && $candidates->isNotEmpty()) {
                        // fallback to candidate list (less strict)
                        $matches = $candidates;
                    }

                    if ($matches->isNotEmpty()) {
                        // choose best: published first, then by stock desc
                        $best = $matches->sortByDesc(function ($p) {
                            return (($p->is_published ? 1 : 0) * 1000000) + ($p->stock ?? 0);
                        })->first();

                        if ($best) {
                            $ing->product_id = $best->id;
                            $ing->save();
                            // ensure logistics flag
                            try { $best->update(['logistics_request_available' => true]); } catch (\Throwable $t) {}
                            Log::info('Auto-linked ingredient to existing product during approval', ['dish_id' => $dish->id, 'ingredient_id' => $ing->id, 'product_id' => $best->id]);
                            continue;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Ingredient matching lookup failed', ['ingredient' => $ing->name, 'error' => $e->getMessage()]);
                }

                // If no match found, create placeholder product
                try {
                    $slug = Str::slug($ing->name . '-' . $dish->id . '-' . time());
                    $product = Product::create([
                        'name' => $ing->name,
                        'slug' => $slug,
                        'price' => 0,
                        'cost_price' => 0,
                        'stock' => 0,
                        'min_stock' => 0,
                        'sku' => 'KITCHEN-DISH-' . $dish->id . '-' . mt_rand(1000, 9999),
                        'branch_id' => $dish->branch_id ?? null,
                        'supplier_name' => 'KITCHEN',
                        'supplier_id' => null,
                        // Ingredients should never be published automatically when creating placeholders.
                        'is_published' => false,
                        'is_active' => true,
                        'is_kitchen_dish' => true,
                        'has_been_ordered' => false,
                        'logistics_request_available' => true,
                        'per_pack_or_individual' => $canPublish ? ($validated['per_pack_or_individual'] ?? 'individual') : 'individual',
                        'pack_quantity' => $canPublish ? ($validated['pack_quantity'] ?? null) : null,
                        'pack_unit' => $canPublish ? ($validated['pack_unit'] ?? null) : null,
                    ]);

                    // Update ingredient with the newly created product
                    $ing->update(['product_id' => $product->id]);

                    Log::info('Created placeholder product for approved dish ingredient', [
                        'dish_id' => $dish->id,
                        'ingredient_id' => $ing->id,
                        'product_id' => $product->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create placeholder product for dish ingredient', [
                        'dish_id' => $dish->id,
                        'ingredient' => $ing->name,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue processing other ingredients
                }
            }

            // Create or update a representative dish Product so Admins can manage/publish it.
            // If an ADMIN requested publishing, mark it published; otherwise keep unpublished.
            $publishRequested = ($canPublish && !empty($validated['publish_products'])) ? true : false;

            try {
                $costSum = 0.0;
                $maxServings = null;
                foreach ($dish->ingredients as $ing) {
                        // Aggregate across all products for this ingredient (by SKU or normalized name)
                        $perServing = (float) ($ing->per_serving ?? 1);
                        if ($perServing <= 0) $perServing = 1;

                        $nameKey = trim(strtoupper($ing->name ?? ''));
                        $skuKey = $ing->product?->sku ?? null;

                        $candidateQuery = Product::where('branch_id', $dish->branch_id ?? null)->where('is_active', 1);
                        $candidateQuery->where(function ($q) use ($nameKey, $skuKey) {
                            if ($skuKey) {
                                $q->orWhere('sku', $skuKey);
                            }
                            $q->orWhereRaw('TRIM(UPPER(name)) = ?', [$nameKey]);
                        });

                        $candidateProducts = $candidateQuery->get();
                        if ($candidateProducts->isEmpty()) {
                            $maxServings = 0;
                            break;
                        }

                        $totalPieces = 0;
                        $totalCost = 0.0;
                        $isCondiment = false;
                        foreach ($candidateProducts as $cp) {
                            $cat = strtolower(trim($cp->category ?? ''));
                            if ($cat === 'condiment') $isCondiment = true;

                            $perPackModeCp = in_array($cp->per_pack_or_individual, ['per_pack', 'both']);
                            $packQtyCp = (float) ($cp->pack_quantity ?? 0);
                            if ($perPackModeCp && $packQtyCp > 0) {
                                $openUsedCp = (float) ($cp->open_pack_used ?? 0);
                                $totalPieces += (($cp->stock ?? 0) * $packQtyCp) - $openUsedCp;
                            } else {
                                $totalPieces += (float) ($cp->stock ?? 0);
                            }

                            $totalCost += ((float) ($cp->cost_price ?? $cp->price ?? 0));
                        }

                        if ($isCondiment && $totalPieces <= 0) {
                            $costSum += ($totalCost * $perServing);
                            continue;
                        }

                        $possibleByIng = (int) floor($totalPieces / max(1, $perServing));
                        $maxServings = is_null($maxServings) ? $possibleByIng : min($maxServings, $possibleByIng);
                        $costSum += ($totalCost * $perServing);
                }

                $maxServings = (int) ($maxServings ?? 0);
                // Compute selling price if possible; otherwise default to 0
                $sellingPrice = 0;
                if ($maxServings > 0 && $costSum > 0) {
                    $markup = 1.20;
                    $sellingPrice = round($costSum * $markup, 2);
                }

                // Create or update the dish Product row for this branch and name
                    $dishProduct = Product::where('branch_id', $dish->branch_id)
                    ->whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($dish->name))])
                    ->first();

                    // Determine if we should publish immediately: Admin can request publish via payload,
                    // Owners auto-publish upon approval per requested behavior.
                    $publishRequested = ($canPublish && !empty($validated['publish_products'])) ? true : false;
                    if ($autoPublishOnOwner) {
                        $publishRequested = true;
                    }

                    if (!$dishProduct) {
                    // generate a SKU similar to cashier
                    $skuBase = strtoupper(substr(preg_replace('/[^A-Z0-9]+/i', '', $dish->name), 0, 8));
                    if ($skuBase === '') $skuBase = 'DISH';
                    do {
                        $sku = $skuBase . '-' . strtoupper(Str::random(4));
                    } while (Product::where('sku', $sku)->exists());
                                Product::create([
                        'name' => $dish->name,
                        'slug' => Str::slug($dish->name),
                        'price' => $sellingPrice,
                        'cost_price' => $costSum > 0 ? round($costSum, 2) : null,
                        'stock' => $maxServings,
                        'min_stock' => 0,
                        'sku' => $sku,
                                'branch_id' => $dish->branch_id ?? null,
                                'dish_id' => $dish->id,
                                    'is_dish_product' => true,
                            'is_published' => $publishRequested ? 1 : 0,
                            'published_by' => $publishRequested ? $user->id : null,
                            'published_at' => $publishRequested ? now() : null,
                        'has_been_ordered' => 0,
                        'is_active' => 1,
                        'is_kitchen_dish' => true,
                        'supplier_name' => null,
                        'supplier_id' => null,
                        'logistics_request_available' => false,
                    ]);
                } else {
                    $dishProduct->is_kitchen_dish = true;
                    $dishProduct->is_active = true;
                    $dishProduct->stock = $maxServings;
                    if ($sellingPrice > 0) $dishProduct->price = $sellingPrice;
                    if ($costSum > 0) $dishProduct->cost_price = round($costSum, 2);
                    // only set is_published true if admin explicitly requested publishing
                        if ($publishRequested) {
                            $wasPublished = (bool) $dishProduct->is_published;
                            $dishProduct->is_published = true;
                            if (!$wasPublished) {
                                $dishProduct->published_by = $user->id;
                                $dishProduct->published_at = now();
                            }
                        }
                    $dishProduct->save();
                }
            } catch (\Exception $e) {
                Log::error('Failed to create/update dish product during approval', [
                    'dish_id' => $dish->id,
                    'error' => $e->getMessage(),
                ]);
            }

            \DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Dish approved successfully. Ingredients are now visible in logistics panel.',
                'data' => Dish::with('ingredients.product', 'approver')->find($dish->id),
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Failed to approve dish', [
                'dish_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'ok' => false,
                'message' => 'Failed to approve dish: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject a pending dish
     */
    public function rejectDish(Request $request, $id)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $dish = Dish::findOrFail($id);

        // Verify the dish is pending approval
        if ($dish->approval_status !== 'pending_approval') {
            return response()->json([
                'ok' => false,
                'message' => 'This dish is not pending approval',
            ], 400);
        }

        // Verify branch authorization
        if ($user->branch_id && $dish->branch_id !== $user->branch_id) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized to reject dishes from another branch',
            ], 403);
        }

        try {
            \DB::beginTransaction();

            // Mark dish as rejected
            $dish->update([
                'approval_status' => 'rejected',
                'status' => 'inactive',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'approval_notes' => 'REJECTED: ' . $validated['reason'],
            ]);

            \DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Dish rejected successfully.',
                'data' => $dish,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Failed to reject dish', [
                'dish_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'ok' => false,
                'message' => 'Failed to reject dish: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Publish the representative product for an approved dish.
     * Only branch Admins / SUPER_ADMIN are allowed to publish.
     */
    public function publishDish(Request $request, $id)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        if (!in_array($role, ['ADMIN', 'SUPER_ADMIN', 'SUPERADMIN'])) {
            return response()->json(['ok' => false, 'message' => 'Only branch admins can publish dishes.'], 403);
        }

        $dish = Dish::findOrFail($id);

        // find representative product
        $product = Product::where('branch_id', $dish->branch_id)
            ->where('dish_id', $dish->id)
            ->first();

        if (!$product) {
            return response()->json(['ok' => false, 'message' => 'Representative product for this dish not found.'], 404);
        }

        $product->is_published = true;
        $product->published_by = $user->id;
        $product->published_at = now();
        $product->save();

        return response()->json(['ok' => true, 'message' => 'Dish product published.', 'product' => $product]);
    }

    /**
     * Owner creates a dish directly - no approval needed, affects all branches
     */
    public function ownerCreateDish(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        
        Log::info('ownerCreateDish called', [
            'user_id' => $user->id,
            'user_role' => $role,
            'dish_name' => $request->input('name'),
            'ingredients_count' => count($request->input('ingredients', [])),
        ]);
        
        // Only owner or admin can create dishes directly
        if (!in_array($role, ['OWNER', 'ADMIN', 'SUPER_ADMIN', 'SUPERADMIN'])) {
            Log::warning('Unauthorized attempt to create dish', ['role' => $role, 'user_id' => $user->id]);
            return response()->json(['ok' => false, 'message' => 'Only owners and admins can create dishes directly.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ingredients' => 'nullable|array',
            'ingredients.*.name' => 'required|string|max:255',
            'ingredients.*.brand' => 'nullable|string|max:255',
            'ingredients.*.unit' => 'nullable|string|max:50',
            'ingredients.*.per_serving' => 'nullable|numeric|min:0',
            'ingredients.*.product_id' => 'nullable|integer|exists:products,id',
        ]);

        $branches = \App\Models\Branch::where('is_active', true)->get();
        
        if ($branches->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'No active branches found.'], 400);
        }

        try {
            \DB::beginTransaction();

            // Create the dish for the owner's branch (or first branch if owner has no branch)
            $ownerBranchId = $user->branch_id ?? $branches->first()->id;
            
            $dish = Dish::create([
                'name' => $validated['name'],
                'created_by' => $user->id,
                'branch_id' => $ownerBranchId,
                'status' => 'active',
                'approval_status' => 'approved', // Auto-approve since owner created it
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Create ingredients and ensure products exist for inventory
            $ingredients = $validated['ingredients'] ?? [];
            $ingredientProducts = []; // Store product info to create in all branches
            
            foreach ($ingredients as $ing) {
                $productId = $ing['product_id'] ?? null;
                $product = null;
                
                if ($productId) {
                    $product = Product::find($productId);
                    if (!$product) {
                        $productId = null;
                    }
                }

                // If no product linked, create a placeholder product for inventory tracking
                if (!$product && !$productId) {
                    $ingredientName = trim($ing['name']);
                    if (!empty($ingredientName)) {
                        // Use unique name to avoid matching existing products
                        $uniqueIngredientName = $ingredientName . ' (Dish Ingredient)';
                        $slug = Str::slug($uniqueIngredientName . '-' . $dish->id . '-' . time());
                        $sku = 'ING-' . $dish->id . '-' . strtoupper(Str::random(6));
                        
                        $product = Product::create([
                            'name' => $uniqueIngredientName, // Use unique name from the start
                            'slug' => $slug,
                            'price' => 0,
                            'cost_price' => 0,
                            'stock' => 0, // Start with 0 stock
                            'min_stock' => 10,
                            'sku' => $sku,
                            'branch_id' => $ownerBranchId,
                            'supplier_name' => 'KITCHEN',
                            'supplier_id' => null,
                            'is_published' => false,
                            'is_active' => true,
                            'is_kitchen_dish' => false,
                            'has_been_ordered' => false,
                            'logistics_request_available' => true, // Flag for procurement
                            'per_pack_or_individual' => 'individual',
                            'pack_quantity' => null,
                            'pack_unit' => null,
                        ]);
                        
                        $productId = $product->id;
                        
                        Log::info('Created placeholder ingredient product', [
                            'dish_id' => $dish->id,
                            'ingredient' => $uniqueIngredientName,
                            'product_id' => $product->id,
                        ]);
                    }
                } elseif ($product && $productId) {
                    // CRITICAL: Reset stock to 0 for existing products used as ingredients
                    // This ensures the dish starts with 0 stock regardless of existing inventory
                    if ($product->stock != 0) {
                        $product->update(['stock' => 0]);
                        Log::info('Reset stock to 0 for existing ingredient product', [
                            'dish_id' => $dish->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                        ]);
                    }
                }
                
                // Always store ingredient info to create in all branches (whether new or existing product)
                // ALWAYS add unique suffix to avoid matching existing products in the database
                $actualProductName = trim($ing['name']) . ' (Dish Ingredient)';
                
                $ingredientProducts[] = [
                    'name' => $actualProductName,
                    'sku' => $product ? $product->sku : ($sku ?? 'ING-' . $dish->id . '-' . strtoupper(Str::random(6))),
                    'product_id' => $productId,
                ];

                DishIngredient::create([
                    'dish_id' => $dish->id,
                    'product_id' => $productId,
                    'name' => $ing['name'],
                    'brand' => $ing['brand'] ?? null,
                    'unit' => $ing['unit'] ?? null,
                    'per_serving' => $ing['per_serving'] ?? null,
                ]);
            }
            
            // FIRST: Create ingredient products for ALL branches (so dish stock calculation works correctly)
            // Store the created product IDs by branch and ingredient index
            $ingredientProductIds = []; // [branch_id][ingredient_index] = product_id
            
            foreach ($branches as $branch) {
                $ingredientProductIds[$branch->id] = [];
                
                foreach ($ingredientProducts as $index => $ingProduct) {
                    $originalProductId = $ingProduct['product_id'] ?? null;
                    $product = null;
                    
                    // If there's a linked product_id, try to find it in this branch first
                    if ($originalProductId) {
                        $product = Product::where('id', $originalProductId)
                            ->where('branch_id', $branch->id)
                            ->where('is_active', 1)
                            ->first();
                    }
                    
                    // If no product found by ID, try to find by normalized name (without the suffix)
                    if (!$product) {
                        $originalName = trim($ing['name'] ?? '');
                        if (!empty($originalName)) {
                            $nameUpper = strtoupper($originalName);
                            $normalized = preg_replace('/[^A-Z0-9]+/', '', $nameUpper);
                            
                            $product = Product::where('branch_id', $branch->id)
                                ->where('is_active', 1)
                                ->get()
                                ->filter(function ($p) use ($normalized, $nameUpper) {
                                    $pn = strtoupper($p->name ?? '');
                                    $pnNorm = preg_replace('/[^A-Z0-9]+/', '', $pn);
                                    // Match exact normalized name or if product name contains the ingredient name
                                    return $pnNorm === $normalized || str_contains($pn, $nameUpper);
                                })
                                ->sortByDesc(function ($p) {
                                    return ($p->stock ?? 0);
                                })
                                ->first();
                        }
                    }
                    
                    if ($product) {
                        // Found existing product in this branch - reuse it
                        $ingredientProductIds[$branch->id][$index] = $product->id;
                        
                        Log::info('Reusing EXISTING ingredient product for branch', [
                            'dish_id' => $dish->id,
                            'ingredient' => $ingProduct['name'],
                            'branch_id' => $branch->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'product_stock' => $product->stock,
                        ]);
                    } else {
                        // No existing product found - create a new one
                        $uniqueSku = 'DISH-' . $dish->id . '-ING-' . $index . '-B' . $branch->id;
                        
                        $product = Product::create([
                            'name' => $ingProduct['name'],
                            'slug' => Str::slug($ingProduct['name'] . '-' . $dish->id . '-' . $branch->id),
                            'price' => 0,
                            'cost_price' => 0,
                            'stock' => 0, // IMPORTANT: Always start with 0 stock
                            'min_stock' => 10,
                            'sku' => $uniqueSku,
                            'branch_id' => $branch->id,
                            'supplier_name' => 'KITCHEN',
                            'supplier_id' => null,
                            'is_published' => false,
                            'is_active' => true,
                            'is_kitchen_dish' => false,
                            'has_been_ordered' => false,
                            'logistics_request_available' => true,
                            'per_pack_or_individual' => 'individual',
                            'pack_quantity' => null,
                            'pack_unit' => null,
                        ]);
                        
                        $ingredientProductIds[$branch->id][$index] = $product->id;
                        
                        Log::info('Created NEW ingredient product for branch', [
                            'dish_id' => $dish->id,
                            'ingredient' => $ingProduct['name'],
                            'branch_id' => $branch->id,
                            'product_id' => $product->id,
                            'sku' => $uniqueSku,
                        ]);
                    }
                }
            }

            // SECOND: Now create dish products for ALL branches (ingredients now exist, so stock will be 0)
            foreach ($branches as $branch) {
                $this->createDishProductForBranch($dish, $branch->id, $user, $ingredientProductIds[$branch->id] ?? []);
            }

            \DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Dish created successfully and applied to all ' . $branches->count() . ' branches!',
                'data' => Dish::with('ingredients.product')->find($dish->id),
                'branches_affected' => $branches->count(),
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Failed to create dish for all branches', [
                'dish_name' => $validated['name'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'ok' => false,
                'message' => 'Failed to create dish: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method to create dish product for a specific branch
     */
    private function createDishProductForBranch(Dish $dish, $branchId, $user, $ingredientProductIds = [])
    {
        try {
            // Calculate max servings and cost based on available ingredient stock
            $costSum = 0.0;
            $maxServings = 0; // Start with 0 since ingredients likely have no stock

            foreach ($dish->ingredients as $index => $ing) {
                $perServing = (float) ($ing->per_serving ?? 1);
                if ($perServing <= 0) $perServing = 1;

                // CRITICAL: Use the exact product ID that was created for this dish ingredient
                // This ensures we only count stock from the dish's own ingredients (which start at 0)
                // and NOT from existing products in the database
                $candidateProducts = collect();
                
                if (isset($ingredientProductIds[$index])) {
                    // Get the EXACT product by ID - no searching, no matching by name
                    $product = Product::where('branch_id', $branchId)
                        ->where('id', $ingredientProductIds[$index])
                        ->where('is_active', 1)
                        ->first();
                    
                    if ($product) {
                        $candidateProducts = collect([$product]);
                        Log::info('Found ingredient product for dish stock calculation', [
                            'dish_id' => $dish->id,
                            'branch_id' => $branchId,
                            'ingredient_index' => $index,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'product_stock' => $product->stock,
                        ]);
                    } else {
                        Log::warning('Ingredient product NOT FOUND for dish stock calculation', [
                            'dish_id' => $dish->id,
                            'branch_id' => $branchId,
                            'ingredient_index' => $index,
                            'expected_product_id' => $ingredientProductIds[$index],
                        ]);
                    }
                }
                
                // If still no product found, dish can't be made
                if ($candidateProducts->isEmpty()) {
                    $maxServings = 0;
                    break;
                }

                $totalPieces = 0;
                $totalCost = 0.0;
                $isCondiment = false;
                
                foreach ($candidateProducts as $cp) {
                    $cat = strtolower(trim($cp->category ?? ''));
                    if ($cat === 'condiment') $isCondiment = true;

                    $perPackModeCp = in_array($cp->per_pack_or_individual, ['per_pack', 'both']);
                    $packQtyCp = (float) ($cp->pack_quantity ?? 0);
                    if ($perPackModeCp && $packQtyCp > 0) {
                        $openUsedCp = (float) ($cp->open_pack_used ?? 0);
                        $totalPieces += (($cp->stock ?? 0) * $packQtyCp) - $openUsedCp;
                    } else {
                        $totalPieces += (float) ($cp->stock ?? 0);
                    }

                    $totalCost += ((float) ($cp->cost_price ?? $cp->price ?? 0));
                }

                // If it's a condiment with no stock, still allow it but don't count for servings
                if ($isCondiment && $totalPieces <= 0) {
                    $costSum += ($totalCost * $perServing);
                    continue;
                }

                // Calculate how many servings this ingredient can make
                $possibleByIng = (int) floor($totalPieces / max(1, $perServing));
                
                // If any ingredient has 0 possible servings, the dish has 0 stock
                if ($possibleByIng == 0) {
                    $maxServings = 0;
                    break;
                }
                
                $maxServings = is_null($maxServings) ? $possibleByIng : min($maxServings, $possibleByIng);
                $costSum += ($totalCost * $perServing);
            }

            $maxServings = (int) ($maxServings ?? 0);
            $sellingPrice = 0;
            if ($maxServings > 0 && $costSum > 0) {
                $markup = 1.20;
                $sellingPrice = round($costSum * $markup, 2);
            }

            Log::info('Dish stock calculation complete', [
                'dish_id' => $dish->id,
                'dish_name' => $dish->name,
                'branch_id' => $branchId,
                'maxServings' => $maxServings,
                'costSum' => $costSum,
                'sellingPrice' => $sellingPrice,
            ]);

            // Create or update the dish Product row for this branch
            $dishProduct = Product::where('branch_id', $branchId)
                ->whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($dish->name))])
                ->first();

            if (!$dishProduct) {
                $skuBase = strtoupper(substr(preg_replace('/[^A-Z0-9]+/i', '', $dish->name), 0, 8));
                if ($skuBase === '') $skuBase = 'DISH';
                do {
                    $sku = $skuBase . '-' . strtoupper(Str::random(4));
                } while (Product::where('sku', $sku)->exists());

                Product::create([
                    'name' => $dish->name,
                    'slug' => Str::slug($dish->name),
                    'price' => $sellingPrice,
                    'cost_price' => $costSum > 0 ? round($costSum, 2) : null,
                    'stock' => $maxServings, // Will be 0 if ingredients have no stock
                    'min_stock' => 0,
                    'sku' => $sku,
                    'branch_id' => $branchId,
                    'dish_id' => $dish->id,
                    'is_dish_product' => true,
                    'is_published' => true, // Owner-created dishes are auto-published
                    'published_by' => $user->id,
                    'published_at' => now(),
                    'has_been_ordered' => 0,
                    'is_active' => 1,
                    'is_kitchen_dish' => true,
                    'supplier_name' => null,
                    'supplier_id' => null,
                    'logistics_request_available' => false,
                ]);
            } else {
                $dishProduct->is_kitchen_dish = true;
                $dishProduct->is_active = true;
                $dishProduct->stock = $maxServings; // Update stock based on current ingredient availability
                if ($sellingPrice > 0) $dishProduct->price = $sellingPrice;
                if ($costSum > 0) $dishProduct->cost_price = round($costSum, 2);
                $dishProduct->is_published = true;
                $dishProduct->published_by = $user->id;
                $dishProduct->published_at = now();
                $dishProduct->save();
            }

            // Flag all ingredient products for logistics review
            foreach ($dish->ingredients as $ing) {
                if ($ing->product) {
                    try {
                        $ing->product->update(['logistics_request_available' => true]);
                    } catch (\Exception $e) {
                        Log::warning('Failed to flag ingredient product for logistics', [
                            'ingredient_id' => $ing->id,
                            'product_id' => $ing->product_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to create dish product for branch', [
                'dish_id' => $dish->id,
                'branch_id' => $branchId,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - continue with other branches
        }
    }
}
