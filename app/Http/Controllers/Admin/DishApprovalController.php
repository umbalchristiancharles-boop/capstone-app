<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
            // and flag them for logistics to request from procurement
            foreach ($dish->ingredients as $ing) {
                if (!$ing->product_id) {
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
                            'is_published' => false,
                            'is_active' => true,
                            'is_kitchen_dish' => true,
                            'has_been_ordered' => false,
                            'logistics_request_available' => true,
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
                } else {
                    // For existing products, make sure they're flagged for logistics if needed
                    if ($ing->product) {
                        $ing->product->update(['logistics_request_available' => true]);
                    }
                }
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
}
