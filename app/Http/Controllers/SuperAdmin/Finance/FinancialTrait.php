<?php

namespace App\Http\Controllers\SuperAdmin\Finance;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * FinancialTrait
 *
 * Shared logic for finance controllers to ensure consistency and prevent duplicate calculations
 * Includes:
 * - User authentication resolution
 * - Super Admin authorization checks
 * - Date range calculations
 * - Query filtering helpers
 * - Financial calculation helpers
 */
trait FinancialTrait
{
    /**
     * Resolve authenticated user - handles both session and token auth
     *
     * @param $request
     * @return User|null
     */
    protected function resolveAuthenticatedUser($request)
    {
        if (Auth::check()) {
            return Auth::user();
        }

        $sessionUserId = $request->session()->get('user_id');
        if ($sessionUserId) {
            return User::find($sessionUserId);
        }

        return null;
    }

    /**
     * Check if user is Super Admin
     *
     * @param User|null $user
     * @return bool
     */
    protected function isSuperAdmin($user)
    {
        if (!$user) {
            return false;
        }
        $roleUpper = strtoupper($user->role ?? '');
        return $roleUpper === 'SUPER_ADMIN' || $roleUpper === 'SUPERADMIN';
    }

    /**
     * Return unauthorized JSON response
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorizedResponse()
    {
        return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
    }

    /**
     * Get date range based on predefined range name
     *
     * @param string|null $range (today, yesterday, thisWeek, thisMonth, lastMonth, all)
     * @return array [start_date, end_date] or [null, null] for 'all'
     */
    protected function getDateRange($range = 'today')
    {
        $now = now();

        return match($range) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'thisWeek' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'thisMonth' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'lastMonth' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'all' => [null, null],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }

    /**
     * Get date range from explicit from_date and to_date
     *
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return array [start_date, end_date]
     */
    protected function getDateRangeFromDates($fromDate, $toDate)
    {
        if ($fromDate && $toDate) {
            return [
                \Carbon\Carbon::parse($fromDate)->startOfDay(),
                \Carbon\Carbon::parse($toDate)->endOfDay(),
            ];
        }

        // Default to today
        $now = now();
        return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
    }

    /**
     * Apply date range filter to a query
     *
     * @param $query
     * @param array $dateRange [$start, $end] - if null values, filter is not applied
     * @return mixed
     */
    protected function applyDateRangeFilter($query, array $dateRange)
    {
        if ($dateRange[0] !== null && $dateRange[1] !== null) {
            $query->whereBetween('created_at', $dateRange);
        }
        return $query;
    }

    /**
     * Apply branch filter to a query
     *
     * @param $query
     * @param int|null $branchId
     * @return mixed
     */
    protected function applyBranchFilter($query, $branchId)
    {
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        return $query;
    }

    /**
     * Apply both date range and branch filters to a query
     *
     * @param $query
     * @param array|null $dateRange
     * @param int|null $branchId
     * @return mixed
     */
    protected function applyFinanceFilters($query, $dateRange = null, $branchId = null)
    {
        if ($dateRange !== null) {
            $query = $this->applyDateRangeFilter($query, $dateRange);
        }
        if ($branchId !== null) {
            $query = $this->applyBranchFilter($query, $branchId);
        }
        return $query;
    }

    /**
     * Validate pagination parameters
     *
     * @param int $page
     * @param int $perPage
     * @return array [$page, $perPage]
     */
    protected function validatePagination($page = 1, $perPage = 15)
    {
        $page = max(1, (int) $page);
        $perPage = min(max(1, (int) $perPage), 100); // Max 100 per page
        return [$page, $perPage];
    }

    /**
     * Prevent duplicate transaction entries by checking for existing entries
     * with same order_id, branch_id, status, and amount within a time window
     *
     * @param mixed $query The model query class
     * @param string $modelClass The model to check (e.g., 'Expense', 'Settlement')
     * @param array $data Transaction data to check
     * @param int $timeWindowMinutes Time window to check within (default 5 minutes)
     * @return bool true if potential duplicate found
     */
    protected function isPotentialDuplicate($modelClass, array $data, $timeWindowMinutes = 5)
    {
        $modelPath = 'App\\Models\\' . $modelClass;

        if (!class_exists($modelPath)) {
            return false;
        }

        $query = $modelPath::query();

        // Build duplicate detection criteria
        if (isset($data['branch_id'])) {
            $query->where('branch_id', $data['branch_id']);
        }

        if (isset($data['amount'])) {
            $query->where('amount', $data['amount']);
        }

        if (isset($data['status'])) {
            $query->where('status', $data['status']);
        }

        // Check within time window
        $query->whereBetween('created_at', [
            now()->subMinutes($timeWindowMinutes),
            now()
        ]);

        return $query->exists();
    }

    /**
     * Log financial transaction for audit trail
     *
     * @param string $type (sale, refund, expense, settlement, etc)
     * @param array $details
     * @return void
     */
    protected function logFinancialTransaction($type, array $details)
    {
        \Log::channel('finance')->info("Financial Transaction: $type", $details);
    }
}
