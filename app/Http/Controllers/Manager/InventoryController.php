<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Mock inventory data - replace with actual inventory table later
        $inventory = [
            [
                'id' => 1,
                'item_name' => 'Chicken Breast',
                'category' => 'Raw Materials',
                'quantity' => 45,
                'unit' => 'kg',
                'min_stock' => 20,
                'status' => 'ok',
                'last_updated' => now()->subHours(3)->format('M d, Y H:i'),
            ],
            [
                'id' => 2,
                'item_name' => 'Chicken Wings',
                'category' => 'Raw Materials',
                'quantity' => 15,
                'unit' => 'kg',
                'min_stock' => 20,
                'status' => 'low',
                'last_updated' => now()->subHours(5)->format('M d, Y H:i'),
            ],
            [
                'id' => 3,
                'item_name' => 'French Fries',
                'category' => 'Sides',
                'quantity' => 8,
                'unit' => 'kg',
                'min_stock' => 15,
                'status' => 'critical',
                'last_updated' => now()->subHours(1)->format('M d, Y H:i'),
            ],
            [
                'id' => 4,
                'item_name' => 'Gravy',
                'category' => 'Sauce',
                'quantity' => 5,
                'unit' => 'liters',
                'min_stock' => 10,
                'status' => 'critical',
                'last_updated' => now()->subMinutes(30)->format('M d, Y H:i'),
            ],
            [
                'id' => 5,
                'item_name' => 'Spicy Coating',
                'category' => 'Ingredients',
                'quantity' => 30,
                'unit' => 'kg',
                'min_stock' => 15,
                'status' => 'ok',
                'last_updated' => now()->subDays(1)->format('M d, Y H:i'),
            ],
        ];

        // Filter by search query if provided
        $search = $request->query('search');
        if ($search) {
            $inventory = array_filter($inventory, function ($item) use ($search) {
                return stripos($item['item_name'], $search) !== false ||
                       stripos($item['category'], $search) !== false;
            });
        }

        // Filter by status if provided
        $statusFilter = $request->query('status');
        if ($statusFilter) {
            $inventory = array_filter($inventory, function ($item) use ($statusFilter) {
                return $item['status'] === $statusFilter;
            });
        }

        return response()->json([
            'success' => true,
            'inventory' => array_values($inventory),
            'summary' => [
                'total_items' => count($inventory),
                'low_stock' => count(array_filter($inventory, fn($i) => $i['status'] === 'low')),
                'critical_stock' => count(array_filter($inventory, fn($i) => $i['status'] === 'critical')),
            ],
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

        $request->validate([
            'quantity' => 'required|numeric|min:0',
            'action' => 'required|in:add,set,subtract',
            'note' => 'nullable|string|max:255',
        ]);

        // TODO: Implement actual database update
        // For now, return success
        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'data' => [
                'id' => $id,
                'new_quantity' => $request->quantity,
                'action' => $request->action,
                'updated_at' => now()->format('M d, Y H:i'),
            ],
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
            'item_id' => 'required|integer',
            'quantity' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        // TODO: Implement actual delivery record in database
        return response()->json([
            'success' => true,
            'message' => 'Delivery recorded successfully',
            'data' => [
                'item_id' => $request->item_id,
                'quantity' => $request->quantity,
                'supplier' => $request->supplier,
                'recorded_at' => now()->format('M d, Y H:i'),
            ],
        ]);
    }
}
