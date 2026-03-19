<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProcurementRequest;
use App\Models\SupplierOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcurementProductController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        $branchId = $user->branch_id;

        $isProcurementManager = $role === 'PROCUREMENT_MANAGER';
        $isManagerProcurement = ($role === 'MANAGER' && $dept === 'PROCUREMENT');
        if (!($isProcurementManager || $isManagerProcurement)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('PROCUREMENT PRODUCTS QUERY START', [
            'user_id' => $user->id,
            'role' => $role,
            'branch_id' => $branchId,
        ]);

        $query = Product::with(['supplier:id,username,full_name', 'procurementRequests' => function ($q) {
            $q->whereIn('status', ['pending','budget_pending','cash_in_transit','pending_order_to_supplier'])->latest();
        }])
        ->where('is_active', 1);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $query->where(function ($q) {
            $q->where('is_published', true)
              ->orWhere('logistics_request_available', true)
              ->orWhere(function ($subQ) {
                  $subQ->whereNotNull('supplier_id')
                       ->orWhere(function ($t) {
                           $t->whereNotNull('supplier_name')
                             ->where('supplier_name', '<>', '');
                       })
                       ->where('is_published', false);
              });
        });

        $count = $query->count();
        Log::info('PROCUREMENT PRODUCTS QUERY COUNT: ' . $count . ', SQL: ' . $query->toSql(), $query->getBindings());

        $products = $query->get();

        Log::info('PROCUREMENT PRODUCTS FINAL COUNT', ['count' => $products->count()]);

        return response()->json($products);
    }

    public function requestedProducts(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        $branchId = $user->branch_id;

        $isProcurementManager = $role === 'PROCUREMENT_MANAGER';
        $isManagerProcurement = ($role === 'MANAGER' && $dept === 'PROCUREMENT');
        if (!($isProcurementManager || $isManagerProcurement)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('PROCUREMENT REQUESTED PRODUCTS', [
            'user_id' => $user->id,
            'role' => $role,
            'branch_id' => $branchId,
        ]);

        $products = Product::with(['supplier:id,username,full_name'])
            ->where('is_active', 1)
            ->whereHas('procurementRequests', function ($q) use ($branchId) {
                $q->whereIn('status', ['pending','budget_pending','cash_in_transit','pending_order_to_supplier']);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            });

        if ($branchId) {
            $products->where('branch_id', $branchId);
        }

        $products = $products->get();

        return response()->json($products);
    }

public function placeOrder(Request $request, $productId)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        $isProcurementManager = $role === 'PROCUREMENT_MANAGER';
        $isManagerProcurement = ($role === 'MANAGER' && $dept === 'PROCUREMENT');
        if (!($isProcurementManager || $isManagerProcurement)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'quantity' => 'integer|min:1'
        ]);

        $product = Product::findOrFail($productId);
        if ($user->branch_id && $product->branch_id != $user->branch_id) {
            return response()->json(['error' => 'Not your branch'], 403);
        }

        $customQuantity = $validated['quantity'] ?? null;

        // Find pending procurement request
        $procRequest = ProcurementRequest::where('product_id', $productId)
            ->where('status', 'pending_order_to_supplier')
            ->where('branch_id', $user->branch_id ?? 1)
            ->first();

        if (!$procRequest) {
            return response()->json(['error' => 'No pending logistics request for this product'], 400);
        }

        // Check if SupplierOrder already exists for this request (prevent duplicates)
        $existingOrder = \App\Models\SupplierOrder::where('procurement_request_id', $procRequest->id)->first();
        if ($existingOrder) {
            return response()->json([
                'message' => 'Supplier order already placed (ID: ' . $existingOrder->id . '). Quantity: ' . $existingOrder->quantity,
                'supplier_order' => $existingOrder->load(['product', 'procurementRequest']),
                'procurement_request' => $procRequest->fresh()->load('product')
            ]);
        }

        // Check prerequisites
        if (!$procRequest->budget_approved) {
            return response()->json(['error' => 'Budget must be approved before ordering'], 400);
        }

        // Validate supplier exists
        $supplierId = $product->supplier_id;
        if (!$supplierId) {
            return response()->json(['error' => 'Product has no assigned supplier'], 400);
        }

        $quantity = $customQuantity ?? $procRequest->quantity;

        Log::info('Placing supplier order', [
            'user_id' => $user->id,
            'product_id' => $productId,
            'proc_request_id' => $procRequest->id,
            'supplier_id' => $supplierId,
            'quantity' => $quantity
        ]);


        // Create supplier order atomically
        try {
            $supplierOrder = DB::transaction(function () use ($procRequest, $supplierId, $quantity, $user) {
                $order = SupplierOrder::create([
                    'procurement_request_id' => $procRequest->id,
                    'product_id' => $procRequest->product_id,
                    'supplier_id' => $supplierId,
                    'quantity' => $quantity,
                    'status' => 'pending',
                    'branch_id' => $procRequest->branch_id,
                ]);

                // Update procurement request status to pending_order_to_supplier and clear flags
                // Use 'pending_order_to_supplier' to match current enum values and queries
                $procRequest->update([
                    'procurement_user_id' => $user->id,
                    'status' => 'pending_order_to_supplier'  // Prevents re-showing in procurement lists
                ]);

                // Clear product logistics flag
                if ($procRequest->product) {
                    $procRequest->product->update(['logistics_request_available' => false]);
                }

                return $order;
            });
        } catch (\Exception $e) {
            Log::error('SUPPLIER ORDER TRANSACTION FAILED', [
                'error' => $e->getMessage(),
                'proc_req_id' => $procRequest->id,
                'user_id' => $user->id
            ]);
            return response()->json(['error' => 'Failed to place supplier order'], 500);
        }


        return response()->json([
            'message' => 'Order placed with supplier successfully. Quantity: ' . $quantity,
            'supplier_order' => $supplierOrder,
            'procurement_request' => $procRequest->fresh()->load('product')
        ]);
    }
}
