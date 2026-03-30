<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceMarkupPercentage;
use App\Models\PriceMarkupRequest;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PriceMarkupController extends Controller
{
    /**
     * Get current price markup percentage for a branch
     */
    public function getCurrentPercentage(Request $request, $branchId = null)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Use provided branch ID or user's branch
        if (!$branchId) {
            $branchId = $user->branch_id;
        }

        if (!$branchId) {
            return response()->json(['ok' => false, 'message' => 'No branch specified'], 400);
        }

        // Verify user has access to this branch
        $hasAccess = false;
        
        if ($role === 'OWNER') {
            // Owner can see all branches
            $hasAccess = true;
        } elseif ($user->branch_id == $branchId) {
            // User can see their own branch
            $hasAccess = true;
        } else {
            // Check if main branch finance manager can see other branches
            $mainBranch = Branch::where('is_main_branch', true)->first();
            if ($mainBranch && $user->branch_id == $mainBranch->id && in_array($role, ['MANAGER', 'BRANCH_MANAGER'])) {
                $dept = strtoupper($user->department ?? '');
                if ($dept === 'FINANCE') {
                    // Main branch finance manager can see any branch
                    $hasAccess = true;
                }
            }
        }
        
        if (!$hasAccess) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branch = Branch::find($branchId);
        if (!$branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }

        // Get current active percentage
        $current = PriceMarkupPercentage::where('branch_id', $branchId)
            ->where('is_active', true)
            ->first();

        if (!$current) {
            // Default to 20% if not set
            $current = PriceMarkupPercentage::create([
                'branch_id' => $branchId,
                'percentage' => 20.00,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'ok' => true,
            'current_percentage' => (float) $current->percentage,
            'multiplier' => $current->getMultiplier(),
            'branch_id' => $branchId,
        ]);
    }

    /**
     * Request a percentage change (Finance Manager action)
     */
    public function requestPercentageChange(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'requested_percentage' => 'required|numeric|min:1|max:100',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $branchId = $request->input('branch_id');

        // Check if user has finance permissions
        $hasFinancePermission = $this->userHasFinancePermission($user, $branchId);
        if (!$hasFinancePermission) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized - Finance permission required'], 401);
        }

        // Check if already pending request exists for this branch
        $existingPending = PriceMarkupRequest::where('branch_id', $branchId)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            return response()->json([
                'ok' => false,
                'message' => 'A pending percentage change request already exists for this branch',
                'pending_request_id' => $existingPending->id,
            ], 409);
        }

        try {
            // Get current percentage
            $current = PriceMarkupPercentage::where('branch_id', $branchId)
                ->where('is_active', true)
                ->first();

            if (!$current) {
                $current = PriceMarkupPercentage::create([
                    'branch_id' => $branchId,
                    'percentage' => 20.00,
                    'is_active' => true,
                ]);
            }

            $requestedPercentage = (float) $request->input('requested_percentage');

            if ($requestedPercentage == (float) $current->percentage) {
                return response()->json([
                    'ok' => false,
                    'message' => 'New percentage is the same as current percentage',
                ], 400);
            }

            $markupRequest = PriceMarkupRequest::create([
                'branch_id' => $branchId,
                'requested_by' => $user->id,
                'current_percentage' => (float) $current->percentage,
                'requested_percentage' => $requestedPercentage,
                'reason' => $request->input('reason'),
                'status' => 'pending',
            ]);

            Log::info('Price markup change requested', [
                'request_id' => $markupRequest->id,
                'branch_id' => $branchId,
                'requested_by' => $user->id,
                'current' => $current->percentage,
                'requested' => $requestedPercentage,
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Percentage change request submitted for approval',
                'request' => $markupRequest,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to request percentage change', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to create request'], 500);
        }
    }

    /**
     * Get pending requests for main finance manager to review
     */
    public function getPendingRequests(Request $request, $branchId = null)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        Log::info('[PriceMarkupController] getPendingRequests called', [
            'user_id' => $user->id,
            'role' => $role,
            'branch_id' => $user->branch_id,
            'department' => $user->department,
            'branchId_param' => $branchId,
        ]);

        // Determine which branches user can see
        $mainBranch = Branch::where('is_main_branch', true)->first();
        $isMainBranchFinance = $mainBranch && $user->branch_id == $mainBranch->id && 
                               in_array($role, ['MANAGER', 'BRANCH_MANAGER']) &&
                               strtoupper($user->department ?? '') === 'FINANCE';

        Log::info('[PriceMarkupController] Auth check', [
            'mainBranch_id' => $mainBranch?->id,
            'isMainBranchFinance' => $isMainBranchFinance,
            'check_branch_match' => $user->branch_id == $mainBranch?->id,
            'check_role' => in_array($role, ['MANAGER', 'BRANCH_MANAGER']),
            'check_department' => strtoupper($user->department ?? '') === 'FINANCE',
        ]);

        // Owner and main branch finance can see all; otherwise filter to user's branch
        if ($role !== 'OWNER' && !$isMainBranchFinance) {
            $branchId = $user->branch_id;
            Log::info('[PriceMarkupController] Filtering to user branch', ['branch_id' => $branchId]);
        }

        $query = PriceMarkupRequest::where('status', 'pending');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $requests = $query->with([
            'branch',
            'requestedBy',
            'mainFinanceApprover',
            'ownerApprover',
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        Log::info('[PriceMarkupController] Returning pending requests', [
            'count' => $requests->count(),
            'requests' => $requests->pluck('id')->toArray(),
        ]);

        return response()->json([
            'ok' => true,
            'requests' => $requests,
        ]);
    }

    /**
     * Main finance manager approves/rejects percentage change
     */
    public function mainFinanceApprove(Request $request, $requestId)
    {
        $request->validate([
            'approved' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Check if main branch finance manager or owner
        $branch = Branch::where('is_main_branch', true)->first();
        $isMainBranchFinance = false;
        
        if ($role === 'OWNER') {
            $isMainBranchFinance = true;
        } elseif ($branch && $user->branch_id == $branch->id && in_array($role, ['MANAGER', 'BRANCH_MANAGER'])) {
            $dept = strtoupper($user->department ?? '');
            if ($dept === 'FINANCE') {
                $isMainBranchFinance = true;
            }
        }

        if (!$isMainBranchFinance) {
            // Check for custom permissions
            $hasPermission = false;
            if ($role === 'CUSTOM') {
                $perms = $user->permissions ?? [];
                if (is_string($perms)) {
                    try { $decoded = json_decode($perms, true); if (is_array($decoded)) $perms = $decoded; } catch (\Throwable $e) { $perms = []; }
                }
                if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                    $hasPermission = collect($perms['modules'])->map(fn($m)=>strtolower((string)$m))->contains('finance');
                }
            }

            if (!$hasPermission) {
                return response()->json(['ok' => false, 'message' => 'Unauthorized - Main branch finance manager approval only'], 401);
            }
        }

        $markupRequest = PriceMarkupRequest::find($requestId);
        if (!$markupRequest) {
            return response()->json(['ok' => false, 'message' => 'Request not found'], 404);
        }

        if ($markupRequest->status !== 'pending') {
            return response()->json(['ok' => false, 'message' => 'Request is no longer pending'], 400);
        }

        try {
            $approved = $request->input('approved');
            $notes = $request->input('notes');

            $markupRequest->main_finance_approval = $approved ? 'approved' : 'rejected';
            $markupRequest->main_finance_approved_by = $user->id;
            $markupRequest->main_finance_approved_at = now();
            $markupRequest->main_finance_notes = $notes;

            // If rejected by main finance, mark entire request as rejected
            if (!$approved) {
                $markupRequest->status = 'rejected';
            }

            $markupRequest->save();

            Log::info('Main finance markup approval', [
                'request_id' => $requestId,
                'approved' => $approved,
                'approved_by' => $user->id,
            ]);

            $message = $approved 
                ? 'Request approved by main finance manager, awaiting owner approval'
                : 'Request rejected by main finance manager';

            return response()->json([
                'ok' => true,
                'message' => $message,
                'request' => $markupRequest,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to approve markup request', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to process approval'], 500);
        }
    }

    /**
     * Owner approves/rejects percentage change
     */
    public function ownerApprove(Request $request, $requestId)
    {
        $request->validate([
            'approved' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Only owner can approve
        if ($role !== 'OWNER') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized - Owner approval only'], 401);
        }

        $markupRequest = PriceMarkupRequest::find($requestId);
        if (!$markupRequest) {
            return response()->json(['ok' => false, 'message' => 'Request not found'], 404);
        }

        if ($markupRequest->status !== 'pending') {
            return response()->json(['ok' => false, 'message' => 'Request is no longer pending'], 400);
        }

        if ($markupRequest->main_finance_approval !== 'approved') {
            return response()->json([
                'ok' => false,
                'message' => 'Main finance manager has not yet approved this request',
            ], 400);
        }

        try {
            $approved = $request->input('approved');
            $notes = $request->input('notes');

            $markupRequest->owner_approval = $approved ? 'approved' : 'rejected';
            $markupRequest->owner_approved_by = $user->id;
            $markupRequest->owner_approved_at = now();
            $markupRequest->owner_notes = $notes;

            if ($approved) {
                // Activate the new percentage
                $markupRequest->status = 'approved';
                $markupRequest->activated_at = now();

                // Deactivate old percentage
                PriceMarkupPercentage::where('branch_id', $markupRequest->branch_id)
                    ->update(['is_active' => false]);

                // Create new active percentage
                PriceMarkupPercentage::create([
                    'branch_id' => $markupRequest->branch_id,
                    'percentage' => $markupRequest->requested_percentage,
                    'is_active' => true,
                    'set_by' => $user->id,
                    'set_at' => now(),
                    'notes' => 'Approved via price markup request #' . $markupRequest->id,
                ]);
            } else {
                $markupRequest->status = 'rejected';
            }

            $markupRequest->save();

            Log::info('Owner markup approval', [
                'request_id' => $requestId,
                'approved' => $approved,
                'approved_by' => $user->id,
            ]);

            if ($approved) {
                $message = 'Price markup percentage approved and activated: ' . $markupRequest->requested_percentage . '%';
            } else {
                $message = 'Request rejected by owner';
            }

            return response()->json([
                'ok' => true,
                'message' => $message,
                'request' => $markupRequest,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to approve markup request by owner', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to process approval'], 500);
        }
    }

    /**
     * Get history of price markup changes
     */
    public function getHistory(Request $request, $branchId)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Verify access - owner can see all, others can only see their own branch
        if ($role !== 'OWNER') {
            if ($user->branch_id != $branchId) {
                return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            }
        }

        $requests = PriceMarkupRequest::where('branch_id', $branchId)
            ->where('status', 'approved')
            ->with([
                'branch',
                'requestedBy',
                'mainFinanceApprover',
                'ownerApprover',
            ])
            ->orderBy('activated_at', 'desc')
            ->get();

        return response()->json([
            'ok' => true,
            'history' => $requests,
        ]);
    }

    /**
     * Helper: Check if user has finance permission
     */
    private function userHasFinancePermission($user, $branchId): bool
    {
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        Log::info('Price markup permission check', [
            'user_id' => $user->id,
            'role' => $role,
            'department' => $dept,
            'user_branch_id' => $user->branch_id,
            'requested_branch_id' => $branchId,
        ]);

        // Owner always has permission
        if ($role === 'OWNER') {
            Log::info('Permission granted: User is OWNER');
            return true;
        }

        // Manager or Branch Manager with FINANCE department in same branch
        if (in_array($role, ['MANAGER', 'BRANCH_MANAGER']) && $user->branch_id == $branchId) {
            if ($dept === 'FINANCE') {
                Log::info('Permission granted: MANAGER/BRANCH_MANAGER with FINANCE dept');
                return true;
            } else {
                Log::info('Permission denied: MANAGER/BRANCH_MANAGER but department is ' . $dept);
            }
        }

        // Check custom permissions
        if ($role === 'CUSTOM') {
            $perms = $user->permissions ?? [];
            if (is_string($perms)) {
                try { $decoded = json_decode($perms, true); if (is_array($decoded)) $perms = $decoded; } catch (\Throwable $e) { $perms = []; }
            }
            if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                if (collect($perms['modules'])->map(fn($m)=>strtolower((string)$m))->contains('finance')) {
                    Log::info('Permission granted: CUSTOM role with finance module');
                    return $user->branch_id == $branchId;
                }
            }
        }

        Log::info('Permission denied for user', ['role' => $role, 'dept' => $dept]);
        return false;
    }
}
