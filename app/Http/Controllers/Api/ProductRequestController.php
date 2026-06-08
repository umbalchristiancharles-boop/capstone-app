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
     * Create a new product request (Inventory Staff)
     * New multi-level workflow: sets status = 'pending_logistics'
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        Log::info('[ProductRequest] Store attempt', [
            'user_id' => $user->id,
            'role' => $role,
            'department' => $dept,
            'branch_id' => $user->branch_id,
        ]);

        // Allow logistics managers, inventory staff, and branch admins to request new products
        $isLogistics = in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS', 'ADMIN']) || ($role === 'MANAGER' && $dept === 'LOGISTICS') || ($role === 'STAFF' && $dept === 'INVENTORY');
        if (!$isLogistics) {
            Log::warning('[ProductRequest] Authorization failed', [
                'user_id' => $user->id,
                'role' => $role,
                'dept' => $dept,
            ]);
            return response()->json(['error' => 'Only logistics managers, inventory staff, and admins can request new products'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'unit' => 'nullable|string|max:50',
        ]);

        try {
            Log::info('[ProductRequest] Creating product request', [
                'name' => $validated['name'],
                'requested_by' => $user->id,
                'branch_id' => $user->branch_id ?? 1,
            ]);

            $productRequest = ProductRequest::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'unit' => $validated['unit'] ?? null,
                'requested_by' => $user->id,
                'branch_id' => $user->branch_id ?? 1,
                'approval_status' => 'pending_approval',
                'status' => 'pending_logistics',  // Multi-level workflow
            ]);

            Log::info('[ProductRequest] Created successfully', [
                'product_request_id' => $productRequest->id,
                'user_id' => $user->id,
                'branch_id' => $productRequest->branch_id,
                'user_role' => $role,
                'user_dept' => $dept,
                'status' => $productRequest->status,
            ]);

            $response = $productRequest->load('requester', 'branch');
            return response()->json($response, 201);
        } catch (\Exception $e) {
            Log::error('[ProductRequest] Creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to create product request: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get pending product requests for approval (Owner/Main Branch Logistics)
     */
    public function getPendingRequests(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Allow OWNER, SUPER_ADMIN, or branch ADMIN to view pending requests
        if (!in_array($role, ['OWNER', 'SUPER_ADMIN', 'ADMIN'])) {
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

        // Only OWNER, SUPER_ADMIN, or branch ADMIN can approve
        if (!in_array($role, ['OWNER', 'SUPER_ADMIN', 'ADMIN'])) {
            return response()->json(['error' => 'Unauthorized to approve product requests'], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $productRequest = ProductRequest::findOrFail($id);

            // Branch-level access: OWNER and ADMIN are scoped to their branch
            if (in_array($role, ['OWNER', 'ADMIN']) && $user->branch_id && $productRequest->branch_id !== $user->branch_id) {
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
     * Get product requests pending logistics approval (Main Branch Logistics)
     */
    public function getPendingLogisticsApproval(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        // Only logistics managers from main branch can approve
        $isLogistics = in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS']) || ($role === 'MANAGER' && $dept === 'LOGISTICS');
        if (!$isLogistics) {
            return response()->json(['error' => 'Unauthorized - logistics manager required'], 403);
        }

        // Check if user is from main branch
        $isMainBranch = false;
        try {
            if ($user->branch_id) {
                $branch = Branch::find($user->branch_id);
                if ($branch) {
                    $branchName = strtoupper(trim((string) ($branch->name ?? '')));
                    $isMainBranch = ((int) $branch->id === 1) || str_contains($branchName, 'MAIN') || $branch->is_main_branch;
                }
            }
        } catch (\Exception $e) {
            $isMainBranch = false;
        }

        if (!$isMainBranch) {
            return response()->json(['error' => 'Only main branch logistics can approve'], 403);
        }

        $requests = ProductRequest::where('status', 'pending_logistics')
            ->with('requester', 'branch', 'product')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return response()->json($requests);
    }

    /**
     * Get product requests pending owner approval
     */
    public function getPendingOwnerApproval(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Only owner or SUPER_ADMIN can approve
        if ($role !== 'OWNER' && $role !== 'SUPER_ADMIN') {
            return response()->json(['error' => 'Unauthorized - owner required'], 403);
        }

        $query = ProductRequest::where('status', 'pending_owner')
            ->with('requester', 'logisticsApprover', 'branch', 'product')
            ->orderBy('created_at', 'asc');

        // Owner sees requests from their branch
        if ($role === 'OWNER' && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        $requests = $query->paginate(20);
        return response()->json($requests);
    }

    /**
     * Get approved product requests for owner view
     */
    public function getOwnerApprovedRequests(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        if ($role !== 'OWNER' && $role !== 'SUPER_ADMIN') {
            return response()->json(['error' => 'Unauthorized - owner required'], 403);
        }

        $query = ProductRequest::where('status', 'approved')
            ->with('requester', 'logisticsApprover', 'ownerApprover', 'product', 'branch')
            ->orderBy('approved_at', 'desc');

        if ($role === 'OWNER' && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        $requests = $query->paginate(20);
        return response()->json($requests);
    }

    /**
     * Approve at logistics level - moves to pending_owner
     */
    public function approveAtLogistics(Request $request, $id)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        // Only logistics managers from main branch
        $isLogistics = in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS']) || ($role === 'MANAGER' && $dept === 'LOGISTICS');
        if (!$isLogistics) {
            return response()->json(['error' => 'Unauthorized - logistics manager required'], 403);
        }

        // Check if user is from main branch
        $isMainBranch = false;
        try {
            if ($user->branch_id) {
                $branch = Branch::find($user->branch_id);
                if ($branch) {
                    $branchName = strtoupper(trim((string) ($branch->name ?? '')));
                    $isMainBranch = ((int) $branch->id === 1) || str_contains($branchName, 'MAIN') || $branch->is_main_branch;
                }
            }
        } catch (\Exception $e) {
            $isMainBranch = false;
        }

        if (!$isMainBranch) {
            return response()->json(['error' => 'Only main branch logistics can approve'], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $productRequest = ProductRequest::findOrFail($id);

            // Verify request is pending logistics approval
            if ($productRequest->status !== 'pending_logistics') {
                return response()->json(['error' => 'This request is not pending logistics approval'], 400);
            }

            // Update to pending owner
            $productRequest->update([
                'status' => 'pending_owner',
                'approved_by_logistics' => $user->id,
                'logistics_approval_notes' => $validated['notes'] ?? null,
            ]);

            Log::info('Product request approved at logistics level', [
                'product_request_id' => $productRequest->id,
                'approved_by_logistics' => $user->id,
                'branch_id' => $productRequest->branch_id,
            ]);

            return response()->json($productRequest->load('requester', 'logisticsApprover', 'branch'));
        } catch (\Exception $e) {
            Log::error('Failed to approve at logistics level', [
                'product_request_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to approve at logistics level'], 500);
        }
    }

    /**
     * Reject at logistics level
     */
    public function rejectAtLogistics(Request $request, $id)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        // Only logistics managers from main branch
        $isLogistics = in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS']) || ($role === 'MANAGER' && $dept === 'LOGISTICS');
        if (!$isLogistics) {
            return response()->json(['error' => 'Unauthorized - logistics manager required'], 403);
        }

        // Check if user is from main branch
        $isMainBranch = false;
        try {
            if ($user->branch_id) {
                $branch = Branch::find($user->branch_id);
                if ($branch) {
                    $branchName = strtoupper(trim((string) ($branch->name ?? '')));
                    $isMainBranch = ((int) $branch->id === 1) || str_contains($branchName, 'MAIN') || $branch->is_main_branch;
                }
            }
        } catch (\Exception $e) {
            $isMainBranch = false;
        }

        if (!$isMainBranch) {
            return response()->json(['error' => 'Only main branch logistics can reject'], 403);
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        try {
            $productRequest = ProductRequest::findOrFail($id);

            // Verify request is pending logistics approval
            if ($productRequest->status !== 'pending_logistics') {
                return response()->json(['error' => 'This request is not pending logistics approval'], 400);
            }

            // Mark as rejected
            $productRequest->update([
                'status' => 'rejected',
                'approved_by_logistics' => $user->id,
                'logistics_approval_notes' => $validated['notes'],
                'rejected_at' => now(),
            ]);

            Log::info('Product request rejected at logistics level', [
                'product_request_id' => $productRequest->id,
                'rejected_by_logistics' => $user->id,
                'branch_id' => $productRequest->branch_id,
            ]);

            return response()->json($productRequest->load('requester', 'logisticsApprover', 'branch'));
        } catch (\Exception $e) {
            Log::error('Failed to reject at logistics level', [
                'product_request_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to reject at logistics level'], 500);
        }
    }

    /**
     * Approve at owner level - final approval, creates product
     */
    public function approveAtOwner(Request $request, $id)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Only OWNER, SUPER_ADMIN, or branch ADMIN
        if (!in_array($role, ['OWNER', 'SUPER_ADMIN', 'ADMIN'])) {
            return response()->json(['error' => 'Unauthorized - owner or branch admin required'], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $productRequest = ProductRequest::findOrFail($id);

            // Branch-level access: OWNER and ADMIN are scoped to their branch
            if (in_array($role, ['OWNER', 'ADMIN']) && $user->branch_id && $productRequest->branch_id !== $user->branch_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Verify request is pending owner approval
            if ($productRequest->status !== 'pending_owner') {
                return response()->json(['error' => 'This request is not pending owner approval'], 400);
            }

            // Create the product
            try {
                $slug = Str::slug($productRequest->name . '-' . $productRequest->id . '-' . time());
                $product = Product::create([
                    'name' => $productRequest->name,
                    'slug' => $slug,
                    'description' => $productRequest->description,
                    'price' => 0,
                    'cost_price' => 0,
                    'stock' => 0,
                    'min_stock' => 0,
                    'sku' => 'PRODUCT-REQ-' . $productRequest->id . '-' . mt_rand(1000, 9999),
                    'branch_id' => $productRequest->branch_id ?? 1,
                    'supplier_name' => 'TO BE ASSIGNED',
                    'supplier_id' => null,
                    'is_published' => true,  // Published after owner approval
                    'is_active' => true,
                    'is_kitchen_dish' => false,
                    'has_been_ordered' => false,
                    'logistics_request_available' => true,
                ]);

                // Mark request as approved with owner info
                $productRequest->update([
                    'status' => 'approved',
                    'approval_status' => 'approved',  // Keep for backward compatibility
                    'approved_by_owner' => $user->id,
                    'owner_approval_notes' => $validated['notes'] ?? null,
                    'approved_by' => $user->id,  // Keep for backward compatibility
                    'approved_at' => now(),
                    'product_id' => $product->id,
                ]);

                Log::info('Product request approved at owner level and product created', [
                    'product_request_id' => $productRequest->id,
                    'product_id' => $product->id,
                    'approved_by_owner' => $user->id,
                    'branch_id' => $productRequest->branch_id,
                ]);

                return response()->json($productRequest->load('requester', 'logisticsApprover', 'ownerApprover', 'product', 'branch'));
            } catch (\Exception $e) {
                Log::error('Failed to create product from request', [
                    'product_request_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json(['error' => 'Failed to create product: ' . $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to approve at owner level', [
                'product_request_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to approve at owner level'], 500);
        }
    }

    /**
     * Reject at owner level
     */
    public function rejectAtOwner(Request $request, $id)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Only owner or SUPER_ADMIN
        if ($role !== 'OWNER' && $role !== 'SUPER_ADMIN') {
            return response()->json(['error' => 'Unauthorized - owner required'], 403);
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

            // Verify request is pending owner approval
            if ($productRequest->status !== 'pending_owner') {
                return response()->json(['error' => 'This request is not pending owner approval'], 400);
            }

            // Mark as rejected
            $productRequest->update([
                'status' => 'rejected',
                'approval_status' => 'rejected',  // Keep for backward compatibility
                'approved_by_owner' => $user->id,
                'owner_approval_notes' => $validated['notes'],
                'rejected_at' => now(),
            ]);

            Log::info('Product request rejected at owner level', [
                'product_request_id' => $productRequest->id,
                'rejected_by_owner' => $user->id,
                'branch_id' => $productRequest->branch_id,
            ]);

            return response()->json($productRequest->load('requester', 'logisticsApprover', 'ownerApprover', 'branch'));
        } catch (\Exception $e) {
            Log::error('Failed to reject at owner level', [
                'product_request_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to reject at owner level'], 500);
        }
    }

    /**
     * Reject a product request (Owner) - Backward compatibility
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
