<?php

namespace App\Http\Controllers\SuperAdmin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Branch;

/**
 * SuperAdmin Finance Controller
 * Handles financial dashboard and KPIs for Super Admin monitoring
 *
 * This controller provides aggregated financial data across all branches
 * for monitoring, analytics, and reporting purposes only.
 */
class SuperAdminFinanceController extends Controller
{
    /**
     * Resolve authenticated user - same approach as SuperAdminController
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
     * Get date range based on filter
     */
    private function getDateRange($range)
    {
        $now = now();

        switch ($range) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'yesterday':
                return [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()];
            case 'thisWeek':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'thisMonth':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'lastMonth':
                return [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()];
            case 'all':
                return [null, null]; // No date filter
            default:
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
        }
    }

    /**
     * GET /api/superadmin/finance/dashboard
     *
     * Get financial dashboard with KPIs aggregated from all branches
     *
     * KPIs:
     * - total_revenue: SUM of grand_total from completed orders
     * - total_orders: COUNT of completed orders only (for accurate financial reporting)
     * - total_expenses: 0 (placeholder - no expenses table yet)
     * - total_refunds: SUM of grand_total from cancelled/refunded orders
     * - total_net_profit: total_revenue - total_expenses - total_refunds
     */
    public function dashboard(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $range = $request->query('range', 'today');
        $dateRange = $this->getDateRange($range);

        // Build query with optional date filter
        $ordersQuery = Order::query();
        $completedQuery = Order::query();
        $cancelledQuery = Order::query();

        if ($dateRange[0] !== null && $dateRange[1] !== null) {
            $ordersQuery->whereBetween('created_at', $dateRange);
            $completedQuery->whereBetween('created_at', $dateRange);
            $cancelledQuery->whereBetween('created_at', $dateRange);
        }

        // Total Revenue - SUM of grand_total from completed orders
        $totalRevenue = (float) $completedQuery->clone()
            ->where('status', 'completed')
            ->sum('grand_total');

        // Total Orders - COUNT of completed orders only (for accurate financial reporting)
        $totalOrders = (int) $ordersQuery->clone()
            ->where('status', 'completed')
            ->count();

        // Total Refunds - SUM of grand_total from cancelled orders
        $totalRefunds = (float) $cancelledQuery->clone()
            ->where('status', 'cancelled')
            ->sum('grand_total');

        // Total Expenses - placeholder (0 for now)
        $totalExpenses = 0.0;

        // Net Profit = total_revenue - total_expenses - total_refunds
        $netProfit = $totalRevenue - $totalExpenses - $totalRefunds;

        // Get branch count
        $totalBranches = Branch::count();

        // Get recent transactions (last 10 completed orders within date range)
        $recentTransactions = Order::with('branch')
            ->where('status', 'completed')
            ->when($dateRange[0] !== null, fn($q) => $q->whereBetween('created_at', $dateRange))
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

        // Get order status breakdown
        $orderStatusBreakdown = [
            'completed' => (int) Order::when($dateRange[0] !== null, fn($q) => $q->whereBetween('created_at', $dateRange))
                ->where('status', 'completed')->count(),
            'pending' => (int) Order::when($dateRange[0] !== null, fn($q) => $q->whereBetween('created_at', $dateRange))
                ->where('status', 'pending')->count(),
            'in_kitchen' => (int) Order::when($dateRange[0] !== null, fn($q) => $q->whereBetween('created_at', $dateRange))
                ->where('status', 'in_kitchen')->count(),
            'cancelled' => (int) Order::when($dateRange[0] !== null, fn($q) => $q->whereBetween('created_at', $dateRange))
                ->where('status', 'cancelled')->count(),
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

        $branches = $branchesQuery->get()->map(function ($branch) use ($dateRange) {
            // Total Sales (completed orders)
            $totalSales = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', $dateRange)
                ->where('status', 'completed')
                ->sum('grand_total');

            // Total Orders - count only completed orders (for accurate financial reporting)
            $totalOrders = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', $dateRange)
                ->where('status', 'completed')
                ->count();

            // Total Refunds (cancelled orders)
            $totalRefunds = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', $dateRange)
                ->where('status', 'cancelled')
                ->sum('grand_total');

            // Expenses (placeholder - 0)
            $totalExpenses = 0.0;

            // Net Profit = total_sales - total_expenses - total_refunds
            $netProfit = $totalSales - $totalExpenses - $totalRefunds;

            return [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'branch_code' => $branch->code,
                'total_sales' => (float) $totalSales,
                'total_orders' => (int) $totalOrders,
                'total_expenses' => $totalExpenses,
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

