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
    use FinancialTrait;

    /**
     * GET /api/superadmin/finance/expenses
     *
     * Return all recorded expenses with proper filtering
     * Only approved expenses count toward financial reporting
     */
    public function index(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return $this->unauthorizedResponse();
        }

        // Get filter parameters
        $branchId = $request->query('branch_id');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $status = $request->query('status'); // pending, approved, rejected
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        // Validate pagination
        [$page, $perPage] = $this->validatePagination($page, $perPage);

        // Build query with filters
        $query = Expense::with(['branch', 'creator']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Use date range helper for consistent filtering
        $dateRange = $this->getDateRangeFromDates($fromDate, $toDate);
        $query = $this->applyDateRangeFilter($query, $dateRange);

        if ($status) {
            // Validate status
            $validStatuses = ['pending', 'approved', 'rejected'];
            if (in_array($status, $validStatuses)) {
                $query->where('status', $status);
            }
        }

        // Get total amount - only approved expenses count
        $approvedQuery = (clone $query)->where('status', 'approved');
        $totalAmount = (float) $approvedQuery->sum('amount');

        // Get counts by status
        $pendingCount = (int) (clone $query)->where('status', 'pending')->count();
        $approvedCount = (int) (clone $query)->where('status', 'approved')->count();
        $rejectedCount = (int) (clone $query)->where('status', 'rejected')->count();

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
                    'amount' => (float) $expense->amount,
                    'description' => $expense->description,
                    'status' => $expense->status,
                    'created_by' => $expense->created_by,
                    'creator_name' => $expense->creator ? $expense->creator->full_name : null,
                    'created_at' => $expense->created_at->toISOString(),
                ];
            }),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
            ],
            'summary' => [
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'rejected' => $rejectedCount,
            ],
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_id' => $branchId,
                'status' => $status,
            ],
            'total_approved_amount' => $totalAmount,
        ]);
    }

    /**
     * GET /api/superadmin/finance/expenses/summary
     *
     * Get expense summary with status breakdown
     * Only approved expenses are included in financial calculations
     */
    public function summary(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return $this->unauthorizedResponse();
        }

        $branchId = $request->query('branch_id');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $query = Expense::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Apply date range filter
        if ($fromDate && $toDate) {
            $dateRange = $this->getDateRangeFromDates($fromDate, $toDate);
            $query = $this->applyDateRangeFilter($query, $dateRange);
        }

        return response()->json([
            'ok' => true,
            'summary' => [
                'total_expenses' => (float) (clone $query)->where('status', 'approved')->sum('amount'),
                'pending_approval' => (int) (clone $query)->where('status', 'pending')->count(),
                'approved' => (int) (clone $query)->where('status', 'approved')->count(),
                'rejected' => (int) (clone $query)->where('status', 'rejected')->count(),
            ],
        ]);
    }
}
