<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupplierOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupplierOrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            Log::info('SupplierOrderController@index called', ['has_user' => (bool) $user, 'user' => $user ? ['id' => $user->id, 'role' => $user->role ?? null] : null]);
            if (!$user) {
                Log::warning('SupplierOrderController@index unauthorized - no user');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $role = strtoupper($user->role ?? '');
            if (!in_array($role, ['SUPPLIER', 'SUPPLIER_MANAGER'])) {
                Log::warning('SupplierOrderController@index unauthorized - wrong role', ['role' => $role]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $query = SupplierOrder::with(['product', 'procurementRequest.logisticsUser', 'branch'])
                ->where('supplier_id', $user->id)
                ->orderBy('created_at', 'desc');

            $orders = $query->paginate(20);

            Log::info('SupplierOrderController@index returning orders', ['user_id' => $user->id, 'count' => $orders->total() ?? (is_array($orders) ? count($orders) : null)]);

            return response()->json($orders);
        } catch (\Exception $e) {
            Log::error('SupplierOrderController@index exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Supplier can submit product details to fulfill an order.
     * POST /api/supplier-orders/{id}/submit-product
     */
    public function submitProduct(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);
        if (!in_array(strtoupper($user->role ?? ''), ['SUPPLIER', 'SUPPLIER_MANAGER'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $order = SupplierOrder::findOrFail($id);
        if ($order->supplier_id != $user->id) return response()->json(['error' => 'Not your order'], 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Create or update product for supplier to fulfill the order
            $isDish = \App\Models\Dish::whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($validated['name']))])
                ->where('branch_id', $order->branch_id)
                ->exists();
            // Ensure SKU is not null — some databases enforce NOT NULL on sku.
            $generatedSku = $validated['sku'] ?? ('sku-' . time() . '-' . rand(1000, 9999));
            $ProductModel = \App\Models\Product::class;

            // Prefer updating the product already attached to the order
            $existingProduct = null;
            if (!empty($order->product_id)) {
                $existingProduct = $ProductModel::find($order->product_id);
            }

            // If no product attached, try to find by SKU if provided
            if (!$existingProduct && !empty($validated['sku'])) {
                $existingProduct = $ProductModel::where('sku', $validated['sku'])->first();
            }

            // If still not found, try matching by name + supplier + branch (avoid duplicates)
            if (!$existingProduct) {
                $existingProduct = $ProductModel::whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($validated['name']))])
                    ->where('branch_id', $order->branch_id)
                    ->where('supplier_id', $user->id)
                    ->first();
            }

            if ($existingProduct) {
                // Update existing product fields
                $existingProduct->update([
                    'name' => $validated['name'],
                    'slug' => \Illuminate\Support\Str::slug($validated['name']),
                    'price' => $validated['price'],
                    'cost_price' => $validated['price'],
                    'stock' => $validated['stock'] ?? $existingProduct->stock ?? 0,
                    'sku' => $validated['sku'] ?? $existingProduct->sku ?? $generatedSku,
                    'branch_id' => $order->branch_id,
                    'supplier_id' => $user->id,
                    'supplier_name' => $user->full_name ?? $user->username,
                    'is_published' => 1,
                    'is_active' => 1,
                    'is_kitchen_dish' => $isDish,
                ]);

                $product = $existingProduct;
            } else {
                // Create new product
                $product = $ProductModel::create([
                    'name' => $validated['name'],
                    'slug' => \Illuminate\Support\Str::slug($validated['name']),
                    'price' => $validated['price'],
                    'cost_price' => $validated['price'],
                    'stock' => $validated['stock'] ?? 0,
                    'sku' => $generatedSku,
                    'branch_id' => $order->branch_id,
                    'supplier_id' => $user->id,
                    'supplier_name' => $user->full_name ?? $user->username,
                    'is_published' => 1,
                    'is_active' => 1,
                    'is_kitchen_dish' => $isDish,
                ]);
            }

            // Attach product to supplier order
            $order->update(['product_id' => $product->id]);

            // Update linked procurement request to point to this product and set price/total
            if ($order->procurement_request_id) {
                $proc = $order->procurementRequest;
                if ($proc) {
                    $proc->update([
                        'product_id' => $product->id,
                        'price' => $validated['price'],
                        'total_amount' => ($validated['price'] * max(1, $order->quantity)),
                        'supplier_confirmed' => true,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['ok' => true, 'message' => 'Product submitted and linked to order', 'product' => $product, 'order' => $order->fresh()->load(['product', 'procurementRequest'])]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('submitProduct failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to submit product', 'details' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $user = $request->user();
            Log::info('SupplierOrderController@updateStatus called', ['user' => $user ? ['id' => $user->id, 'role' => $user->role ?? null] : null, 'input' => $request->all(), 'order_id' => $id]);

            if (!$user) {
                Log::warning('SupplierOrderController@updateStatus unauthorized - no user');
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            if (!in_array(strtoupper($user->role ?? ''), ['SUPPLIER', 'SUPPLIER_MANAGER'])) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            try {
                $order = SupplierOrder::findOrFail($id);
            } catch (ModelNotFoundException $e) {
                Log::warning('SupplierOrder not found', ['order_id' => $id]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            if ($order->supplier_id != $user->id) {
                return response()->json(['error' => 'Not your order'], 403);
            }

            $validated = $request->validate([
                'status' => 'required|in:pending,fulfilled,cancelled,on_delivery'
            ]);

            // Handle fulfillment and on-delivery: update order and update linked procurement request appropriately
            if ($validated['status'] === 'fulfilled' || $validated['status'] === 'on_delivery') {
                $newStatus = $validated['status'] === 'fulfilled' ? 'fulfilled' : 'on_delivery';

                DB::beginTransaction();
                try {
                    // Only set fulfilled_at when the supplier actually fulfilled the order
                    $order->update([
                        'status' => $newStatus,
                        'fulfilled_at' => $newStatus === 'fulfilled' ? now() : $order->fulfilled_at,
                    ]);

                    Log::info('SupplierOrder status updated', [
                        'order_id' => $order->id,
                        'new_status' => $newStatus,
                        'procurement_request_id' => $order->procurement_request_id
                    ]);

                    $proc = $order->procurementRequest;
                    if ($proc) {
                        Log::info('ProcurementRequest found', ['proc_id' => $proc->id, 'current_status' => $proc->status]);
                        
                        try {
                            if ($newStatus === 'fulfilled') {
                                // Finalize procurement: increment stock and mark procurement completed
                                if ($proc->product) {
                                    Log::info('Incrementing product stock', ['product_id' => $proc->product->id, 'quantity' => $proc->quantity]);
                                    $proc->product->increment('stock', $proc->quantity);
                                    $proc->product->update(['has_been_ordered' => true, 'logistics_request_available' => false]);
                                } else {
                                    Log::warning('No product found for procurement', ['proc_id' => $proc->id]);
                                }

                                $proc->update(['status' => 'completed']);
                                Log::info('ProcurementRequest completed', ['proc_id' => $proc->id]);
                            } else {
                                // Supplier indicates items are on delivery -> map to an allowed procurement status
                                // Map supplier's 'on_delivery' to procurement status 'ongoing_delivery'
                                $procNewStatus = 'ongoing_delivery';
                                $proc->update(['status' => $procNewStatus]);
                                Log::info('ProcurementRequest set to mapped status for on_delivery', ['proc_id' => $proc->id, 'mapped_status' => $procNewStatus]);
                            }
                        } catch (\Exception $procError) {
                            Log::error('ProcurementRequest update failed', [
                                'proc_id' => $proc->id,
                                'new_status' => $newStatus,
                                'error' => $procError->getMessage()
                            ]);
                            throw $procError;  // Re-throw to rollback
                        }
                    } else {
                        Log::error('No ProcurementRequest found for SupplierOrder', ['order_id' => $order->id, 'procurement_request_id' => $order->procurement_request_id]);
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('SupplierOrderController::updateStatus FAILED', [
                        'order_id' => $order->id ?? 'unknown',
                        'status' => $newStatus ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'procurement_request_id' => $order->procurement_request_id ?? null
                    ]);
                    $errorMsg = 'Failed to update order status';
                    if (stripos($e->getMessage(), 'procurement') !== false || stripos($e->getMessage(), 'product') !== false) {
                        $errorMsg = 'Missing procurement or product data for this order';
                    }
                    return response()->json(['error' => $errorMsg, 'details' => config('app.debug') ? $e->getMessage() : null], 500);
                }
            } else {
                $order->update(['status' => $validated['status']]);
            }

            return response()->json($order->fresh()->load(['product', 'procurementRequest']));
        } catch (\Throwable $e) {
            Log::error('SupplierOrderController@updateStatus exception', ['order_id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'input' => $request->all()]);
            return response()->json(['error' => 'Server error', 'message' => config('app.debug') ? $e->getMessage() : 'Failed to update order status'], 500);
        }
    }
}

