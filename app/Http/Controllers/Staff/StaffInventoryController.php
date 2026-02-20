<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StaffInventoryController extends Controller
{
    // PUT /api/staff/inventory/profile
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $validated = $request->validate([
            'fullName' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:50|unique:users,username,' . $user->id,
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'contact' => 'sometimes|string|max:50',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        if (isset($validated['fullName'])) $user->full_name = $validated['fullName'];
        if (isset($validated['username'])) $user->username = $validated['username'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        if (isset($validated['contact'])) $user->phone_number = $validated['contact'];
        if (isset($validated['password'])) $user->password = $validated['password'];
        $user->save();
        return response()->json(['ok' => true, 'message' => 'Profile updated successfully.']);
    }
    // GET /api/staff/inventory/products
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $branchId = $user->branch_id;

        $products = Product::where('branch_id', $branchId)
            ->select('id', 'name', 'slug', 'price', 'stock', 'sku', 'branch_id', 'created_at', 'updated_at')
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
            'sku' => 'required|string|unique:products,sku',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $branchId = $user->branch_id;

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'sku' => $validated['sku'],
            'branch_id' => $branchId,
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
        // Attempt to get profile info from related UserProfile if available
        // Use only User model fields for name and department
        $fullName = $user->full_name ?? $user->username ?? $user->email ?? 'Unknown';
        return response()->json([
            'user' => [
                'id' => $user->id,
                'fullName' => $fullName,
                'role' => $user->role,
                'department' => $user->department ?? null,
                'accountId' => $user->account_id ?? null,
                'avatarUrl' => $user->avatar_url ?? null,
                'branch_id' => $user->branch_id ?? null,
            ]
        ]);
    }
}
