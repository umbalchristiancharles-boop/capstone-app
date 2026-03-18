<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

$query = Product::where('branch_id', $branchId)->where('is_published', 1);

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
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku',
        ]);

        // Additional protection: ensure stock is never negative
        if ($validated['stock'] < 0) {
            $validated['stock'] = 0;
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

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'sku' => $sku,
            'branch_id' => $branchId,
            'supplier_name' => $supplierName,
            'is_published' => $isPublished,
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
}
