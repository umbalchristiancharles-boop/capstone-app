<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProcurementRequest;
use App\Models\SupplierOrder;
use App\Models\Product;

class ProcurementRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        $query = ProcurementRequest::with(['product', 'logisticsUser', 'procurementUser', 'financeUser'])
            ->orderBy('created_at', 'desc');

        if (in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS'])) {
            // Logistics sees own requests
            $query->where('logistics_user_id', $user->id);
        } elseif ($role === 'PROCUREMENT_MANAGER') {
            // Procurement sees pending/approved for branch
            $query->where('branch_id', $user->branch_id ?? 1)
                  ->whereIn('status', ['pending', 'approved', 'budget_pending']);
        } elseif (in_array($role, ['FINANCE_MANAGER', 'MANAGER_FINANCE'])) {
            // Finance sees pending budget approvals
            $query->where('budget_approved', false);
        } else {
            // Default branch filter
            $query->where('branch_id', $user->branch_id ?? 1);
        }

        $requests = $query->paginate(20);

        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $user = $request->user();
if (!(
    (strtoupper($user->role ?? '') === 'LOGISTICS_MANAGER' || strtoupper($user->role ?? '') === 'MANAGER_LOGISTICS') ||
    (strtoupper($user->role ?? '') === 'MANAGER' && strtoupper($user->department ?? '') === 'LOGISTICS')
)) {
    return response()->json(['error' => 'Unauthorized'], 401);
}

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        if ($user->branch_id && $product->branch_id != $user->branch_id) {
            return response()->json(['error' => 'Product not in your branch'], 403);
        }

        $procRequest = ProcurementRequest::create([
            'product_id' => $validated['product_id'],
            'logistics_user_id' => $user->id,
            'quantity' => $validated['quantity'],
            'price' => $product->price,
            'total_amount' => $product->price * $validated['quantity'],
            'status' => 'pending',
            'budget_approved' => false,
            'branch_id' => $user->branch_id ?? 1,
        ]);

        return response()->json($procRequest->load(['product', 'logisticsUser']), 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        $procRequest = ProcurementRequest::findOrFail($id);

        if ($role === 'PROCUREMENT_MANAGER' && $procRequest->status === 'pending') {
            $procRequest->update([
                'procurement_user_id' => $user->id,
                'status' => 'budget_pending'  // Wait for finance
            ]);
        } elseif (in_array($role, ['FINANCE_MANAGER', 'MANAGER_FINANCE']) && !$procRequest->budget_approved) {
            $validated = $request->validate(['budget_amount' => 'required|numeric|min:0']);
            if ($validated['budget_amount'] < $procRequest->total_amount) {
                return response()->json(['error' => 'Budget must cover total amount'], 400);
            }
            $procRequest->update([
                'finance_user_id' => $user->id,
                'budget_amount' => $validated['budget_amount'],
                'budget_approved' => true,
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json($procRequest->fresh()->load(['product', 'logisticsUser', 'procurementUser', 'financeUser']));
    }

    public function completeOrder(Request $request, $id)
    {
        $user = $request->user();
        if (strtoupper($user->role ?? '') !== 'PROCUREMENT_MANAGER') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $procRequest = ProcurementRequest::with('product')->findOrFail($id);

        // Check prerequisites
        if ($procRequest->status !== 'budget_pending' || !$procRequest->budget_approved) {
            return response()->json(['error' => 'Budget must be approved first'], 400);
        }
        if ($user->branch_id && $procRequest->branch_id != $user->branch_id) {
            return response()->json(['error' => 'Not your branch'], 403);
        }

        // Update stock
        $procRequest->product->increment('stock', $procRequest->quantity);
        $procRequest->update(['status' => 'completed', 'procurement_user_id' => $user->id]);

        // Create supplier order
        SupplierOrder::create([
            'procurement_request_id' => $procRequest->id,
            'product_id' => $procRequest->product_id,
            'supplier_id' => $procRequest->product->supplier_id,
            'quantity' => $procRequest->quantity,
            'status' => 'pending',
            'branch_id' => $procRequest->branch_id,
        ]);

        return response()->json([
            'message' => 'Order completed successfully. Stock updated and supplier notified.',
            'request' => $procRequest->fresh()->load('product')
        ]);
    }
}

