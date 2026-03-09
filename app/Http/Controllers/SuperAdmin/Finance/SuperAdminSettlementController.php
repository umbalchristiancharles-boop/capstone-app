<?php

namespace App\Http\Controllers\SuperAdmin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SuperAdmin Settlement Controller
 * Handles settlement/payout monitoring for Super Admin
 *
 * Since there's no settlements table yet, this returns placeholder data
 * for monitoring purposes
 */
class SuperAdminSettlementController extends Controller
{
    /**
     * Resolve authenticated user
     */
    private function resolveAuthenticatedUser($request)
    {
        if (Auth::check()) {
            return Auth::user();
        }

        $sessionUserId = $request->session()->get('user_id');
        if ($sessionUserId) {
            return \App\Models\User::find($sessionUserId);
        }

        return null;
    }

    /**
     * Check if user is Super Admin
     */
    private function isSuperAdmin($user)
    {
        if (!$user) {
            return false;
        }
        $roleUpper = strtoupper($user->role ?? '');
        return $roleUpper === 'SUPER_ADMIN' || $roleUpper === 'SUPERADMIN';
    }

    /**
     * GET /api/superadmin/finance/settlements
     *
     * Return settlement or payout records
     *
     * Since there's no settlements table yet, this returns empty array
     * with appropriate message for monitoring purposes
     *
     * Fields (when settlements table exists):
     * - settlement_id
     * - branch_id
     * - amount
     * - method
     * - status
     * - approved_by
     * - executed_at
     */
    public function index(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $request->query('branch_id');
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        $perPage = min(max($perPage, 1), 100);

        // For now, return empty settlements since there's no settlements table
        $settlements = [];
        $total = 0;

        return response()->json([
            'ok' => true,
            'settlements' => $settlements,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => 0,
            ],
            'filters' => [
                'branch_id' => $branchId,
                'status' => $status,
            ],
            'message' => 'Settlements tracking will be available once the settlements module is implemented.',
            'total_amount' => 0.0,
        ]);
    }

    /**
     * GET /api/superadmin/finance/settlements/summary
     *
     * Get settlement summary (placeholder)
     */
    public function summary(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'ok' => true,
            'summary' => [
                'total_settlements' => 0.0,
                'pending' => 0,
                'completed' => 0,
                'rejected' => 0,
            ],
            'message' => 'Settlements tracking will be available once the settlements module is implemented.',
        ]);
    }
}

