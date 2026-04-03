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

        // Only owner role can approve
        if (!$this->isOwner($user)) {
            return response()->json(['error' => 'Only Owner can access this'], 403);
        }

        $products = Product::where('status', 'pending_owner')
            ->with(['supplier', 'branch', 'logisticsApprover', 'ownerApprover'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

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

        // Only owner can approve
        if (!$this->isOwner($user)) {
            return response()->json(['error' => 'Only Owner can approve at this stage'], 403);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Product must be pending owner approval
        if ($product->status !== 'pending_owner') {
            return response()->json(['error' => 'Product is not pending owner approval'], 400);
        }

        try {
            $product->update([
                'status' => 'approved',
                'approved_by_owner' => $user->id,
                'approved_at' => now(),
                'is_published' => true, // Mark as published when approved
            ]);

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

        // Only owner can reject
        if (!$this->isOwner($user)) {
            return response()->json(['error' => 'Only Owner can reject at this stage'], 403);
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
