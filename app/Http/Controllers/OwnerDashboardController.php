<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Expense;

class OwnerDashboardController extends Controller
{
    public function branchAnalytics(Request $request)
    {
        $range = $request->query('range', 'thisMonth');
        $now = now();
        $dateRange = match ($range) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'thisWeek' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'lastMonth' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'all' => [null, null],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
        $range = in_array($range, ['today', 'yesterday', 'thisWeek', 'thisMonth', 'lastMonth', 'all'], true)
            ? $range
            : 'thisMonth';

        $branches = Branch::query()
            ->where(function ($query) {
                $query->where('is_main_branch', false)->orWhereNull('is_main_branch');
            })
            ->orderBy('name')
            ->get()
            ->map(function (Branch $branch) use ($dateRange, $range) {
                $orders = Order::where('branch_id', $branch->id);
                $expenses = Expense::where('branch_id', $branch->id);

                if ($range !== 'all' && $dateRange[0] && $dateRange[1]) {
                    $orders->whereBetween('created_at', $dateRange);
                    $expenses->whereBetween('created_at', $dateRange);
                }

                $sales = (float) (clone $orders)->where('status', 'completed')->sum('grand_total');
                $orderCount = (int) (clone $orders)->where('status', 'completed')->count();
                $refunds = (float) (clone $orders)->where('status', 'cancelled')->sum('grand_total');
                $totalExpenses = (float) (clone $expenses)->where('status', 'approved')->sum('amount');

                return [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'branch_code' => $branch->code,
                    'is_active' => (bool) $branch->is_active,
                    'total_sales' => $sales,
                    'total_orders' => $orderCount,
                    'total_expenses' => $totalExpenses,
                    'total_refunds' => $refunds,
                    'net_profit' => $sales - $totalExpenses - $refunds,
                ];
            });

        return response()->json([
            'ok' => true,
            'branches' => $branches,
            'totals' => [
                'total_sales' => $branches->sum('total_sales'),
                'total_orders' => $branches->sum('total_orders'),
                'total_expenses' => $branches->sum('total_expenses'),
                'total_refunds' => $branches->sum('total_refunds'),
                'net_profit' => $branches->sum('net_profit'),
            ],
            'filters' => ['range' => $range],
        ]);
    }

    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user = Auth::user();

        // Count all active employees (STAFF, BRANCH_MANAGER or HR)
        $totalEmployees = User::whereIn('role', ['STAFF', 'BRANCH_MANAGER', 'HR'])
            ->where('is_active', 1)
            ->count();

        // Count all branches
        $totalBranches = Branch::count();

        // For branch manager or staff, count employees in their branch only
        $branchEmployees = null;
        if (in_array($user->role, ['BRANCH_MANAGER', 'STAFF', 'HR']) && $user->branch_id) {
            // Only count STAFF (not branch manager) if the user is STAFF; include HR in branch counts
            $roles = $user->role === 'STAFF' ? ['STAFF'] : ['STAFF', 'BRANCH_MANAGER', 'HR'];
            $branchEmployees = User::whereIn('role', $roles)
                ->where('is_active', 1)
                ->where('branch_id', $user->branch_id)
                ->count();
        }

        // Example: you can add this to the response for frontend use
        return response()->json([
            'ok' => true,
            'totals' => [
                'orders'    => 12,
                'completed' => 10,
                'sales'     => '₱5,240',
                'pending'   => 2,
            ],
            'summary' => [
                'totalBranches'  => $totalBranches,
                'totalEmployees' => $totalEmployees,
                'branchEmployees' => $branchEmployees, // null for admin, number for branch manager/staff
            ],
            // ...existing code for recentOrders, productionQueue, etc...
            'recentOrders' => [
                [
                    'id'          => 1,
                    'code'        => '#ORD001',
                    'customer'    => 'John Doe',
                    'status'      => 'completed',
                    'statusLabel' => 'Completed',
                    'total'       => '₱850',
                ],
                [
                    'id'          => 2,
                    'code'        => '#ORD002',
                    'customer'    => 'Jane Smith',
                    'status'      => 'in_kitchen',
                    'statusLabel' => 'In Kitchen',
                    'total'       => '₱620',
                ],
                [
                    'id'          => 3,
                    'code'        => '#ORD003',
                    'customer'    => 'Bob Wilson',
                    'status'      => 'pending',
                    'statusLabel' => 'Pending',
                    'total'       => '₱450',
                ],
                [
                    'id'          => 4,
                    'code'        => '#ORD004',
                    'customer'    => 'Alice Brown',
                    'status'      => 'completed',
                    'statusLabel' => 'Completed',
                    'total'       => '₱1,200',
                ],
                [
                    'id'          => 5,
                    'code'        => '#ORD005',
                    'customer'    => 'Charlie Davis',
                    'status'      => 'completed',
                    'statusLabel' => 'Completed',
                    'total'       => '₱920',
                ],
            ],
            'productionQueue' => [
                [
                    'id'         => 1,
                    'title'      => 'Ube Cake - Order #ORD002',
                    'meta'       => 'Started 10 mins ago',
                    'badgeLabel' => 'In Progress',
                    'badgeClass' => 'badge--warning',
                ],
                [
                    'id'         => 2,
                    'title'      => 'Mochi Bread - Order #ORD003',
                    'meta'       => 'Queue position: 2',
                    'badgeLabel' => 'Queued',
                    'badgeClass' => 'badge--info',
                ],
            ],
            'topProducts' => [
                ['id' => 1, 'name' => 'Ube Cake',   'orders' => 8],
                ['id' => 2, 'name' => 'Mochi Bread','orders' => 6],
                ['id' => 3, 'name' => 'Croissant',  'orders' => 5],
            ],
            'lowStockItems' => [
                ['id' => 1, 'name' => 'Ube Powder', 'qty' => 2, 'unit' => 'kg'],
                ['id' => 2, 'name' => 'Butter',     'qty' => 3, 'unit' => 'kg'],
            ],
            'staffActivity' => [
                ['id' => 1, 'message' => 'Order #ORD001 completed', 'meta' => '2 mins ago'],
                ['id' => 2, 'message' => 'Maria clocked in',        'meta' => '15 mins ago'],
                ['id' => 3, 'message' => 'Inventory updated',       'meta' => '1 hour ago'],
            ],
        ]);
    }
}
