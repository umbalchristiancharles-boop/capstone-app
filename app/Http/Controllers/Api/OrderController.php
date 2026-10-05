<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Mark an active kitchen order as completed after it has been served.
     * PATCH /api/orders/{id}/mark-completed
     */
    public function markCompleted(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Only allow staff from the same branch to mark orders as completed
        if ($order->branch_id !== $user->branch_id && !in_array($user->role, ['SUPER_ADMIN', 'SUPERADMIN'])) {
            return response()->json(['error' => 'Unauthorized - different branch'], 403);
        }

        if ($order->status !== 'ready') {
            return response()->json([
                'error' => 'Cannot mark as completed',
                'message' => "Order status is '{$order->status}', expected 'ready'"
            ], 422);
        }

        try {
            DB::transaction(function () use ($order, $user) {
                $order->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $user->id,
                ]);
            });

            return response()->json([
                'message' => 'Order marked as completed',
                'order' => $order->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move a kitchen order from queued to preparing, then to ready.
     * PATCH /api/orders/{id}/kitchen-status
     */
    public function updateKitchenStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:preparing,ready',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return DB::transaction(function () use ($user, $id, $validated) {
            $order = Order::whereKey($id)->lockForUpdate()->first();
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            if ($order->branch_id !== $user->branch_id && !in_array($user->role, ['SUPER_ADMIN', 'SUPERADMIN'])) {
                return response()->json(['error' => 'Unauthorized - different branch'], 403);
            }

            $allowedTransitions = [
                'pending' => 'preparing',
                'in_kitchen' => 'preparing',
                'preparing' => 'ready',
            ];

            if (($allowedTransitions[$order->status] ?? null) !== $validated['status']) {
                return response()->json([
                    'error' => 'Invalid kitchen order transition',
                    'message' => "Order status is '{$order->status}' and cannot be changed to '{$validated['status']}'",
                ], 422);
            }

            $order->update(['status' => $validated['status']]);

            return response()->json([
                'message' => 'Kitchen order status updated',
                'order' => $order->fresh(),
            ]);
        });
    }

    /**
     * Get order details
     * GET /api/orders/{id}
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::with('items')->find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Only allow viewing orders from the same branch
        if ($order->branch_id !== $user->branch_id && !in_array($user->role, ['SUPER_ADMIN', 'SUPERADMIN'])) {
            return response()->json(['error' => 'Unauthorized - different branch'], 403);
        }

        return response()->json($order);
    }
}
