<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Get inventory items for branch manager's branch
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        $isManager = ($user->role === 'BRANCH_MANAGER' && $user->branch_id);
        $isKitchen = ($user->department === 'KITCHEN' && $user->branch_id);

        if (! $isManager && ! $isKitchen) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $branchId = $user->branch_id;

        $productsQuery = Product::where('branch_id', $branchId)
            ->where('is_published', 1)
            ->where('is_active', 1);

        // Kitchen staff only need to see kitchen ingredient products
        if ($isKitchen && ! $isManager) {
            $productsQuery->where('is_kitchen_dish', 1);
        }

        $products = $productsQuery
            ->select('id', 'name', 'slug', 'price', 'stock', 'real_stock', 'min_stock', 'sku', 'branch_id', 'supplier_name', 'is_published', 'created_at', 'updated_at')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Update inventory quantity
     */
    public function updateStock(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        $isManager = ($user->role === 'BRANCH_MANAGER' && $user->branch_id);
        $isKitchen = ($user->department === 'KITCHEN' && $user->branch_id);

        if (! $isManager && ! $isKitchen) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $branchId = $user->branch_id;

        $product = Product::where('id', $id)
            ->where('branch_id', $branchId)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        // Validation differs for kitchen staff (they send `reduce`) vs managers (they send absolute `stock`).
        if ($isKitchen && ! $isManager) {
            // For kitchen staff, always group by normalized name to handle multi-supplier ingredients.
            // This ensures Flour from different suppliers aggregates correctly.
            $normalized = trim(strtoupper((string)$product->name));
            $groupQuery = Product::where('branch_id', $branchId)
                ->whereRaw('TRIM(UPPER(name)) = ?', [$normalized])
                ->where('is_active', 1);
            $groupTotal = (int) $groupQuery->sum('stock');

            $request->validate([
                'reduce' => ['required','integer','min:1', 'max:'.$groupTotal],
            ]);
        } else {
            $request->validate([
                'stock' => 'required|integer|min:0',
            ]);
        }

        // If user is kitchen staff, only allow updating products that are marked as kitchen ingredients
        if ($isKitchen && !$isManager) {
            if (!$product || (int)$product->is_kitchen_dish !== 1) {
                return response()->json(['success' => false, 'message' => 'Unauthorized to update this product'], 403);
            }
        }

        // Kitchen staff reduce by a quantity; managers set absolute stock
        if ($isKitchen && ! $isManager) {
            $reduce = (int) $request->reduce;

            // Use name-based grouping to decrement across all rows of this ingredient
            $normalized = trim(strtoupper((string)$product->name));
            $branchIdForTrans = $branchId;
            $normalizedForTrans = $normalized;

            DB::transaction(function () use ($normalizedForTrans, $branchIdForTrans, $reduce) {
                $remaining = $reduce;
                // Rebuild query inside transaction to avoid stale state
                $rows = Product::where('branch_id', $branchIdForTrans)
                    ->whereRaw('TRIM(UPPER(name)) = ?', [$normalizedForTrans])
                    ->where('is_active', 1)
                    ->where('stock', '>', 0)
                    ->orderBy('stock', 'desc')
                    ->get();
                    
                foreach ($rows as $r) {
                    if ($remaining <= 0) break;
                    $take = min((int)$r->stock, $remaining);
                    $r->stock = max(0, (int)$r->stock - $take);
                    $r->save();
                    $remaining -= $take;
                }
            });

            // Recompute aggregated real_stock for the group
            Product::recomputeRealStockForGroup($branchId, $product->sku, $product->name);

            // reload product to reflect updated values
            $product = Product::where('id', $product->id)->first();
        } else {
            $product->stock = (int) $request->stock;
            $product->save();
            Product::recomputeRealStockForGroup($branchId, $product->sku, $product->name);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'product' => $product
        ]);
    }

    /**
     * Record delivery/restock
     */
    public function recordDelivery(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        $branchId = $user->branch_id;

        $product = Product::where('id', $request->product_id)
            ->where('branch_id', $branchId)
            ->where('is_published', 1)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found or not published'], 404);
        }

        $newStock = $product->stock + $request->quantity;
        $product->stock = max(0, $newStock);
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Delivery recorded successfully',
            'product' => $product
        ]);
    }
}

