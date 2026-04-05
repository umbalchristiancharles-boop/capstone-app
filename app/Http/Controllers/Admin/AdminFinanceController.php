<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use App\Models\BudgetRequest;
use App\Models\SupplierOrder;
use App\Models\ProcurementRequest;

/**
 * Admin Finance Controller
 * Handles financial dashboard, reports, and transactions for Admin users
 * Can show data for all branches or single branch depending on admin role
 */
class AdminFinanceController extends Controller
{
    /**
     * Resolve authenticated user
     */
    private function resolveAuthenticatedUser(Request $request)
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
     * Check if user is admin or has admin access
     */
    private function isAdmin($user)
    {
        if (!$user) return false;
        $role = strtoupper($user->role ?? '');
        return in_array($role, ['OWNER', 'ADMIN', 'BRANCH_MANAGER', 'SUPER_ADMIN', 'SUPERADMIN']);
    }

    /**
     * Resolve branch scope from authenticated user and optional branch_id query.
     *
     * Rules:
     * - Main Branch users default to ALL branches when no explicit filter is provided.
     * - OWNER/SUPER_ADMIN/SUPERADMIN and Main Branch users can request any branch_id.
     * - Other users may only request their own branch_id.
     */
    private function resolveBranchScope(Request $request, $user)
    {
        $requestedBranchId = $request->query('branch_id');
        $userRole = strtoupper($user->role ?? '');
        $userBranch = $user->branch;
        $isMainBranchUser = $userBranch && strtoupper($userBranch->name ?? '') === 'MAIN BRANCH';

        $branchId = $user->branch_id;

        if ($isMainBranchUser && !$requestedBranchId) {
            $branchId = null;
        }

        if ($requestedBranchId) {
            if (in_array($userRole, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN']) || $isMainBranchUser) {
                $branchId = $requestedBranchId;
            } elseif ((string) $requestedBranchId === (string) $user->branch_id) {
                $branchId = $requestedBranchId;
            }
        }

        return [$branchId, $isMainBranchUser];
    }

    /**
     * Get date range based on 'range' param
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
            default:
                return [null, null];
        }
    }

    /**
     * GET /api/admin/finance/dashboard?range=...
     * KPIs: totalRevenue, totalOrders, netProfit, totalExpenses
     * Shows data for all branches (or filtered by user's branch if assigned)
     */
    public function dashboard(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user || !$this->isAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $range = $request->query('range', 'all');
        $dateRange = $this->getDateRange($range);
        [$branchId] = $this->resolveBranchScope($request, $user);

        // Orders/Expenses queries
        $completedQuery = Order::whereIn('status', ['completed', 'approved']);
        $ordersQuery = Order::query();

        // Filter by branch if user has one assigned
        if ($branchId) {
            $completedQuery->where('branch_id', $branchId);
            $ordersQuery->where('branch_id', $branchId);
        }

        // Date filter if not 'all'
        if ($dateRange[0] && $dateRange[1]) {
            $start = $dateRange[0];
            $end = $dateRange[1];
            $completedQuery->whereBetween('created_at', [$start, $end]);
            $ordersQuery->whereBetween('created_at', [$start, $end]);
        }

        // Total Income / Revenue: sum completed orders grand_total
        $totalRevenue = (float) $completedQuery->sum('grand_total');

        // Total Orders: count completed
        $totalOrders = (int) $ordersQuery->where('status', 'completed')->count();

        // Total Expenses: sum from Supplier Orders, Procurement Requests, and Budget Requests
        $supplierOrdersExpense = SupplierOrder::query();
        $procurementExpense = ProcurementRequest::query();
        $budgetExpenseQuery = BudgetRequest::query();

        // Filter by branch if user has one assigned
        if ($branchId) {
            $supplierOrdersExpense->where('branch_id', $branchId);
            $procurementExpense->where('branch_id', $branchId);
            $budgetExpenseQuery->where('branch_id', $branchId);
        }

        // Supplier orders: sum of (price * quantity) for fulfilled/on_delivery orders
        $supplierOrdersExpense = (float) $supplierOrdersExpense
            ->whereIn('status', ['fulfilled', 'on_delivery', 'confirmed'])
            ->selectRaw('SUM(price * quantity) as total')
            ->value('total') ?? 0;

        // Procurement requests: sum of total_amount for completed requests
        $procurementExpense = (float) $procurementExpense
            ->where('status', 'completed')
            ->sum('total_amount');

        // Budget requests: if status is "Budget Given" or "Approved", it's an expense
        $budgetExpense = (float) $budgetExpenseQuery
            ->whereIn('status', ['Approved', 'Budget Given'])
            ->sum('requested_amount');

        // Total Expenses
        $totalExpenses = $supplierOrdersExpense + $procurementExpense + $budgetExpense;

        // Pending Approvals: count pending BudgetRequest
        $pendingApprovalsQuery = BudgetRequest::where('status', 'Pending');
        if ($branchId) {
            $pendingApprovalsQuery->where('branch_id', $branchId);
        }
        $pendingApprovals = (int) $pendingApprovalsQuery->count();

        // Net Profit
        $netProfit = $totalRevenue - $totalExpenses;

        return response()->json([
            'ok' => true,
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'totalOrders' => $totalOrders,
            'totalSales' => $totalRevenue, // For KPI display
            'pendingApprovals' => $pendingApprovals,
            'netProfit' => $netProfit,
            'revenue' => $netProfit, // Net revenue for KPI display
            'range' => $range,
            'branch_id' => $branchId,
            'viewingAllBranches' => !$branchId
        ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * GET /api/admin/finance/transactions
     * Recent transactions for table: latest completed orders
     */
    public function transactions(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user || !$this->isAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        [$branchId] = $this->resolveBranchScope($request, $user);
        Log::info('Transactions query', ['branch_id' => $branchId, 'user_id' => $user->id, 'user_role' => $user->role]);

        $transactionsQuery = Order::with(['items.product', 'branch', 'cashier'])
            ->whereIn('status', ['pending', 'in_kitchen', 'approved', 'completed', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->limit(20);

        // Filter by branch if user has one assigned
        if ($branchId) {
            $transactionsQuery->where('branch_id', $branchId);
        }

        $transactions = $transactionsQuery->get();
        Log::info('Transactions count', ['count' => count($transactions), 'branch_id' => $branchId]);

        $transactions = $transactions
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'branch_name' => $order->branch?->name ?? 'Unknown',
                    'cashier_name' => $order->cashier?->full_name ?? $order->cashier?->name ?? 'System',
                    'customer' => $order->customer_name ?: 'Walk-in',
                    'total' => number_format($order->grand_total, 2),
                    'paid' => number_format($order->amount_paid, 2),
                    'status' => ucfirst($order->status),
                    'ordered_at' => $order->created_at->format('M d, Y H:i'),
                    'items' => $order->items->map(fn($item) => [
                        'product_name' => $item->product?->name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'subtotal' => number_format($item->subtotal ?? 0, 2)
                    ])
                ];
            });

        return response()->json(['ok' => true, 'transactions' => $transactions])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * GET /api/admin/finance/reports
     * Chart data: Monthly income, expenses, net profit (past 12 months)
     */
    public function reports(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user || !$this->isAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        [$branchId] = $this->resolveBranchScope($request, $user);
        $now = now()->startOfDay();
        $months = [];
        $incomeData = [];
        $expensesData = [];
        $netData = [];

        // Generate past 12 months starting from current month going backwards
        for ($i = 11; $i >= 0; $i--) {
            // Start from first day of current month, then subtract months
            $monthStart = $now->copy()->startOfMonth()->subMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();
            $monthLabel = $monthStart->format('M Y');

            // Income: completed orders
            $incomeQuery = Order::whereIn('status', ['completed', 'approved'])
                ->whereBetween('created_at', [$monthStart, $monthEnd]);
            if ($branchId) {
                $incomeQuery->where('branch_id', $branchId);
            }
            $income = (float) $incomeQuery->sum('grand_total');

            // Expenses: supplier orders + procurement requests + budget requests
            $supplierOrdersQuery = SupplierOrder::whereIn('status', ['fulfilled', 'on_delivery', 'confirmed'])
                ->whereBetween('created_at', [$monthStart, $monthEnd]);
            if ($branchId) {
                $supplierOrdersQuery->where('branch_id', $branchId);
            }
            $supplierExpense = (float) $supplierOrdersQuery
                ->selectRaw('SUM(price * quantity) as total')
                ->value('total') ?? 0;

            $procurementQuery = ProcurementRequest::where('status', 'completed')
                ->whereBetween('created_at', [$monthStart, $monthEnd]);
            if ($branchId) {
                $procurementQuery->where('branch_id', $branchId);
            }
            $procurementExpense = (float) $procurementQuery->sum('total_amount');

            $budgetQuery = BudgetRequest::whereIn('status', ['Approved', 'Budget Given'])
                ->whereBetween('created_at', [$monthStart, $monthEnd]);
            if ($branchId) {
                $budgetQuery->where('branch_id', $branchId);
            }
            $budgetExpense = (float) $budgetQuery->sum('requested_amount');

            $expenses = (float) ($supplierExpense + $procurementExpense + $budgetExpense);

            $net = $income - $expenses;

            $months[] = $monthLabel;
            $incomeData[] = $income;
            $expensesData[] = $expenses;
            $netData[] = $net;
        }

        $reports = [[
            'id' => 1,
            'title' => 'Income, Expenses & Net Profit (Past 12 Months)',
            'summary' => 'Monthly financial performance' . ($branchId ? ' for your branch' : ' (All Branches)'),
            'data' => [
                'months' => $months,
                'income' => $incomeData,
                'expenses' => $expensesData,
                'netProfit' => $netData
            ],
            'type' => 'line'
        ]];

        return response()->json(['ok' => true, 'reports' => $reports])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
