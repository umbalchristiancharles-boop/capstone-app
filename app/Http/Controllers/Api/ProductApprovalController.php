<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductApprovalController extends Controller
{
    /**
     * Get products pending logistics approval
     */
    public function getPendingLogisticsApproval(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Only logistics main branch users can approve
        if (!$this->isLogisticsMainBranch($user)) {
            return response()->json(['error' => 'Only Logistics Main Branch can access this'], 403);
        }

        $products = Product::where('status', 'pending_logistics_main')
            ->with(['supplier', 'branch', 'logisticsApprover', 'ownerApprover'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($products);
    }

    /**
     * Get products pending owner approval
     */
    public function getPendingOwnerApproval(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        // Allow OWNER, SUPER_ADMIN, or branch ADMIN to view pending owner approvals
        $role = strtoupper($user->role ?? '');
        if (!in_array($role, ['OWNER', 'SUPER_ADMIN', 'ADMIN'])) {
            return response()->json(['error' => 'Only Owner or branch Admin can access this'], 403);
        }

        $query = Product::where('status', 'pending_owner')
            ->with(['supplier', 'branch', 'logisticsApprover', 'ownerApprover']);

        // Scope to branch for OWNER and ADMIN
        if (in_array($role, ['OWNER', 'ADMIN']) && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($products);
    }

    /**
     * Approve product at logistics main branch level
     */
    public function approveAtLogistics(Request $request, $productId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Only logistics main branch users can approve
        if (!$this->isLogisticsMainBranch($user)) {
            return response()->json(['error' => 'Only Logistics Main Branch can approve at this stage'], 403);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Product must be pending logistics approval
        if ($product->status !== 'pending_logistics_main') {
            return response()->json(['error' => 'Product is not pending logistics approval'], 400);
        }

        try {
            $product->update([
                'status' => 'pending_owner',
                'approved_by_logistics_main' => $user->id,
            ]);

            Log::info('Product approved by logistics main branch', [
                'product_id' => $product->id,
                'approved_by' => $user->id,
            ]);

            return response()->json([
                'message' => 'Product approved by logistics main branch',
                'product' => $product->load(['supplier', 'branch', 'logisticsApprover', 'ownerApprover']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving product at logistics', ['error' => $e->getMessage(), 'product_id' => $productId]);
            return response()->json(['error' => 'Failed to approve product'], 500);
        }
    }

    /**
     * Reject product at logistics main branch level
     */
    public function rejectAtLogistics(Request $request, $productId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Only logistics main branch users can reject
        if (!$this->isLogisticsMainBranch($user)) {
            return response()->json(['error' => 'Only Logistics Main Branch can reject at this stage'], 403);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Product must be pending logistics approval
        if ($product->status !== 'pending_logistics_main') {
            return response()->json(['error' => 'Product is not pending logistics approval'], 400);
        }

        try {
            $product->update([
                'status' => 'rejected',
                'approved_by_logistics_main' => $user->id,
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            Log::info('Product rejected by logistics main branch', [
                'product_id' => $product->id,
                'rejected_by' => $user->id,
                'reason' => $validated['rejection_reason'],
            ]);

            return response()->json([
                'message' => 'Product rejected by logistics main branch',
                'product' => $product->load(['supplier', 'branch', 'logisticsApprover', 'ownerApprover']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting product at logistics', ['error' => $e->getMessage(), 'product_id' => $productId]);
            return response()->json(['error' => 'Failed to reject product'], 500);
        }
    }

    /**
     * Approve product at owner level (final approval)
     */
    public function approveAtOwner(Request $request, $productId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Only OWNER, SUPER_ADMIN, or branch ADMIN can approve
        $role = strtoupper($user->role ?? '');
        if (!in_array($role, ['OWNER', 'SUPER_ADMIN', 'ADMIN'])) {
            return response()->json(['error' => 'Only Owner or branch Admin can approve at this stage'], 403);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Product must be pending owner approval
        if ($product->status !== 'pending_owner') {
            return response()->json(['error' => 'Product is not pending owner approval'], 400);
        }

        // Branch-level restriction for OWNER/ADMIN
        if (in_array($role, ['OWNER', 'ADMIN']) && $user->branch_id && $product->branch_id !== $user->branch_id) {
            return response()->json(['error' => 'Unauthorized to approve product from another branch'], 403);
        }

        try {
            $update = [
                'status' => 'approved',
                'approved_by_owner' => $user->id,
                'approved_at' => now(),
            ];

            // When approved by an owner/admin, mark published so it becomes available
            $update['is_published'] = true;

            $product->update($update);

            Log::info('Product approved by owner', [
                'product_id' => $product->id,
                'approved_by' => $user->id,
            ]);

            return response()->json([
                'message' => 'Product approved and is now available in inventory',
                'product' => $product->load(['supplier', 'branch', 'logisticsApprover', 'ownerApprover']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving product at owner level', ['error' => $e->getMessage(), 'product_id' => $productId]);
            return response()->json(['error' => 'Failed to approve product'], 500);
        }
    }

    /**
     * Reject product at owner level
     */
    public function rejectAtOwner(Request $request, $productId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Only OWNER, SUPER_ADMIN, or branch ADMIN can reject
        $role = strtoupper($user->role ?? '');
        if (!in_array($role, ['OWNER', 'SUPER_ADMIN', 'ADMIN'])) {
            return response()->json(['error' => 'Only Owner or branch Admin can reject at this stage'], 403);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Product must be pending owner approval
        if ($product->status !== 'pending_owner') {
            return response()->json(['error' => 'Product is not pending owner approval'], 400);
        }

        // Branch-level restriction for OWNER/ADMIN
        if (in_array($role, ['OWNER', 'ADMIN']) && $user->branch_id && $product->branch_id !== $user->branch_id) {
            return response()->json(['error' => 'Unauthorized to reject product from another branch'], 403);
        }

        try {
            $product->update([
                'status' => 'rejected',
                'approved_by_owner' => $user->id,
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            Log::info('Product rejected by owner', [
                'product_id' => $product->id,
                'rejected_by' => $user->id,
                'reason' => $validated['rejection_reason'],
            ]);

            return response()->json([
                'message' => 'Product rejected',
                'product' => $product->load(['supplier', 'branch', 'logisticsApprover', 'ownerApprover']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting product at owner level', ['error' => $e->getMessage(), 'product_id' => $productId]);
            return response()->json(['error' => 'Failed to reject product'], 500);
        }
    }

    /**
     * Check if user is logistics main branch
     */
    private function isLogisticsMainBranch($user): bool
    {
        if (!$user) return false;

        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        // Must have logistics role in logistics department
        if (!in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS', 'MANAGER']) || $dept !== 'LOGISTICS') {
            return false;
        }

        // Must be from main branch (ID 1 or name contains "MAIN")
        try {
            if ($user->branch_id) {
                $branch = $user->branch;
                if ($branch) {
                    $branchName = strtoupper(trim((string) ($branch->name ?? '')));
                    if ((int) $branch->id === 1 || str_contains($branchName, 'MAIN BRANCH')) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Check if user is owner
     */
    private function isOwner($user): bool
    {
        if (!$user) return false;

        $role = strtoupper($user->role ?? '');
        return $role === 'OWNER';
    }
}
