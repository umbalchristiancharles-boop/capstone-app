<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProcurementRequest;
use App\Models\SupplierOrder;
use App\Models\Branch;
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

        // Filter out products from unapproved kitchen dishes
        // Only show kitchen dish products if the dish has been approved by the owner
        $products = $products->filter(function ($product) {
            if (!$product->is_kitchen_dish) {
                // Non-kitchen dishes are always shown
                return true;
            }

            // For kitchen dishes, check if all related dishes are approved
            $dishIngredients = $product->dishIngredients;
            if ($dishIngredients->isEmpty()) {
                // No dish associations, show it
                return true;
            }

            // Only show if at least one associated dish is approved
            return $dishIngredients->some(function ($dishIng) {
                $dish = $dishIng->dish;
                return $dish && $dish->approval_status === 'approved';
            });
        });

        // Reindex the collection
        $products = $products->values();

            // Attach a flag for UI: whether supplier input is needed for each product
            $products = $products->map(function ($p) {
                $p->needs_supplier = true;
                if (!empty($p->supplier_id) && (float)($p->price ?? 0) > 0) {
                    $p->needs_supplier = false;
                }
                return $p;
            });

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

        $products = Product::with(['supplier:id,username,full_name', 'dishIngredients.dish'])
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

        // Filter out products from unapproved kitchen dishes
        $products = $products->filter(function ($product) {
            if (!$product->is_kitchen_dish) {
                // Non-kitchen dishes are always shown
                return true;
            }

            // For kitchen dishes, check if all related dishes are approved
            $dishIngredients = $product->dishIngredients;
            if ($dishIngredients->isEmpty()) {
                // No dish associations, show it
                return true;
            }

            // Only show if at least one associated dish is approved
            return $dishIngredients->some(function ($dishIng) {
                $dish = $dishIng->dish;
                return $dish && $dish->approval_status === 'approved';
            });
        });

        // Reindex the collection
        $products = $products->values();

            // Mark whether this product needs supplier submission
            $products = $products->map(function ($p) {
                $p->needs_supplier = true;
                if (!empty($p->supplier_id) && (float)($p->price ?? 0) > 0) {
                    $p->needs_supplier = false;
                }
                return $p;
            });

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
            'supplier_id' => 'nullable|exists:users,id'
        ]);

        $product = Product::findOrFail($productId);
        if ($user->branch_id && $product->branch_id != $user->branch_id) {
            return response()->json(['error' => 'Not your branch'], 403);
        }

        // Find pending procurement request
        $procRequest = ProcurementRequest::where('product_id', $productId)
            ->where('status', 'pending_order_to_supplier')
            ->where('branch_id', $user->branch_id ?? 1)
            ->first();

        if (!$procRequest) {
            return response()->json(['error' => 'No pending logistics request for this product'], 400);
        }

        // Ensure supplier has confirmed availability before allowing procurement manager to place an order.
        if (empty($procRequest->supplier_confirmed) || !$procRequest->supplier_confirmed) {
            return response()->json(['error' => 'Supplier has not confirmed product availability yet'], 400);
        }

        // Determine the supplier for this product early so we can check duplicates.
        $supplierId = $product->supplier_id;

        // Check if SupplierOrder already exists for this request for the intended supplier (prevent duplicates).
        // Broadcast-created orders for other suppliers should not block placing an order for the selected supplier.
        $existingOrder = \App\Models\SupplierOrder::where('procurement_request_id', $procRequest->id)
            ->where('supplier_id', $supplierId)
            ->first();
        if ($existingOrder) {
            return response()->json([
                'message' => 'Supplier order already placed for this supplier (ID: ' . $existingOrder->id . '). Quantity: ' . $existingOrder->quantity,
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

        // Always use the quantity requested by logistics (cannot be changed by procurement)
        $quantity = $procRequest->quantity;

        Log::info('Placing supplier order', [
            'user_id' => $user->id,
            'product_id' => $productId,
            'proc_request_id' => $procRequest->id,
            'supplier_id' => $supplierId,
            'quantity' => $quantity
        ]);


        // Ensure budget approved before ordering
        if (!$procRequest->budget_approved) {
            return response()->json(['error' => 'Budget must be approved before ordering'], 400);
        }

        // Use the logistics-requested quantity (cannot be modified by procurement)
        $quantity = $procRequest->quantity;

        // If explicit supplier_id is provided in request, use it; otherwise use product's supplier
        if (!empty($validated['supplier_id'])) {
            $supplierId = $validated['supplier_id'];
        }

        // Create supplier order atomically (single code path, no duplication)
        try {
            $supplierOrder = DB::transaction(function () use ($procRequest, $supplierId, $quantity, $user) {
                $order = SupplierOrder::create([
                    'procurement_request_id' => $procRequest->id,
                    'product_id' => $procRequest->product_id,
                    'supplier_id' => $supplierId,
                    'quantity' => $quantity,
                    'status' => 'pending',
                    'is_broadcast' => false,
                    'branch_id' => $procRequest->branch_id,
                ]);

                // Update procurement request status to pending_order_to_supplier and clear flags
                // Use 'pending_order_to_supplier' to match current enum values and queries.
                // If the DB doesn't accept that enum value, fall back to 'delivery_pending'.
                try {
                    $procRequest->update([
                        'procurement_user_id' => $user->id,
                        'status' => 'pending_order_to_supplier',  // Prevents re-showing in procurement lists
                        'supplier_confirmed' => false,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to set pending_order_to_supplier, falling back to delivery_pending', ['error' => $e->getMessage(), 'procurement_request_id' => $procRequest->id]);
                    $procRequest->update([
                        'procurement_user_id' => $user->id,
                        'status' => 'delivery_pending',
                    ]);
                }

                // Clear product logistics flag
                if ($procRequest->product) {
                    $procRequest->product->update(['logistics_request_available' => false]);
                }

                // Deduct branch budget for this procurement order (use budget_amount when available)
                try {
                    $branch = Branch::where('id', $procRequest->branch_id)->lockForUpdate()->first();
                    $deductAmount = $procRequest->budget_amount ?? $procRequest->total_amount ?? ($procRequest->price * $quantity);
                    if ($branch && $deductAmount) {
                        $branch->budget = is_null($branch->budget) ? 0 : ($branch->budget - (float) $deductAmount);
                        $branch->save();
                    }
                } catch (\Exception $e) {
                    // If budget update fails, log but allow order creation to continue - transaction will rollback this whole closure
                    // Let exception bubble to trigger rollback
                    throw $e;
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

        Log::info('SupplierOrder created', [
            'supplier_order_id' => $supplierOrder->id ?? null,
            'supplier_id' => $supplierOrder->supplier_id ?? null,
            'procurement_request_id' => $supplierOrder->procurement_request_id ?? null,
        ]);

        return response()->json([
            'message' => 'Order placed with supplier successfully. Quantity: ' . $quantity,
            'supplier_order' => $supplierOrder,
            'procurement_request' => $procRequest->fresh()->load('product')
        ]);
    }
}
