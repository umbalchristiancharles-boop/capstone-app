<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Dish;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\ProcurementRequest;
use App\Models\SupplierOrder;
use App\Models\SupplierAuditLog;
use App\Models\LogisticsTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product as ProductModel;

class StaffInventoryController extends Controller
{
    // PUT /api/staff/inventory/profile
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if user is staff (not owner/admin/manager)
        $isStaff = ($user->role === 'staff');

        $validated = $request->validate([
            'fullName' => $isStaff ? 'nullable' : 'sometimes|string|max:255',
            'username' => $isStaff ? 'nullable' : 'sometimes|string|max:50|unique:users,username,' . $user->id,
            'email' => $isStaff ? 'nullable' : 'nullable|email|unique:users,email,' . $user->id,
            'contact' => $isStaff ? 'nullable' : 'sometimes|string|max:50',
            'password' => $isStaff ? 'required|string|min:8|confirmed' : 'nullable|string|min:8|confirmed',
        ]);

        // For staff role: ONLY allow password update, block all other fields
        if ($isStaff) {
            $user->password = Hash::make($validated['password']);
            $user->save();

            return response()->json([
                'ok' => true,
                'message' => 'Password updated successfully.',
                'user' => [
                    'id' => $user->id,
                    'fullName' => $user->full_name ?? $user->username,
                    'full_name' => $user->full_name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'contact' => $user->phone_number,
                    'role' => $user->role,
                    'department' => $user->department ?? null,
                    'avatarUrl' => $user->avatar_url ? url($user->avatar_url) : null,
                ]
            ]);
        }

        // For owner/admin/manager: allow all fields to be updated
        // Update fields only if provided
        if (!empty($validated['fullName'])) {
            $user->full_name = $validated['fullName'];
        }

