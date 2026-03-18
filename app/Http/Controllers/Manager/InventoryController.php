<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;

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

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $branchId = $user->branch_id;

        $products = Product::where('branch_id', $branchId)
            ->where('is_published', 1)
            ->where('is_active', 1)
            ->select('id', 'name', 'slug', 'price', 'stock', 'min_stock', 'sku', 'branch_id', 'supplier_name', 'is_published', 'created_at', 'updated_at')
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

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $branchId = $user->branch_id;

        $product = Product::where('id', $id)
            ->where('branch_id', $branchId)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $product->stock = $request->stock;
        $product->save();

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

