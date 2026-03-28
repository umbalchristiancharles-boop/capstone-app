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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $query = Product::where('branch_id', $branchId)->where('is_active', 1);

        // Allow callers to request unpublished products as well (useful for internal staff views)
        $includeUnpublished = $request->boolean('include_unpublished', false);

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

        $products = $query->select('id', 'name', 'slug', 'price', 'stock', 'sku', 'branch_id', 'is_published', 'created_at', 'updated_at')
            ->orderBy('name')
            ->get();

        return response()->json($products);
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
            'expires_at' => 'required|date_format:Y-m-d\TH:i',
            'sku' => 'nullable|string|unique:products,sku',
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

        $isPublished = 1;
        if ($user && strtoupper($user->role) === 'SUPPLIER') {
            // Supplier-submitted products stay unpublished until procurement places order
            $isPublished = 0;
        }

        // Check if this is a kitchen dish (wrapped in try-catch to avoid timeouts)
        $isKitchenDish = false;
        try {
            $isKitchenDish = Dish::whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($validated['name']))])
                ->where('branch_id', $branchId)
                ->exists();
        } catch (\Exception $e) {
            \Log::warning('Failed to check if product is kitchen dish', ['error' => $e->getMessage()]);
            $isKitchenDish = false;
        }

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'price' => $validated['price'],
            'cost_price' => $validated['price'],
            'stock' => $stock,
            'sku' => $sku,
            'category' => $validated['category'],
            'per_pack_or_individual' => $validated['per_pack_or_individual'],
            'expires_at' => $validated['expires_at'],
            'branch_id' => $branchId,
            'supplier_id' => $user->id,
            'supplier_name' => $supplierName,
            'is_published' => $isPublished,
            'is_active' => true,
            'is_kitchen_dish' => $isKitchenDish,
        ]);

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
            'expires_at' => 'sometimes|date_format:Y-m-d\TH:i',
        ]);

        // Additional protection: ensure stock is never negative
        if (isset($validated['stock']) && $validated['stock'] < 0) {
            $validated['stock'] = 0;
        }

        $product->update($validated);

        if (isset($validated['name'])) {
            $product->slug = Str::slug($validated['name']);
            $product->save();
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
                    'price' => $r->price,
                    'receipt_path' => $r->receipt_path ?? null,
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
            'counted_stock' => 'required|integer|min:0'
        ]);

        $proc = ProcurementRequest::with('product')->find($id);
        if (!$proc) return response()->json(['error' => 'Procurement request not found'], 404);
        if ($proc->branch_id != $user->branch_id) return response()->json(['error' => 'Not your branch'], 403);
        // Accept the new status used by procurement completion flow.
        if ($proc->status !== 'awaiting_inventory_confirmation') return response()->json(['error' => 'Procurement not awaiting inventory confirmation'], 400);

        try {
            DB::transaction(function () use ($proc, $validated, $user) {
                $prod = \App\Models\Product::where('id', $proc->product_id)->lockForUpdate()->first();
                if ($prod) {
                    // Increment product stock by the counted delivered quantity
                    $incrementBy = (int) $validated['counted_stock'];
                    if ($incrementBy < 0) $incrementBy = 0;
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
                    } catch (\Exception $e) {
                        Log::warning('Failed to apply cost/price update on stock confirmation', ['error' => $e->getMessage(), 'product_id' => $prod->id]);
                    }
                }

                // Mark procurement request completed
                $proc->update([
                    'status' => 'completed',
                    'procurement_user_id' => $user->id,
                    'updated_at' => now()
                ]);

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
}
