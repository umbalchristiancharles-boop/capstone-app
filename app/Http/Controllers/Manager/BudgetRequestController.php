<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\BudgetRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\ProcurementRequest;

class BudgetRequestController extends Controller
{
    private function isAuthorizedUser($user, $requiredDepartment)
    {
        if (!$user || !$user->is_active) {
            return false;
        }

        $role = strtoupper($user->role ?? '');
        $userDept = strtoupper($user->department ?? '');

        $isManager = in_array($role, ['MANAGER', 'MANAGER_HR', 'BRANCH_MANAGER']);
        $hasDeptAccess = strtoupper($requiredDepartment) === $userDept;

        // Allow CUSTOM accounts that have the required module in their permissions
        if ($role === 'CUSTOM') {
            try {
                $perms = $user->permissions ?? [];
                if (is_string($perms)) $perms = json_decode($perms, true) ?: [];
                $modules = [];
                if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                    $modules = $perms['modules'];
                } elseif (is_array($perms)) {
                    $modules = $perms;
                }
                foreach ($modules as $m) {
                    if (strtoupper(trim((string)$m)) === strtoupper($requiredDepartment)) return true;
                }
            } catch (\Throwable $e) {
                // ignore and continue
            }
        }

        return $isManager && $hasDeptAccess;
    }

    /**
     * Get logistics manager's inventory (read-only)
     */
    public function getInventory(Request $request)
    {
        $user = Auth::user();

        // Allow Logistics and Procurement managers to view their own requests
        $dept = strtoupper($user->department ?? '');
        if (!in_array($dept, ['LOGISTICS', 'PROCUREMENT']) || !$user->is_active || !in_array(strtoupper($user->role ?? ''), ['MANAGER','MANAGER_HR','BRANCH_MANAGER'])) {
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

        $products = Product::where('branch_id', $branchId)
            ->where('is_active', 1)
            ->where('is_published', 1)
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
        $user = Auth::user();

        // Allow Logistics and Procurement managers to create budget requests
        $dept = strtoupper($user->department ?? '');
        if (!in_array($dept, ['LOGISTICS', 'PROCUREMENT']) || !$user->is_active || !in_array(strtoupper($user->role ?? ''), ['MANAGER','MANAGER_HR','BRANCH_MANAGER'])) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branchId = $user->branch_id;

        $requests = BudgetRequest::where('branch_id', $branchId)
            ->where('user_id', $user->id)
            ->with(['processor:id,full_name'])
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
                    'processed_by' => $req->processor?->full_name ?? null,
                    'date_processed' => $req->date_processed,
                    'created_at' => $req->created_at->toDateString(),
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
        $user = Auth::user();

        if (!$this->isAuthorizedUser($user, 'logistics')) {
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

        try {
            $validated = $request->validate([
                'purpose' => 'required|string|max:500',
                'requested_amount' => 'required|numeric|min:0.01',
            ]);

            $budgetRequest = DB::transaction(function () use ($validated, $branchId, $user) {
                return BudgetRequest::create([
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'purpose' => $validated['purpose'],
                    'requested_amount' => $validated['requested_amount'],
                    'status' => 'Pending',
                    'date_requested' => now()->toDateString(),
                ]);
            });

            Log::info('Budget request created', ['id' => $budgetRequest->id, 'user_id' => $user->id, 'branch_id' => $branchId]);

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

        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Budget request creation failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return response()->json([
                'ok' => false,
                'message' => 'Failed to create budget request'
            ], 500);
        }
    }

    /**
     * Get all budget requests for Finance Manager's branch
     */
    public function getAllRequests(Request $request)
    {
        $user = Auth::user();

        if (!$this->isAuthorizedUser($user, 'finance')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branchId = $user->branch_id;

        $requests = BudgetRequest::where('branch_id', $branchId)
            ->with(['user:id,full_name', 'processor:id,full_name'])
            ->orderBy('created_at', 'desc')
            ->get();

        Log::info('Finance getAllRequests', ['finance_user_id' => $user->id, 'branch_id' => $branchId, 'count' => $requests->count()]);

        $requests = $requests->map(function ($req) {
                return [
                    'id' => $req->id,
                    'branch_id' => $req->branch_id,
                    'requester_name' => $req->user?->full_name ?? 'Unknown',
                    'purpose' => $req->purpose,
                    'requested_amount' => number_format($req->requested_amount, 2),
                    'status' => $req->status,
                    'date_requested' => $req->date_requested,
                    'processed_by' => $req->processor?->full_name ?? null,
                    'date_processed' => $req->date_processed,
                    'created_at' => $req->created_at->toDateString(),
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
        $user = Auth::user();

        if (!$this->isAuthorizedUser($user, 'finance')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branchId = $user->branch_id;

        try {
            $budgetRequest = DB::transaction(function () use ($id, $branchId) {
                return BudgetRequest::where('id', $id)
                    ->where('branch_id', $branchId)
                    ->where('status', 'Pending')
                    ->firstOrFail();
            });

            $budgetRequest->update([
                'status' => 'Approved',
                'processed_by' => $user->id,
                'date_processed' => now()->toDateString(),
            ]);

            // If this budget request was created for a procurement request, update
            // the linked procurement request status so procurement knows the
            // budget has been approved and is to be received.
            try {
                if (preg_match('/Procurement Request #(\d+)/i', $budgetRequest->purpose, $matches)) {
                    $procId = intval($matches[1] ?? 0);
                    if ($procId > 0) {
                        $proc = ProcurementRequest::find($procId);
                        if ($proc) {
                            if ($proc->status !== 'pending_order_to_supplier') {
                                $proc->update(['status' => 'pending_order_to_supplier']);
                                Log::info('BudgetRequest approved -> set ProcurementRequest to pending_order_to_supplier', ['budget_request_id' => $budgetRequest->id, 'procurement_request_id' => $procId]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to link BudgetRequest approval to ProcurementRequest', ['budget_request_id' => $budgetRequest->id, 'error' => $e->getMessage()]);
            }
            Log::info('Budget request approved', ['id' => $id, 'processor_id' => $user->id]);

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

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Budget request not found or already processed'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Budget request approval failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'ok' => false,
                'message' => 'Failed to approve budget request'
            ], 500);
        }
    }

    /**
     * Reject a budget request (Finance Manager)
     * When a budget request is rejected, update the linked procurement request status to 'rejected'
     */
    public function rejectRequest(Request $request, $id)
    {
        $user = Auth::user();

        if (!$this->isAuthorizedUser($user, 'finance')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branchId = $user->branch_id;

        try {
            $budgetRequest = DB::transaction(function () use ($id, $branchId) {
                return BudgetRequest::where('id', $id)
                    ->where('branch_id', $branchId)
                    ->where('status', 'Pending')
                    ->firstOrFail();
            });

            $budgetRequest->update([
                'status' => 'Rejected',
                'processed_by' => $user->id,
                'date_processed' => now()->toDateString(),
            ]);

            // If this budget request was created for a procurement request, update
            // the linked procurement request status to 'rejected'
            try {
                if (preg_match('/Procurement Request #(\d+)/i', $budgetRequest->purpose, $matches)) {
                    $procId = intval($matches[1] ?? 0);
                    if ($procId > 0) {
                        $proc = ProcurementRequest::find($procId);
                        if ($proc) {
                            $proc->update(['status' => 'rejected']);
                            Log::info('BudgetRequest rejected -> set ProcurementRequest to rejected', ['budget_request_id' => $budgetRequest->id, 'procurement_request_id' => $procId]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to update ProcurementRequest status after budget rejection', ['budget_request_id' => $budgetRequest->id, 'error' => $e->getMessage()]);
            }

            Log::info('Budget request rejected', ['id' => $id, 'processor_id' => $user->id]);

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

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Budget request not found or already processed'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Budget request rejection failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'ok' => false,
                'message' => 'Failed to reject budget request'
            ], 500);
        }
    }

    /**
     * Finance confirms budget was physically given to procurement/logistics.
     * This will update the linked ProcurementRequest to allow ordering (pending order to supplier).
     */
    public function markGiven(Request $request, $id)
    {
        $user = Auth::user();

        if (!$this->isAuthorizedUser($user, 'finance')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branchId = $user->branch_id;

        try {
            $budgetRequest = BudgetRequest::where('id', $id)
                ->where('branch_id', $branchId)
                ->where('status', 'Approved')
                ->firstOrFail();

            // Find linked procurement request from purpose text
            if (preg_match('/Procurement Request #(\d+)/i', $budgetRequest->purpose, $matches)) {
                $procId = intval($matches[1] ?? 0);
                if ($procId > 0) {
                    $proc = ProcurementRequest::find($procId);
                    if (!$proc) {
                        return response()->json(['ok' => false, 'message' => 'Linked procurement request not found'], 404);
                    }

                    // Mark as budget given: set finance user, mark budget approved and move status
                    try {
                        // Approved procurements should move to supplier ordering first.
                        $newStatus = 'pending_order_to_supplier';
                        $proc->update([
                            'finance_user_id' => $user->id,
                            'budget_amount' => $budgetRequest->requested_amount,
                            'budget_approved' => true,
                            'status' => $newStatus,
                        ]);
                    } catch (\Exception $e) {
                        // Fallback for DBs that haven't run the enum migration yet
                        Log::warning('Failed to set pending_order_to_supplier, falling back to delivery_pending', ['error' => $e->getMessage(), 'procurement_request_id' => $proc->id]);
                        try {
                            $fallbackStatus = 'delivery_pending';
                            $proc->update([
                                'finance_user_id' => $user->id,
                                'budget_amount' => $budgetRequest->requested_amount,
                                'budget_approved' => true,
                                'status' => $fallbackStatus,
                            ]);
                        } catch (\Exception $_) {
                            // ignore secondary fallback
                        }
                    }

                    Log::info('Finance marked budget as given', ['budget_request_id' => $budgetRequest->id, 'procurement_request_id' => $proc->id, 'finance_user' => $user->id]);

                    // Mark the budget request itself as 'Budget Given' so the UI
                    // and subsequent calls won't allow re-confirming the same budget.
                    try {
                        $budgetRequest->update([
                            'status' => 'Budget Given',
                            'processed_by' => $user->id,
                            'date_processed' => now()->toDateString(),
                        ]);
                    } catch (\Exception $e) {
                        Log::warning('Failed to update BudgetRequest after marking given', ['error' => $e->getMessage(), 'budget_request_id' => $budgetRequest->id]);
                    }

                    return response()->json([
                        'ok' => true,
                        'message' => 'Budget marked as given',
                        'procurement_request' => $proc->fresh(),
                        'budget_request' => [
                            'id' => $budgetRequest->id,
                            'status' => $budgetRequest->status,
                            'processed_by' => $budgetRequest->processor?->full_name ?? $user->full_name,
                            'date_processed' => $budgetRequest->date_processed,
                        ]
                    ]);
                }
            }

            return response()->json(['ok' => false, 'message' => 'No linked procurement request found in purpose'], 400);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['ok' => false, 'message' => 'Budget request not found or not approved'], 404);
        } catch (\Exception $e) {
            Log::error('markGiven failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to mark budget as given'], 500);
        }
    }
}

