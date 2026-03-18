<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupplierOrder;

class SupplierOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!in_array(strtoupper($user->role ?? ''), ['SUPPLIER', 'SUPPLIER_MANAGER'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $query = SupplierOrder::with(['product', 'procurementRequest.logisticsUser', 'branch'])
            ->where('supplier_id', $user->id)
            ->orderBy('created_at', 'desc');

        $orders = $query->paginate(20);

        return response()->json($orders);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array(strtoupper($user->role ?? ''), ['SUPPLIER', 'SUPPLIER_MANAGER'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $order = SupplierOrder::findOrFail($id);

        if ($order->supplier_id != $user->id) {
            return response()->json(['error' => 'Not your order'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,fulfilled,cancelled'
        ]);

        if ($validated['status'] === 'fulfilled') {
            $order->update([
                'status' => 'fulfilled',
                'fulfilled_at' => now(),
            ]);
        } else {
            $order->update(['status' => $validated['status']]);
        }

        return response()->json($order->fresh()->load(['product', 'procurementRequest']));
    }
}

