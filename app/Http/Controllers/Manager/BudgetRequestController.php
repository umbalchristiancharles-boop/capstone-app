<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\BudgetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetRequestController extends Controller
{
    /**
     * Check if user is authenticated and is a manager
     */
    private function getAuthenticatedManager(Request $request)
    {
        if (Auth::check()) {
            return Auth::user();
        }

        $userId = $request->session()->get('user_id');
        if ($userId) {
            return \App\Models\User::find($userId);
        }

        return null;
    }

    /**
     * Check if user is a manager
     */
    private function isManager($user)
    {
        if (!$user) {
            return false;
        }

        $role = strtoupper($user->role ?? '');
        return in_array($role, ['MANAGER', 'MANAGER_HR', 'BRANCH_MANAGER']);
    }

    /**
     * Check if user has access to specific department
     */
    private function hasDepartmentAccess($user, $department)
    {
        if (!$user) {
            return false;
        }

        $userDept = strtoupper($user->department ?? '');
        $targetDept = strtoupper($department);

        return $userDept === $targetDept;
    }

    // ==========================================
    // LOGISTICS MANAGER ENDPOINTS
    // ==========================================

    /**
     * Get logistics manager's inventory (read-only)
     */
    public function getInventory(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'logistics')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branchId = $user->branch_id;

        if (!$branchId) {
            return response()->json([
                'ok' => false,
                'message' => 'No branch assigned'
            ], 400);
        }

        // Get products for this branch - READ ONLY
        $products = \App\Models\Product::where('branch_id', $branchId)
            ->where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($product) {
                $stock = $product->stock ?? 0;
                $minStock = $product->min_stock ?? 0;
                $status = $stock <= $minStock ? 'LOW STOCK' : 'OK';

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $stock,
                    'min_stock' => $minStock,
                    'status' => $status,
                ];
            });

        return response()->json([
            'ok' => true,
            'products' => $products
        ]);
    }

    /**
     * Get logistics manager's budget requests (own requests only)
     */
    public function getMyRequests(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'logistics')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branchId = $user->branch_id;

        $requests = BudgetRequest::where('branch_id', $branchId)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'branch_id' => $req->branch_id,
                    'purpose' => $req->purpose,
                    'requested_amount' => number_format($req->requested_amount, 2),
                    'status' => $req->status,
                    'date_requested' => $req->date_requested,
                    'processed_by' => $req->processor ? $req->processor->full_name : null,
                    'date_processed' => $req->date_processed,
                    'created_at' => $req->created_at,
                ];
            });

        return response()->json([
            'ok' => true,
            'requests' => $requests
        ]);
    }

    /**
     * Create a new budget request (Logistics Manager)
     */
    public function createRequest(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'logistics')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'purpose' => 'required|string|max:500',
            'requested_amount' => 'required|numeric|min:1',
        ]);

        $branchId = $user->branch_id;

        if (!$branchId) {
            return response()->json([
                'ok' => false,
                'message' => 'No branch assigned'
            ], 400);
        }

        $budgetRequest = BudgetRequest::create([
            'branch_id' => $branchId,
            'user_id' => $user->id,
            'purpose' => $validated['purpose'],
            'requested_amount' => $validated['requested_amount'],
            'status' => 'Pending',
            'date_requested' => now()->toDateString(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Budget request created successfully',
            'request' => [
                'id' => $budgetRequest->id,
                'purpose' => $budgetRequest->purpose,
                'requested_amount' => number_format($budgetRequest->requested_amount, 2),
                'status' => $budgetRequest->status,
                'date_requested' => $budgetRequest->date_requested,
            ]
        ], 201);
    }

    // ==========================================
    // FINANCE MANAGER ENDPOINTS
    // ==========================================

    /**
     * Get all budget requests for Finance Manager's branch
     */
    public function getAllRequests(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'finance')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branchId = $user->branch_id;

        $requests = BudgetRequest::where('branch_id', $branchId)
            ->with(['user:id,full_name', 'processor:id,full_name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'branch_id' => $req->branch_id,
                    'requester_name' => $req->user ? $req->user->full_name : 'Unknown',
                    'purpose' => $req->purpose,
                    'requested_amount' => number_format($req->requested_amount, 2),
                    'status' => $req->status,
                    'date_requested' => $req->date_requested,
                    'processed_by' => $req->processor ? $req->processor->full_name : null,
                    'date_processed' => $req->date_processed,
                    'created_at' => $req->created_at,
                ];
            });

        return response()->json([
            'ok' => true,
            'requests' => $requests
        ]);
    }

    /**
     * Approve a budget request (Finance Manager)
     */
    public function approveRequest(Request $request, $id)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'finance')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branchId = $user->branch_id;

        $budgetRequest = BudgetRequest::where('id', $id)
            ->where('branch_id', $branchId)
            ->where('status', 'Pending')
            ->first();

        if (!$budgetRequest) {
            return response()->json([
                'ok' => false,
                'message' => 'Budget request not found or already processed'
            ], 404);
        }

        $budgetRequest->update([
            'status' => 'Approved',
            'processed_by' => $user->id,
            'date_processed' => now()->toDateString(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Budget request approved successfully',
            'request' => [
                'id' => $budgetRequest->id,
                'status' => $budgetRequest->status,
                'processed_by' => $user->full_name,
                'date_processed' => $budgetRequest->date_processed,
            ]
        ]);
    }

    /**
     * Reject a budget request (Finance Manager)
     */
    public function rejectRequest(Request $request, $id)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'finance')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branchId = $user->branch_id;

        $budgetRequest = BudgetRequest::where('id', $id)
            ->where('branch_id', $branchId)
            ->where('status', 'Pending')
            ->first();

        if (!$budgetRequest) {
            return response()->json([
                'ok' => false,
                'message' => 'Budget request not found or already processed'
            ], 404);
        }

        $budgetRequest->update([
            'status' => 'Rejected',
            'processed_by' => $user->id,
            'date_processed' => now()->toDateString(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Budget request rejected successfully',
            'request' => [
                'id' => $budgetRequest->id,
                'status' => $budgetRequest->status,
                'processed_by' => $user->full_name,
                'date_processed' => $budgetRequest->date_processed,
            ]
        ]);
    }
}

