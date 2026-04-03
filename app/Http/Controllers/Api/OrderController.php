<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;

class OrderController extends Controller
{
    /**
     * Mark an order as completed (transition from 'in_kitchen' to 'completed')
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

        // Only allow marking 'in_kitchen' or 'pending' orders as completed
        if (!in_array($order->status, ['in_kitchen', 'pending'])) {
            return response()->json([
                'error' => 'Cannot mark as completed',
                'message' => "Order status is '{$order->status}', expected 'in_kitchen' or 'pending'"
            ], 422);
        }

        try {
            DB::transaction(function () use ($order, $user) {
                $order->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $user->id,
                ]);

                // Credit branch budget with the order amount (sales increase budget)
                try {
                    $branch = Branch::where('id', $order->branch_id)->lockForUpdate()->first();
                    if ($branch) {
                        $amount = (float) ($order->grand_total ?? 0);
                        $branch->budget = is_null($branch->budget) ? $amount : ($branch->budget + $amount);
                        $branch->save();
                    }
                } catch (\Exception $e) {
                    // If branch update fails, rethrow to rollback transaction
                    throw $e;
                }
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
