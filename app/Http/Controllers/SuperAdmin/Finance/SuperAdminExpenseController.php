<?php

namespace App\Http\Controllers\SuperAdmin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Branch;

/**
 * SuperAdmin Expense Controller
 * Handles expense monitoring for Super Admin
 *
 * Since there's no expenses table yet, this returns placeholder data
 * for monitoring purposes
 */
class SuperAdminExpenseController extends Controller
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
     * GET /api/superadmin/finance/expenses
     *
     * Return all recorded expenses
     *
     * Since there's no expenses table yet, this returns empty array
     * with appropriate message for monitoring purposes
     *
     * Fields (when expenses table exists):
     * - expense_id
     * - branch_id
     * - vendor
     * - amount
     * - status
     * - approved_by
     * - created_at
     */
    public function index(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get filter parameters
        $branchId = $request->query('branch_id');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        // Validate per_page
        $perPage = min(max($perPage, 1), 100);

        // For now, return empty expenses since there's no expenses table
        // This maintains the monitoring interface structure
        $expenses = [];
        $total = 0;

        return response()->json([
            'ok' => true,
            'expenses' => $expenses,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => 0,
            ],
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_id' => $branchId,
            ],
            'message' => 'Expenses tracking will be available once the expenses module is implemented.',
            'total_amount' => 0.0,
        ]);
    }

    /**
     * GET /api/superadmin/finance/expenses/summary
     *
     * Get expense summary (placeholder)
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
                'total_expenses' => 0.0,
                'pending_approval' => 0,
                'approved' => 0,
                'rejected' => 0,
            ],
            'message' => 'Expenses tracking will be available once the expenses module is implemented.',
        ]);
    }
}
