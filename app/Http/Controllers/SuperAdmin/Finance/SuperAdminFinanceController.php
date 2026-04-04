<?php

namespace App\Http\Controllers\SuperAdmin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Expense;

/**
 * SuperAdmin Finance Controller
 * Handles financial dashboard and KPIs for Super Admin monitoring
 *
 * This controller provides aggregated financial data across all branches
 * for monitoring, analytics, and reporting purposes only.
 */
class SuperAdminFinanceController extends Controller
{
    use FinancialTrait;

    /**
     * GET /api/superadmin/finance/dashboard
     *
     * Get financial dashboard with KPIs aggregated from all branches
     *
     * Optional filters:
     * - range: Date range filter (today, yesterday, thisWeek, thisMonth, lastMonth, all)
     * - branch_id: Filter by specific branch
     *
     * KPIs Calculation:
     * - total_revenue: SUM of grand_total from completed orders only
     * - total_orders: COUNT of completed orders (accurate financial reporting)
     * - total_expenses: SUM of all expenses (approved status)
     * - total_refunds: SUM of grand_total from cancelled orders
     * - total_net_profit: total_revenue - total_expenses - total_refunds
     *
     * Data Consistency:
     * - Only 'completed' orders count as revenue (NOT 'approved' or 'pending')
     * - Only 'cancelled' orders count as refunds (NOT pending)
     * - Expenses are from the expenses table, not orders
     */
    public function dashboard(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $range = $request->query('range', 'today');
        $branchId = $request->query('branch_id');
        $dateRange = $this->getDateRange($range);

        // Helper to apply date and branch filters
        $applyFilters = function($query) use ($dateRange, $branchId) {
            if ($dateRange[0] !== null && $dateRange[1] !== null) {
                $query->whereBetween('created_at', $dateRange);
            }
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
            return $query;
        };

        // === REVENUE ===
        // Only completed orders count as revenue (accurate financial reporting)
        $revenueQuery = Order::where('status', 'completed');
        $revenueQuery = $applyFilters($revenueQuery);
        $totalRevenue = (float) $revenueQuery->sum('grand_total');

        // === ORDER COUNT ===
        // Count only completed orders
        $ordersQuery = Order::where('status', 'completed');
        $ordersQuery = $applyFilters($ordersQuery);
        $totalOrders = (int) $ordersQuery->count();

        // === REFUNDS ===
        // Only cancelled orders count as refunds
        $refundsQuery = Order::where('status', 'cancelled');
        $refundsQuery = $applyFilters($refundsQuery);
        $totalRefunds = (float) $refundsQuery->sum('grand_total');

        // === EXPENSES ===
        // Sum all approved expenses
        $expensesQuery = Expense::where('status', 'approved');
        $expensesQuery = $applyFilters($expensesQuery);
        $totalExpenses = (float) $expensesQuery->sum('amount');

        // === NET PROFIT ===
        // Net Profit = total_revenue - total_expenses - total_refunds
        $netProfit = $totalRevenue - $totalExpenses - $totalRefunds;

        // === BRANCH COUNT ===
        $totalBranches = $branchId ? 1 : Branch::where('is_active', true)->count();

        // === RECENT TRANSACTIONS ===
        // Get last 10 completed orders for recent activity
        $recentTransactionsQuery = Order::with('branch')
            ->where('status', 'completed');
        $recentTransactionsQuery = $applyFilters($recentTransactionsQuery);

        $recentTransactions = $recentTransactionsQuery
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'transaction_id' => 'TXN-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'branch_id' => $order->branch_id,
                    'branch_name' => $order->branch ? $order->branch->name : 'N/A',
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'amount' => (float) $order->grand_total,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toISOString(),
                ];
            });

        // === ORDER STATUS BREAKDOWN ===
        // Count orders by status for visibility
        $baseStatusQuery = Order::query();
        $baseStatusQuery = $applyFilters($baseStatusQuery);

        $orderStatusBreakdown = [
            'completed' => (int) (clone $baseStatusQuery)->where('status', 'completed')->count(),
            'pending' => (int) (clone $baseStatusQuery)->where('status', 'pending')->count(),
            'in_kitchen' => (int) (clone $baseStatusQuery)->where('status', 'in_kitchen')->count(),
            'cancelled' => (int) (clone $baseStatusQuery)->where('status', 'cancelled')->count(),
            'approved' => (int) (clone $baseStatusQuery)->where('status', 'approved')->count(),
        ];

        return response()->json([
            'ok' => true,
            'dashboard' => [
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'total_expenses' => $totalExpenses,
                'total_refunds' => $totalRefunds,
                'total_net_profit' => $netProfit,
                'total_branches' => $totalBranches,
                'currency' => 'PHP',
                'date_range' => $range,
                'branch_id' => $branchId,
            ],
            'order_status_breakdown' => $orderStatusBreakdown,
            'recent_transactions' => $recentTransactions,
        ]);
    }

    /**
     * GET /api/superadmin/finance/branches
     *
     * Get financial performance per branch
     *
     * Optional filters:
     * - range: Date range filter (today, yesterday, thisWeek, thisMonth, lastMonth, all)
     * - from_date: Start date filter (alternative to range)
     * - to_date: End date filter (alternative to range)
     * - branch_id: Filter by specific branch
     */
    public function branches(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get filter parameters - support both 'range' and explicit dates
        $branchId = $request->query('branch_id');
        $range = $request->query('range', 'today');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        // Build date range - prefer 'range' parameter if provided, otherwise use from_date/to_date
        if ($range && $range !== 'custom') {
            $dateRange = $this->getDateRange($range);
        } elseif ($fromDate && $toDate) {
            $dateRange = [
                \Carbon\Carbon::parse($fromDate)->startOfDay(),
                \Carbon\Carbon::parse($toDate)->endOfDay(),
            ];
        } else {
            // Default to today
            $dateRange = $this->getDateRange('today');
        }

        // Get branches with financial data
        $branchesQuery = Branch::query();

        if ($branchId) {
            $branchesQuery->where('id', $branchId);
        }

        $branches = $branchesQuery->get()->map(function ($branch) use ($dateRange, $range) {
            // Build query base for this branch
            $orderQuery = Order::where('branch_id', $branch->id);
            $expenseQuery = Expense::where('branch_id', $branch->id);

            // Only apply date filter if range is not 'all' and dates are valid
            $applyDateFilter = ($range !== 'all' && $dateRange[0] !== null && $dateRange[1] !== null);

            if ($applyDateFilter) {
                $orderQuery->whereBetween('created_at', $dateRange);
                $expenseQuery->whereBetween('created_at', $dateRange);
            }

            // Total Sales (completed orders only)
            $totalSales = (float) (clone $orderQuery)
                ->where('status', 'completed')
                ->sum('grand_total');

            // Total Orders - count only completed orders
            $totalOrders = (int) (clone $orderQuery)
                ->where('status', 'completed')
                ->count();

            // Total Refunds (cancelled orders only)
            $totalRefunds = (float) (clone $orderQuery)
                ->where('status', 'cancelled')
                ->sum('grand_total');

            // Total Expenses (approved expenses only)
            $totalExpenses = (float) (clone $expenseQuery)
                ->where('status', 'approved')
                ->sum('amount');

            // Net Profit = total_sales - total_expenses - total_refunds
            $netProfit = $totalSales - $totalExpenses - $totalRefunds;

            return [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'branch_code' => $branch->code,
                'total_sales' => (float) $totalSales,
                'total_orders' => (int) $totalOrders,
                'total_expenses' => (float) $totalExpenses,
                'total_refunds' => (float) $totalRefunds,
                'net_profit' => (float) $netProfit,
                'is_active' => (bool) $branch->is_active,
            ];
        });

        // Calculate totals
        $totals = [
            'total_sales' => $branches->sum('total_sales'),
            'total_orders' => $branches->sum('total_orders'),
            'total_expenses' => $branches->sum('total_expenses'),
            'total_refunds' => $branches->sum('total_refunds'),
            'net_profit' => $branches->sum('net_profit'),
        ];

        return response()->json([
            'ok' => true,
            'branches' => $branches,
            'totals' => $totals,
            'filters' => [
                'range' => $range,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_id' => $branchId,
            ],
        ]);
    }
}

