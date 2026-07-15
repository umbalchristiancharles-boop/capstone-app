<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Expense;
use App\Models\User;
use App\Models\BudgetRequest;
use App\Models\SupplierOrder;
use App\Models\ProcurementRequest;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Manager Finance Controller
 * Handles financial dashboard, reports, and transactions for Finance Managers
 * Branch-specific data (filtered by authenticated manager's branch_id)
 */
class ManagerFinanceController extends Controller
{
    /**
     * Resolve authenticated user from session or guard
     */
    private function resolveAuthenticatedUser(Request $request)
    {
        if (Auth::check()) {
            Log::debug('[ManagerFinance] Auth::check() true, user id: ' . (Auth::id() ?? 'null'));
            return Auth::user();
        }
        // Support Bearer token authentication (Sanctum personal access tokens)
        try {
            $authHeader = $request->header('Authorization');
            $token = $request->bearerToken();
            Log::debug('[ManagerFinance] Authorization header: ' . ($authHeader ?? 'none') . ', bearerToken: ' . ($token ? '[present]' : '[none]'));
            if ($token) {
                $pat = PersonalAccessToken::findToken($token);
                if ($pat && $pat->tokenable) {
                    Log::debug('[ManagerFinance] PersonalAccessToken matched tokenable id: ' . ($pat->tokenable->id ?? 'unknown'));
                    return $pat->tokenable;
                }
                Log::debug('[ManagerFinance] PersonalAccessToken findToken returned no tokenable');
            }
        } catch (\Throwable $e) {
            Log::error('[ManagerFinance] Token lookup error: ' . $e->getMessage());
            // ignore and fallback to session-based lookup
        }
        $sessionUserId = $request->session()->get('user_id');
        Log::debug('[ManagerFinance] session user_id: ' . ($sessionUserId ?? 'null') . ', cookies: ' . json_encode($request->cookies->all()));
        if ($sessionUserId) {
            return User::find($sessionUserId);
        }
        return null;
    }

    /**
     * Check if user has finance manager permissions
     */
    private function isFinanceManager($user)
    {
        if (!$user) return false;
        $role = strtoupper($user->role ?? '');
        if (in_array($role, ['FINANCE_MANAGER', 'MANAGER', 'OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])) {
            return true;
        }

        // Support CUSTOM role users who have the finance module enabled in their
        // permissions payload (some accounts are created with role 'CUSTOM' and
        // a JSON 'permissions' field listing enabled modules).
        if ($role === 'CUSTOM') {
            $perms = $user->permissions ?? [];
            if (is_string($perms)) {
                try { $decoded = json_decode($perms, true); if (is_array($decoded)) $perms = $decoded; } catch (\Throwable $e) { $perms = []; }
            }
            if (is_array($perms)) {
                // Check for modules list
                if (isset($perms['modules']) && is_array($perms['modules'])) {
                    foreach ($perms['modules'] as $m) {
                        if (strtolower((string)$m) === 'finance') return true;
                    }
                }
                // Check for explicit finance flag
                if (isset($perms['finance']) && ($perms['finance'] === true || $perms['finance'] === '1' || $perms['finance'] === 1)) {
                    return true;
                }
                // Fallback: flat list containing 'finance'
                foreach ($perms as $v) {
                    if (is_string($v) && strtolower($v) === 'finance') return true;
                }
            }
        }

        return false;
    }

    /**
     * Get date range based on 'range' param (matches frontend filter-bar)
     */
    private function getDateRange($range, $startDate = null, $endDate = null)
    {
        // Handle custom date range
        if ($range === 'custom' && $startDate && $endDate) {
            return [
                \Carbon\Carbon::parse($startDate)->startOfDay(),
                \Carbon\Carbon::parse($endDate)->endOfDay()
            ];
        }

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
     * GET /api/manager/finance/profile
     * Frontend expects: {data: {user: {...}}}
     */
    public function profile(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user || !$this->isFinanceManager($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }
        return response()->json(['ok' => true, 'user' => $user]);
    }

    /**
     * GET /api/manager/finance/dashboard?range=...&branch_id=...
     * KPIs: totalRevenue (income), totalOrders, netProfit (income - expenses), pendingApprovals
     * Branch-filtered to user's branch_id, or to query branch_id if user has permission
     */
    public function dashboard(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user || !$this->isFinanceManager($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $range = $request->query('range', 'all');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $dateRange = $this->getDateRange($range, $startDate, $endDate);

        // If branch_id is provided in query, use it (for filtering view)
        // Otherwise use user's own branch_id
        $requestedBranchId = $request->query('branch_id');
        $userRole = strtoupper($user->role ?? '');
        $userBranch = $user->branch;
        $isMainBranchUser = $userBranch && strtoupper($userBranch->name ?? '') === 'MAIN BRANCH';
        $branchId = $user->branch_id;
        
        // Main Branch user defaults to viewing ALL branches (no filter)
        if ($isMainBranchUser && !$requestedBranchId) {
            $branchId = null;
        }
        
        // Owner/Admin or Main Branch user can filter by any branch
        if ($requestedBranchId && (in_array($userRole, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN']) || $isMainBranchUser)) {
            $branchId = $requestedBranchId;
        } elseif ($requestedBranchId && $requestedBranchId == $user->branch_id) {
            // User requesting their own branch
            $branchId = $requestedBranchId;
        }

        // Orders/Expenses queries - if user has no branch_id (owner/admin), get ALL branches
        // Include in_kitchen orders as they are paid at checkout (dishes are queued for kitchen)
        $completedQuery = Order::whereIn('status', ['completed', 'approved', 'in_kitchen']);
        $cancelledQuery = Order::whereIn('status', ['cancelled']);
        $ordersQuery = Order::query();

        // Filter by branch if user has one assigned
        if ($branchId) {
            $completedQuery->where('branch_id', $branchId);
            $cancelledQuery->where('branch_id', $branchId);
            $ordersQuery->where('branch_id', $branchId);
        }

        // Date filter if not 'all'
        if ($dateRange[0] && $dateRange[1]) {
            $start = $dateRange[0];
            $end = $dateRange[1];
            $completedQuery->whereBetween('created_at', [$start, $end]);
            $cancelledQuery->whereBetween('created_at', [$start, $end]);
            $ordersQuery->whereBetween('created_at', [$start, $end]);
        }

        // Total Income / Revenue: sum completed orders grand_total
        $totalRevenue = (float) $completedQuery->sum('grand_total');

        // Total Orders: count completed
        $totalOrders = (int) $ordersQuery->where('status', 'completed')->count();

        // Total Expenses: sum from Supplier Orders and Procurement Requests
        // Expenses come from confirmed supplier orders and approved procurement requests
        $supplierOrdersExpense = SupplierOrder::query();
        $procurementExpense = ProcurementRequest::query();

        // Filter by branch if user has one assigned
        if ($branchId) {
            $supplierOrdersExpense->where('branch_id', $branchId);
            $procurementExpense->where('branch_id', $branchId);
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
        $budgetExpenseQuery = BudgetRequest::whereIn('status', ['Approved', 'Budget Given']);
        if ($branchId) {
            $budgetExpenseQuery->where('branch_id', $branchId);
        }
        $budgetExpense = (float) $budgetExpenseQuery->sum('requested_amount');

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
            'pendingApprovals' => $pendingApprovals,
            'netProfit' => $netProfit,
            'range' => $range,
            'branch_id' => $branchId,
            'viewingAllBranches' => !$branchId
        ]);
    }

    /**
     * GET /api/manager/finance/transactions
     * Recent transactions for table: 20 latest completed orders made by cashiers
     * If user has branch_id, show branch transactions; otherwise show all branches
     */
    public function transactions(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user || !$this->isFinanceManager($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $user->branch_id;
        $userBranch = $user->branch;
        $isMainBranchUser = $userBranch && strtoupper($userBranch->name ?? '') === 'MAIN BRANCH';
        
        // Main Branch user defaults to viewing ALL branches (no filter)
        if ($isMainBranchUser) {
            $branchId = null;
        }

        $transactionsQuery = Order::with(['items.product', 'branch', 'cashier'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(20);

        // Filter by branch if user has one assigned and not a main branch user
        if ($branchId) {
            $transactionsQuery->where('branch_id', $branchId);
        }

        $transactions = $transactionsQuery->get()
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

        return response()->json(['ok' => true, 'transactions' => $transactions]);
    }

    /**
     * GET /api/manager/finance/reports?range=...&branch_id=...
     * Chart data: Monthly income, expenses, net profit (past 12 months)
     * If user has branch_id, show branch reports; if branch_id in query, use that (if permitted)
     */
    public function reports(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user || !$this->isFinanceManager($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $range = $request->query('range', 'all');
        
        // If branch_id is provided in query, use it (for filtering view)
        // Otherwise use user's own branch_id
        $requestedBranchId = $request->query('branch_id');
        $userRole = strtoupper($user->role ?? '');
        $userBranch = $user->branch;
        $isMainBranchUser = $userBranch && strtoupper($userBranch->name ?? '') === 'MAIN BRANCH';
        $branchId = $user->branch_id;
        
        // Main Branch user defaults to viewing ALL branches (no filter)
        if ($isMainBranchUser && !$requestedBranchId) {
            $branchId = null;
        }
        
        // Owner/Admin or Main Branch user can filter by any branch
        if ($requestedBranchId && (in_array($userRole, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN']) || $isMainBranchUser)) {
            $branchId = $requestedBranchId;
        } elseif ($requestedBranchId && $requestedBranchId == $user->branch_id) {
            // User requesting their own branch
            $branchId = $requestedBranchId;
        }
        
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

            // Income: include completed, approved, and in_kitchen orders (dishes are paid at checkout)
            $incomeQuery = Order::whereIn('status', ['completed', 'approved', 'in_kitchen'])
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

            // Additional expenses from Expense model (approved expenses)
            $expenseQuery = Expense::where('status', 'approved')
                ->whereBetween('created_at', [$monthStart, $monthEnd]);
            if ($branchId) {
                $expenseQuery->where('branch_id', $branchId);
            }
            $additionalExpense = (float) $expenseQuery->sum('amount');

            $expenses = (float) ($supplierExpense + $procurementExpense + $budgetExpense + $additionalExpense);

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

        return response()->json(['ok' => true, 'reports' => $reports]);
    }

    /**
     * Existing methods - unchanged
     */
    public function branches(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        $role = strtoupper($user->role ?? '');
        $branchId = $user->branch_id ?? null;
        $userBranch = $user->branch;
        $isMainBranchUser = $userBranch && strtoupper($userBranch->name ?? '') === 'MAIN BRANCH';

        if (in_array($role, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN']) || $isMainBranchUser) {
            $branches = Branch::where('is_active', true)->get();
        } else {
            $branches = Branch::where('is_active', true)->when($branchId, function ($q) use ($branchId) {
                return $q->where('id', $branchId);
            })->get();
        }

        return response()->json(['ok' => true, 'branches' => $branches]);
    }

    public function updateBranchBudget(Request $request, $id)
    {
        $request->validate([
            'budget' => 'required|numeric'
        ]);

        $user = $this->resolveAuthenticatedUser($request);
        $role = strtoupper($user->role ?? '');

        $hasFinanceModule = false;
        if ($role === 'CUSTOM') {
            $perms = $user->permissions ?? [];
            if (is_string($perms)) {
                try { $decoded = json_decode($perms, true); if (is_array($decoded)) $perms = $decoded; } catch (\Throwable $e) { $perms = []; }
            }
            if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                $hasFinanceModule = collect($perms['modules'])->map(fn($m)=>strtolower((string)$m))->contains('finance');
            }
        }

        if (!in_array($role, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN', 'FINANCE_MANAGER', 'MANAGER']) && !$hasFinanceModule) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branch = Branch::where('id', $id)->lockForUpdate()->first();
        if (!$branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }

        try {
            $branch->budget = (float) $request->input('budget');
            $branch->save();
            return response()->json(['ok' => true, 'branch' => $branch]);
        } catch (\Exception $e) {
            Log::error('Failed to update branch budget', ['error' => $e->getMessage(), 'branch_id' => $id]);
            return response()->json(['ok' => false, 'message' => 'Failed to update budget'], 500);
        }
    }
}

