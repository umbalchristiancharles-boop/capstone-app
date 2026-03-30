<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductRequest;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductRequestController extends Controller
{
    /**
     * Get all product requests for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        $query = ProductRequest::with('requester', 'approver', 'branch', 'product')
            ->orderBy('created_at', 'desc');

        // Logistics manager sees their own requests + pending requests if main branch
        if (in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS']) || ($role === 'MANAGER' && $dept === 'LOGISTICS')) {
            $isMainBranch = false;
            try {
                if ($user->branch_id) {
                    $branch = Branch::find($user->branch_id);
                    if ($branch) {
                        $branchName = strtoupper(trim((string) ($branch->name ?? '')));
                        $isMainBranch = ((int) $branch->id === 1) || str_contains($branchName, 'MAIN BRANCH');
                    }
                }
            } catch (\Exception $e) {
                $isMainBranch = false;
            }

            if (!$isMainBranch) {
                $query->where('requested_by', $user->id);
            } else {
                // Main branch logistics can see all requests from their branches
                $query->where('branch_id', $user->branch_id);
            }
        } else {
            // Default to user's branch
            $query->where('branch_id', $user->branch_id ?? 1);
        }

        $requests = $query->paginate(20);
        return response()->json($requests);
    }

    /**
     * Create a new product request (Logistics Manager)
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        // Allow logistics managers and inventory staff to request new products
        $isLogistics = in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS']) || ($role === 'MANAGER' && $dept === 'LOGISTICS') || ($role === 'STAFF' && $dept === 'INVENTORY');
        if (!$isLogistics) {
            return response()->json(['error' => 'Only logistics managers can request new products'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'unit' => 'nullable|string|max:50',
        ]);

        try {
            $productRequest = ProductRequest::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'unit' => $validated['unit'] ?? null,
                'requested_by' => $user->id,
                'branch_id' => $user->branch_id ?? 1,
                'approval_status' => 'pending_approval',
            ]);

            Log::info('Product request created by logistics manager', [
                'product_request_id' => $productRequest->id,
                'user_id' => $user->id,
                'branch_id' => $productRequest->branch_id,
            ]);

            return response()->json($productRequest->load('requester', 'branch'), 201);
        } catch (\Exception $e) {
            Log::error('Failed to create product request', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return response()->json(['error' => 'Failed to create product request'], 500);
        }
    }

    /**
     * Get pending product requests for approval (Owner/Main Branch Logistics)
     */
    public function getPendingRequests(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Only owner or SUPER_ADMIN can approve
        if ($role !== 'OWNER' && $role !== 'SUPER_ADMIN') {
            return response()->json(['error' => 'Unauthorized to approve product requests'], 403);
        }

        $query = ProductRequest::where('approval_status', 'pending_approval')
            ->with('requester', 'branch');

        // Owner sees requests from their branch
        if ($role === 'OWNER' && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($requests);
    }

    /**
     * Approve a product request and create the product (Owner)
     */
    public function approveRequest(Request $request, $id)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Only owner or SUPER_ADMIN can approve
        if ($role !== 'OWNER' && $role !== 'SUPER_ADMIN') {
            return response()->json(['error' => 'Unauthorized to approve product requests'], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $productRequest = ProductRequest::findOrFail($id);

            // Check branch access for owner
            if ($role === 'OWNER' && $user->branch_id && $productRequest->branch_id !== $user->branch_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Verify request is pending approval
            if ($productRequest->approval_status !== 'pending_approval') {
                return response()->json([
                    'error' => 'This product request is not pending approval',
                ], 400);
            }

            // Create the product
            try {
                $slug = Str::slug($productRequest->name . '-' . $productRequest->id . '-' . time());
                $product = Product::create([
                    'name' => $productRequest->name,
                    'slug' => $slug,
                    'price' => 0,
                    'cost_price' => 0,
                    'stock' => 0,
                    'min_stock' => 0,
                    'sku' => 'LOGISTICS-REQUEST-' . $productRequest->id . '-' . mt_rand(1000, 9999),
                    'branch_id' => $productRequest->branch_id ?? 1,
                    'supplier_name' => 'REQUESTED',
                    'supplier_id' => null,
                    'is_published' => false,
                    'is_active' => true,
                    'is_kitchen_dish' => false,
                    'has_been_ordered' => false,
                    'logistics_request_available' => true,
                ]);

                // Update product request with approval info
                $productRequest->update([
                    'approval_status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'approval_notes' => $validated['notes'] ?? null,
                    'product_id' => $product->id,
                ]);

                Log::info('Product request approved and product created', [
                    'product_request_id' => $productRequest->id,
                    'product_id' => $product->id,
                    'approved_by' => $user->id,
                    'branch_id' => $productRequest->branch_id,
                ]);

                return response()->json($productRequest->load('approver', 'requester', 'product', 'branch'));
            } catch (\Exception $e) {
                Log::error('Failed to create product from request', [
                    'product_request_id' => $id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json(['error' => 'Failed to create product: ' . $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to approve product request', [
                'product_request_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to approve product request'], 500);
        }
    }

    /**
     * Reject a product request (Owner)
     */
    public function rejectRequest(Request $request, $id)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Only owner or SUPER_ADMIN can reject
        if ($role !== 'OWNER' && $role !== 'SUPER_ADMIN') {
            return response()->json(['error' => 'Unauthorized to reject product requests'], 403);
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        try {
            $productRequest = ProductRequest::findOrFail($id);

            // Check branch access for owner
            if ($role === 'OWNER' && $user->branch_id && $productRequest->branch_id !== $user->branch_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Verify request is pending approval
            if ($productRequest->approval_status !== 'pending_approval') {
                return response()->json([
                    'error' => 'This product request is not pending approval',
                ], 400);
            }

            // Update product request with rejection info
            $productRequest->update([
                'approval_status' => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'approval_notes' => $validated['notes'],
            ]);

            Log::info('Product request rejected', [
                'product_request_id' => $productRequest->id,
                'rejected_by' => $user->id,
                'branch_id' => $productRequest->branch_id,
            ]);

            return response()->json($productRequest->load('approver', 'requester', 'branch'));
        } catch (\Exception $e) {
            Log::error('Failed to reject product request', [
                'product_request_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to reject product request'], 500);
        }
    }
}