        if (!empty($validated['username'])) {
            $user->username = $validated['username'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (!empty($validated['contact'])) {
            $user->phone_number = $validated['contact'];
        }

        // Only update password if provided and not empty
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'ok' => true,
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'fullName' => $user->full_name ?? $user->username,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'contact' => $user->phone_number,
                'role' => $user->role,
                'department' => $user->department ?? null,
                'avatarUrl' => $user->avatar_url ? url($user->avatar_url) : null,
            ]
        ]);
    }

    // GET /api/staff/inventory/products
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $branchId = $user->branch_id;

        $query = Product::where('branch_id', $branchId)
            ->where('is_active', 1)
            ->where(function($q) {
                // Show all active products including pending approval; exclude only rejected
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['rejected']);
            });

        // Allow callers to request unpublished products as well (useful for internal staff views)
        // Branch ADMIN/OWNER/SUPER_ADMIN should be able to see unpublished products by default
        $includeUnpublished = $request->boolean('include_unpublished', false);
        $roleUpper = strtoupper($user->role ?? '');
        if (in_array($roleUpper, ['ADMIN', 'OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])) {
            $includeUnpublished = true;
        }

        // Exclude representative dish products from the inventory list (only ingredients should show)
        $query->where(function($q) {
            $q->whereNull('is_dish_product')->orWhere('is_dish_product', 0);
        });

        // Suppliers should only see products they own; other roles see published products.
        if (strtoupper($user->role ?? '') === 'SUPPLIER') {
            $query->where('supplier_id', $user->id);
        } else {
            if (!$includeUnpublished) {
                $query->where('is_published', 1);
            }
        }

        // Show supplier-submitted products as well so staff and logistics
        // can view newly added supplier products that have not yet
        // been accepted/placed into inventory by procurement.
        // (Procurement will still mark products as published when placed.)

        $products = $query->select('id', 'name', 'slug', 'price', 'stock', 'sku', 'branch_id', 'is_published', 'created_at', 'updated_at', 'status')
            ->orderBy('name')
            ->get();

        // Deduplicate by normalized name: prefer published items, otherwise most recently updated.
        try {
            $map = [];
            foreach ($products as $p) {
                $key = trim(strtolower($p->name ?? ''));
                if ($key === '') continue;
                if (!isset($map[$key])) {
                    $map[$key] = $p;
                    continue;
                }
                $existing = $map[$key];
                $existingPublished = !empty($existing->is_published);
                $curPublished = !empty($p->is_published);
                if ($curPublished && !$existingPublished) {
                    $map[$key] = $p;
                    continue;
                }
                if ($curPublished === $existingPublished) {
                    $existingTime = strtotime($existing->updated_at ?? $existing->created_at ?? 0);
                    $curTime = strtotime($p->updated_at ?? $p->created_at ?? 0);
                    if ($curTime > $existingTime) {
                        $map[$key] = $p;
                    }
                }
            }

            $deduped = array_values($map);
            // keep alphabetical order by name
            usort($deduped, function ($a, $b) {
                return strcasecmp($a->name ?? '', $b->name ?? '');
            });
            return response()->json($deduped);
        } catch (\Exception $e) {
            // If dedupe fails for any reason, return original list to avoid breaking clients
            Log::warning('Product dedupe failed in StaffInventoryController:index', ['error' => $e->getMessage()]);
            return response()->json($products);
        }
    }

    // Alias for backward compatibility
    public function products(Request $request)
    {
        return $this->index($request);
    }

    // POST /api/staff/inventory/products
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category' => 'required|string|max:255',
            'per_pack_or_individual' => 'required|in:individual,per_pack,both',
            'pack_quantity' => 'sometimes|required_if:per_pack_or_individual,per_pack|nullable|numeric|min:0',
            'pack_unit' => 'sometimes|required_if:per_pack_or_individual,per_pack|nullable|string|max:50',
            'expires_at' => 'required|date_format:Y-m-d\\TH:i',
            'sku' => 'nullable|string|unique:products,sku',
            'requires_logistics' => 'boolean', // Whether product requires logistics approval
        ]);

        // Default stock to 0 if not provided
        $stock = $validated['stock'] ?? 0;

        // Additional protection: ensure stock is never negative
        if ($stock < 0) {
            $stock = 0;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $branchId = $user->branch_id;

        // If SKU not provided, generate a unique SKU automatically.
        if (empty($validated['sku'])) {
            $base = strtoupper(preg_replace('/[^A-Z0-9]+/i', '', substr($validated['name'], 0, 6)));
            if ($base === '') {
                $base = 'PRD';
            }
            // Try a few times to avoid collisions
            do {
                $random = strtoupper(Str::random(4));
                $sku = $base . '-' . $random;
            } while (Product::where('sku', $sku)->exists());
        } else {
            $sku = $validated['sku'];
        }

        // Derive supplier name from authenticated user (supplier account)
        $supplierName = null;
        if ($user) {
            $supplierName = $user->full_name ?? $user->username ?? $user->email ?? null;
        }

        // By default new products are unpublished. Branch `ADMIN`, `OWNER`, and super-admins
        // may create published products immediately.
        $roleUpper = strtoupper($user->role ?? '');
        $isPublished = in_array($roleUpper, ['ADMIN', 'OWNER', 'SUPER_ADMIN', 'SUPERADMIN']) ? 1 : 0;
        // Ensure supplier-submitted products remain unpublished
        if ($roleUpper === 'SUPPLIER') {
            $isPublished = 0;
        }

        // Check if this is a kitchen dish (wrapped in try-catch to avoid timeouts)
        $isKitchenDish = false;
        try {
            $isKitchenDish = Dish::whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($validated['name']))])
                ->where('branch_id', $branchId)
                ->exists();
        } catch (\Exception $e) {
            Log::warning('Failed to check if product is kitchen dish', ['error' => $e->getMessage()]);
            $isKitchenDish = false;
        }

        // Determine initial status based on approval workflow
        $requiresLogistics = $validated['requires_logistics'] ?? false;
        $initialStatus = $requiresLogistics ? 'pending_logistics_main' : 'pending_owner';

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'price' => $validated['price'],
            'cost_price' => $validated['price'],
            'stock' => $stock,
            'sku' => $sku,
            'category' => $validated['category'],
            'per_pack_or_individual' => $validated['per_pack_or_individual'],
            'pack_quantity' => $validated['pack_quantity'] ?? null,
            'pack_unit' => $validated['pack_unit'] ?? null,
            'expires_at' => $validated['expires_at'],
            'branch_id' => $branchId,
            'supplier_id' => $user->id,
            'supplier_name' => $supplierName,
            'is_published' => $isPublished,
            'is_active' => true,
            'is_kitchen_dish' => $isKitchenDish,
            'status' => $initialStatus,
            'requires_logistics' => $requiresLogistics,
        ]);

        // Recompute persisted real_stock for the group (branch + sku/name)
        try {
            ProductModel::recomputeRealStockForGroup($product->branch_id, $product->sku, $product->name);
        } catch (\Exception $e) {
            Log::warning('Failed to recompute real_stock after product create', ['error' => $e->getMessage(), 'product_id' => $product->id]);
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    // PUT /api/staff/inventory/products/{id}
    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $branchId = $user->branch_id;

        $product = Product::where('id', $id)
            ->where('branch_id', $branchId)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found or access denied'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'sku' => 'sometimes|string|unique:products,sku,' . $id,
            'category' => 'sometimes|string|max:255',
            'per_pack_or_individual' => 'sometimes|in:individual,per_pack,both',
            'pack_quantity' => 'sometimes|required_if:per_pack_or_individual,per_pack|nullable|numeric|min:0',
            'pack_unit' => 'sometimes|required_if:per_pack_or_individual,per_pack|nullable|string|max:50',
            'expires_at' => 'sometimes|date_format:Y-m-d\\TH:i',
            // allow branch-level Admins to toggle publish state
            'is_published' => 'sometimes|boolean',
        ]);

        // Additional protection: ensure stock is never negative
        if (isset($validated['stock']) && $validated['stock'] < 0) {
            $validated['stock'] = 0;
        }

        // Only allow changing publish state if the actor is a branch Admin/Owner/SuperAdmin
        if (array_key_exists('is_published', $validated)) {
            if (in_array(strtoupper($user->role), ['ADMIN', 'OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])) {
                $wasPublished = (bool) $product->is_published;
                $nowPublished = (bool) $validated['is_published'];
                $product->is_published = $nowPublished;
                // Record audit info when publishing
                if ($nowPublished && !$wasPublished) {
                    $product->published_by = $user->id;
                    $product->published_at = now();
                }
                // Clear audit info when unpublishing
                if (!$nowPublished && $wasPublished) {
                    $product->published_by = null;
                    $product->published_at = null;
                }
                unset($validated['is_published']);
            }
        }

        // Persist pack fields when provided
        if (array_key_exists('pack_quantity', $validated)) {
            $product->pack_quantity = $validated['pack_quantity'];
            unset($validated['pack_quantity']);
        }
        if (array_key_exists('pack_unit', $validated)) {
            $product->pack_unit = $validated['pack_unit'];
            unset($validated['pack_unit']);
        }

        $product->update($validated);

        if (isset($validated['name'])) {
            $product->slug = Str::slug($validated['name']);
            $product->save();
        }

        // If stock was updated, recompute group real_stock
        if (isset($validated['stock'])) {
            try {
                ProductModel::recomputeRealStockForGroup($product->branch_id, $product->sku, $product->name);
            } catch (\Exception $e) {
                Log::warning('Failed to recompute real_stock after product update', ['error' => $e->getMessage(), 'product_id' => $product->id]);
            }
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }

    // DELETE /api/staff/inventory/products/{id}
    public function destroy(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $branchId = $user->branch_id;

        $product = Product::where('id', $id)
            ->where('branch_id', $branchId)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found or access denied'], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    // GET /api/staff/inventory/profile
    public function profile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $fullName = $user->full_name ?? $user->username ?? $user->email ?? 'Unknown';

        // Generate full avatar URL
        $avatarUrl = null;
        if ($user->avatar_url) {
            if (strpos($user->avatar_url, 'http') === 0) {
                $avatarUrl = $user->avatar_url;
            } else {
                $avatarUrl = url($user->avatar_url);
            }
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'fullName' => $fullName,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'contact' => $user->phone_number,
                'role' => $user->role,
                'department' => $user->department ?? null,
                'accountId' => 'kk' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'avatarUrl' => $avatarUrl,
                'branch_id' => $user->branch_id ?? null,
            ]
        ]);
    }

    // POST /api/staff/inventory/avatar
    public function uploadAvatar(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $file = $request->file('avatar');

        if (!$file) {
            return response()->json(['ok' => false, 'message' => 'No file uploaded'], 400);
        }

        try {
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');
            $storePath = '/storage/' . $path;

            $user->avatar_url = $storePath;
            $user->save();

            return response()->json([
                'ok' => true,
                'avatarUrl' => url($storePath),
            ]);
        } catch (\Exception $ex) {
            return response()->json(['ok' => false, 'message' => 'Failed to upload avatar'], 500);
        }
    }

    // GET /api/staff/inventory/pending-procurements
    public function pendingProcurements(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $branchId = $user->branch_id;

        try {
            $requests = ProcurementRequest::with(['product:id,name,sku,price', 'logisticsUser'])
                ->where('branch_id', $branchId)
                ->where('status', 'awaiting_inventory_confirmation')
                ->orderBy('created_at', 'desc')
                ->get();

            // Map to simple payload expected by frontend
            $payload = $requests->map(function ($r) {
                return [
                    'id' => $r->id,
                    'procurement_request_id' => $r->id,
                    'product_id' => $r->product_id,
                    'product_name' => $r->product?->name,
                    'quantity' => $r->quantity,
                    'requested_quantity' => $r->quantity,
                    'product_stock' => $r->product?->stock ?? 0,
                    'min_stock' => $r->product?->min_stock ?? $r->product?->minimum_stock ?? 10,
                    'product_expires_at' => $r->product?->expires_at ?? null,
                    'price' => $r->price,
                    'receipt_path' => $r->receipt_path ?? null,
                    'supplier_id' => $r->supplier_id ?? null,
                    'supplier_name' => $r->product?->supplier_name ?? null,
                    'created_at' => $r->created_at,
                ];
            });

            return response()->json($payload);
        } catch (\Exception $e) {
            Log::error('Failed to load pending procurements for staff', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch pending procurements'], 500);
        }
    }

    // GET /api/staff/inventory/confirmed-procurements
    public function confirmedProcurements(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $branchId = $user->branch_id;

        try {
            $requests = ProcurementRequest::with(['product', 'procurementUser'])
                ->where('branch_id', $branchId)
                ->where('status', 'completed')
                ->orderBy('updated_at', 'desc')
                ->limit(50)
                ->get();

            $payload = $requests->map(function ($r) {
                return [
                    'id' => $r->id,
                    'product_id' => $r->product_id,
                    'product_name' => $r->product?->name,
                    'quantity' => $r->quantity,
                    'confirmed_by' => $r->procurementUser?->full_name ?? $r->procurementUser?->username ?? null,
                    'confirmed_at' => $r->updated_at,
                ];
            });

            return response()->json($payload);
        } catch (\Exception $e) {
            Log::error('Failed to load confirmed procurements for staff', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch confirmed procurements'], 500);
        }
    }

    // POST /api/staff/inventory/procurements/{id}/confirm-stock
    public function confirmProcurementStock(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $validated = $request->validate([
            'counted_stock' => 'required|integer|min:0',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'notes' => 'nullable|string|max:1000'
        ]);

        $proc = ProcurementRequest::with('product')->find($id);
        if (!$proc) return response()->json(['error' => 'Procurement request not found'], 404);
        if ($proc->branch_id != $user->branch_id) return response()->json(['error' => 'Not your branch'], 403);
        // Accept the new status used by procurement completion flow.
        if ($proc->status !== 'awaiting_inventory_confirmation') return response()->json(['error' => 'Procurement not awaiting inventory confirmation'], 400);

        try {
            DB::transaction(function () use ($proc, $validated, $user, $request) {
                $prod = \App\Models\Product::where('id', $proc->product_id)->lockForUpdate()->first();
                $incrementBy = (int) $validated['counted_stock'];
                if ($incrementBy < 0) $incrementBy = 0;

                $proofPath = null;
                if ($request->hasFile('proof_image')) {
                    $file = $request->file('proof_image');
                    $filename = 'delivery_proof_' . $proc->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('delivery-proofs', $filename, 'public');
                    $proofPath = '/storage/' . $path;
                }

                $variance = $incrementBy - (int) $proc->quantity;
                $varianceReason = null;
                if ($variance !== 0) {
                    $varianceReason = !empty($validated['notes'])
                        ? $validated['notes']
                        : 'Variance: ' . $variance . ' units';
                }

                if ($prod) {
                    // Increment product stock by the counted delivered quantity
                    $prod->increment('stock', $incrementBy);
                    $prod->has_been_ordered = true;
                    $prod->logistics_request_available = false;

                    // Optionally update cost/price similar to procurement completion logic
                    try {
                        $costPerUnit = null;
                        if (!empty($proc->budget_amount) && !empty($proc->quantity)) {
                            $costPerUnit = (float) $proc->budget_amount / max(1, (int)$proc->quantity);
                        } elseif (!empty($proc->price)) {
                            $costPerUnit = (float) $proc->price;
                        }
                        if (!is_null($costPerUnit)) {
                            $prod->cost_price = round($costPerUnit, 2);
                            $prod->price = round($prod->cost_price * 1.10, 2);
                        }
                        $prod->save();

                        // Recompute persisted real_stock for this group after increment
                        try {
                            ProductModel::recomputeRealStockForGroup($prod->branch_id, $prod->sku, $prod->name);
                        } catch (\Exception $e) {
                            Log::warning('Failed to recompute real_stock after procurement stock confirmation', ['error' => $e->getMessage(), 'product_id' => $prod->id]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to apply cost/price update on stock confirmation', ['error' => $e->getMessage(), 'product_id' => $prod->id]);
                    }
                }

                // Mark procurement request completed
                $proc->update([
                    'status' => 'completed',
                    'procurement_user_id' => $user->id,
                    'confirmed_quantity' => $incrementBy,
                    'variance_quantity' => $variance !== 0 ? $variance : null,
                    'variance_reason' => $varianceReason,
                    'variance_reported_at' => $variance !== 0 ? now() : null,
                    'delivery_proof_path' => $proofPath,
                    'updated_at' => now()
                ]);

                // Update logistics transaction with proof and variance
                try {
                    $transaction = LogisticsTransaction::where('procurement_request_id', $proc->id)
                        ->where('type', 'procurement')
                        ->first();
                    if ($transaction) {
                        $transaction->update([
                            'actual_quantity' => $incrementBy,
                            'quantity_verified' => $incrementBy,
                            'variance_reason' => $varianceReason,
                            'proof_of_delivery_path' => $proofPath,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to update logistics transaction details', ['error' => $e->getMessage(), 'proc_req_id' => $proc->id]);
                }

                if ($variance !== 0) {
                    try {
                        $supplierOrder = SupplierOrder::where('procurement_request_id', $proc->id)->first();
                        if ($supplierOrder && $supplierOrder->supplier_id) {
                            SupplierAuditLog::create([
                                'supplier_id' => $supplierOrder->supplier_id,
                                'action' => 'delivery_variance_reported',
                                'description' => 'Delivery variance reported for procurement #' . $proc->id,
                                'triggered_by_user_id' => $user->id,
                                'severity' => 'warning',
                                'metadata' => [
                                    'procurement_request_id' => $proc->id,
                                    'expected_quantity' => (int) $proc->quantity,
                                    'actual_quantity' => $incrementBy,
                                    'variance' => $variance,
                                ],
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to log supplier variance alert', ['error' => $e->getMessage(), 'proc_req_id' => $proc->id]);
                    }
                }

                // Update logistics transaction to verified and completed
                try {
                    $logisticsService = new \App\Services\LogisticsService();
                    $logisticsService->completeProcurement($proc, $user->id, $incrementBy);
                } catch (\Exception $e) {
                    Log::warning('Failed to update logistics transaction on stock confirmation', [
                        'error' => $e->getMessage(),
                        'proc_req_id' => $proc->id
                    ]);
                }

                // Mark supplier order fulfilled if exists
                try {
                    $supplierOrder = SupplierOrder::where('procurement_request_id', $proc->id)->first();
                    if ($supplierOrder) {
                        $supplierOrder->update(['status' => 'fulfilled', 'fulfilled_at' => now()]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to update SupplierOrder after stock confirmation', ['error' => $e->getMessage(), 'proc_req_id' => $proc->id]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to confirm procurement stock', ['error' => $e->getMessage(), 'proc_req_id' => $proc->id]);
            return response()->json(['error' => 'Failed to confirm stock'], 500);
        }

        return response()->json(['ok' => true, 'message' => 'Stock confirmed and product updated']);
    }

    // GET /api/staff/inventory/variance-alerts
    public function varianceAlerts(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $branchId = $user->branch_id;

        try {
            $alerts = LogisticsTransaction::with(['procurementRequest', 'product'])
                ->where('status', 'completed')
                ->whereNotNull('actual_quantity')
                ->whereRaw('actual_quantity != expected_quantity')
                ->when($branchId, function ($q) use ($branchId) {
                    $q->where('destination_branch_id', $branchId);
                })
                ->orderBy('completed_at', 'desc')
                ->limit(25)
                ->get();

            $payload = $alerts->map(function ($t) {
                $variance = null;
                try {
                    $variance = $t->getVariance();
                } catch (\Exception $e) {
                    $variance = null;
                }

                return [
                    'transaction_id' => $t->id,
                    'procurement_request_id' => $t->procurement_request_id,
                    'product_id' => $t->product_id,
                    'product_name' => $t->product?->name,
                    'expected_quantity' => $t->expected_quantity,
                    'actual_quantity' => $t->actual_quantity,
                    'variance' => $variance,
                    'variance_reason' => $t->variance_reason,
                    'proof_of_delivery_path' => $t->proof_of_delivery_path,
                    'completed_at' => $t->completed_at,
                ];
            });

            return response()->json(['ok' => true, 'data' => $payload]);
        } catch (\Exception $e) {
            Log::error('Failed to load variance alerts', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch variance alerts'], 500);
        }
    }
}
