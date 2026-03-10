<?php

namespace App\Http\Controllers\SuperAdmin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Expense;

/**
 * SuperAdmin Expense Controller
 * Handles expense monitoring for Super Admin
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

        // Build query with filters
        $query = Expense::with(['branch', 'creator']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Get total amount
        $totalAmount = (float) $query->sum('amount');

        // Paginate
        $expenses = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $total = $query->count();

        return response()->json([
            'ok' => true,
            'expenses' => $expenses->map(function($expense) {
                return [
                    'id' => $expense->id,
                    'branch_id' => $expense->branch_id,
                    'branch_name' => $expense->branch ? $expense->branch->name : null,
                    'amount' => $expense->amount,
                    'description' => $expense->description,
                    'status' => $expense->status,
                    'created_by' => $expense->created_by,
                    'creator_name' => $expense->creator ? $expense->creator->full_name : null,
                    'created_at' => $expense->created_at,
                ];
            }),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
            ],
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_id' => $branchId,
            ],
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * GET /api/superadmin/finance/expenses/summary
     *
     * Get expense summary
     */
    public function summary(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $request->query('branch_id');

        $query = Expense::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return response()->json([
            'ok' => true,
            'summary' => [
                'total_expenses' => (float) $query->sum('amount'),
                'pending_approval' => (int) $query->where('status', 'pending')->count(),
                'approved' => (int) $query->where('status', 'approved')->count(),
                'rejected' => (int) $query->where('status', 'rejected')->count(),
            ],
        ]);
    }
}
