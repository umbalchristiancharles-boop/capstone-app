<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupplierOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\ProcurementRequestUpdated;

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
                ->orderBy('updated_at', 'desc')
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
            // price must be a positive number greater than zero
            'price' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255|not_in:,null',
            'per_pack_or_individual' => 'required|in:individual,per_pack,both',
            // If front-end always sends the field, allow null when not per_pack by using nullable.
            // Keep required_if to force presence when per_pack is selected.
            'pack_quantity' => 'sometimes|required_if:per_pack_or_individual,per_pack|nullable|numeric|min:0',
            'pack_unit' => 'sometimes|required_if:per_pack_or_individual,per_pack|nullable|string|max:50',
            'expires_at' => 'required|date_format:Y-m-d\TH:i',
            'date_made' => 'nullable|date_format:Y-m-d',
            'stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|max:255'
        ]);

        // Additional check: ensure category is not empty after trimming
        if (empty(trim($validated['category'] ?? ''))) {
            return response()->json(['error' => 'Category cannot be empty'], 422);
        }

        try {
            DB::beginTransaction();

            Log::info('submitProduct: validated data', [
                'name' => $validated['name'],
                'category' => $validated['category'],
                'price' => $validated['price'],
                'expires_at' => $validated['expires_at'],
            ]);

            // Create or update product for supplier to fulfill the order
            $isDish = \App\Models\Dish::whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($validated['name']))])
                ->where('branch_id', $order->branch_id)
                ->exists();
            // Ensure SKU is not null — some databases enforce NOT NULL on sku.
            $generatedSku = $validated['sku'] ?? ('sku-' . time() . '-' . rand(1000, 9999));
            $ProductModel = \App\Models\Product::class;

            // Generate unique slug - handle duplicates
            $slug = \Illuminate\Support\Str::slug($validated['name']);
            $originalSlug = $slug;
            $counter = 1;
            while (\App\Models\Product::where('slug', $slug)->where('id', '!=', $order->product_id ?? 0)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Each supplier should have their own product instance
            // Do NOT use the order's existing product_id as that belongs to procurement request, not this supplier
            $existingProduct = null;

            // Try to find by SKU if provided (supplier-specific)
            if (!empty($validated['sku'])) {
                $existingProduct = $ProductModel::where('sku', $validated['sku'])
                    ->where('supplier_id', $user->id)
                    ->where('branch_id', $order->branch_id)
                    ->first();
            }

            // If still not found, try matching by name + supplier + branch (avoid duplicates per supplier)
            if (!$existingProduct) {
                $existingProduct = $ProductModel::whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($validated['name']))])
                    ->where('branch_id', $order->branch_id)
                    ->where('supplier_id', $user->id)
                    ->first();
            }

            if ($existingProduct) {
                // Update existing product fields
                Log::info('submitProduct: updating existing product', ['product_id' => $existingProduct->id, 'category' => $validated['category']]);
                $existingProduct->update([
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'category' => $validated['category'],
                    'per_pack_or_individual' => $validated['per_pack_or_individual'],
                    'pack_quantity' => $validated['pack_quantity'] ?? null,
                    'pack_unit' => $validated['pack_unit'] ?? null,
                    'price' => $validated['price'],
                    'cost_price' => $validated['price'],
                    'stock' => $validated['stock'] ?? $existingProduct->stock ?? 0,
                    'sku' => $validated['sku'] ?? $existingProduct->sku ?? $generatedSku,
                    'branch_id' => $order->branch_id,
                    'supplier_id' => $user->id,
                    'supplier_name' => $user->full_name ?? $user->username,
                    'expires_at' => $validated['expires_at'],
                    'date_made' => $validated['date_made'] ?? null,
                    'is_published' => 1,
                    'is_active' => 1,
                    'is_kitchen_dish' => $isDish,
                ]);

                $product = $existingProduct;
                Log::info('submitProduct: product updated successfully', ['product_id' => $product->id, 'saved_category' => $product->fresh()->category]);
            } else {
                // Create new product
                Log::info('submitProduct: creating new product', ['category' => $validated['category']]);
                $product = $ProductModel::create([
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'category' => $validated['category'],
                    'per_pack_or_individual' => $validated['per_pack_or_individual'],
                    'pack_quantity' => $validated['pack_quantity'] ?? null,
                    'pack_unit' => $validated['pack_unit'] ?? null,
                    'price' => $validated['price'],
                    'cost_price' => $validated['price'],
                    'stock' => $validated['stock'] ?? 0,
                    'sku' => $generatedSku,
                    'branch_id' => $order->branch_id,
                    'supplier_id' => $user->id,
                    'supplier_name' => $user->full_name ?? $user->username,
                    'expires_at' => $validated['expires_at'],
                    'date_made' => $validated['date_made'] ?? null,
                    'is_published' => 1,
                    'is_active' => 1,
                    'is_kitchen_dish' => $isDish,
                ]);
                Log::info('submitProduct: product created successfully', ['product_id' => $product->id, 'saved_category' => $product->category]);
            }
            // Recompute persisted real_stock for this product group (branch + sku/name)
            try {
                \App\Models\Product::recomputeRealStockForGroup($order->branch_id, $product->sku, $product->name);
            } catch (\Exception $e) {
                Log::warning('Failed to recompute real_stock after supplier submitProduct', ['error' => $e->getMessage(), 'product_id' => $product->id, 'order_id' => $order->id]);
            }
            // Attach product to supplier order
            $order->update(['product_id' => $product->id]);

            // Update linked procurement request with price/total only on first confirmation
            // Do NOT update product_id - each SupplierOrder has their own product
            if ($order->procurement_request_id) {
                $proc = $order->procurementRequest;
                if ($proc) {
                    // Only update if not already confirmed (first supplier confirmation)
                    if (!$proc->supplier_confirmed) {
                        $proc->update([
                            'price' => $validated['price'],
                            'total_amount' => ($validated['price'] * max(1, $order->quantity)),
                            'supplier_confirmed' => true,
                        ]);
                    }
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
                            $oldStatus = $proc->status;
                            if ($newStatus === 'fulfilled') {
                                // Supplier marked order fulfilled. Do NOT increment product stock here.
                                // Inventory should only be updated when logistics staff confirm delivered quantities.
                                Log::info('Marking procurement awaiting inventory confirmation (supplier fulfilled)', ['proc_id' => $proc->id]);
                                $proc->update(['status' => 'awaiting_inventory_confirmation']);
                                // dispatch update event
                                try { event(new ProcurementRequestUpdated($proc->fresh())); } catch (\Throwable $_e) { Log::debug('Failed to dispatch ProcurementRequestUpdated', ['error' => $_e->getMessage()]); }
                                Log::info('ProcurementRequest status changed', ['proc_id' => $proc->id, 'old_status' => $oldStatus, 'new_status' => $proc->status]);
                            } else {
                                // Supplier indicates items are on delivery -> map to an allowed procurement status
                                // Map supplier's 'on_delivery' to procurement status 'ongoing_delivery'
                                $procNewStatus = 'ongoing_delivery';
                                $proc->update(['status' => $procNewStatus]);
                                // dispatch update event
                                try { event(new ProcurementRequestUpdated($proc->fresh())); } catch (\Throwable $_e) { Log::debug('Failed to dispatch ProcurementRequestUpdated', ['error' => $_e->getMessage()]); }
                                Log::info('ProcurementRequest set to mapped status for on_delivery', ['proc_id' => $proc->id, 'mapped_status' => $procNewStatus, 'old_status' => $oldStatus]);
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

