<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManagerDashboardController extends Controller
{
    /**
     * Get Branch Manager Dashboard Data
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        // Only Branch Managers can access this
        if ($user->role !== 'BRANCH_MANAGER') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'No branch assigned'], 400);
        }

        $branch = Branch::find($user->branch_id);

        if (!$branch) {
            return response()->json(['success' => false, 'message' => 'Branch not found'], 404);
        }

        $range = $request->query('range', 'today');

        // Calculate date range
        $dateRange = $this->getDateRange($range);

        // Get branch statistics
        $stats = [
            'orders' => Order::where('branch_id', $user->branch_id)
                ->whereBetween('created_at', $dateRange)
                ->count(),
            'completed' => Order::where('branch_id', $user->branch_id)
                ->where('status', 'completed')
                ->whereBetween('created_at', $dateRange)
                ->count(),
            'pending' => Order::where('branch_id', $user->branch_id)
                ->whereIn('status', ['pending', 'in_kitchen'])
                ->whereBetween('created_at', $dateRange)
                ->count(),
            'sales' => Order::where('branch_id', $user->branch_id)
                ->where('status', 'completed')
                ->whereBetween('created_at', $dateRange)
                ->sum('total_price'),
        ];

        // Get staff count for this branch
        $staffCount = User::where('branch_id', $user->branch_id)
            ->where('role', 'STAFF')
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->count();

        // Get recent orders
        $recentOrders = Order::where('branch_id', $user->branch_id)
            ->whereBetween('created_at', $dateRange)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'code' => $order->order_number ?? 'ORD-' . $order->id,
                    'customer' => $order->customer_name ?? 'Guest',
                    'status' => $order->status,
                    'statusLabel' => ucfirst(str_replace('_', ' ', $order->status)),
                    'total' => '₱' . number_format($order->total_price, 2),
                    'created_at' => $order->created_at->format('M d, Y H:i'),
                ];
            });

        // Get production queue (pending/in_kitchen orders)
        $productionQueue = Order::where('branch_id', $user->branch_id)
            ->whereIn('status', ['pending', 'in_kitchen'])
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'title' => 'Order #' . ($order->order_number ?? $order->id),
                    'meta' => $order->customer_name ?? 'Guest',
                    'badgeLabel' => ucfirst(str_replace('_', ' ', $order->status)),
                    'badgeClass' => $order->status === 'in_kitchen' ? 'badge--warning' : 'badge--info',
                ];
            });

        // Get staff activity
        $staffActivity = User::where('branch_id', $user->branch_id)
            ->where('role', 'STAFF')
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($staff) {
                return [
                    'name' => $staff->full_name ?? $staff->username,
                    'role' => 'Staff',
                    'action' => 'Active',
                    'time' => $staff->updated_at->diffForHumans(),
                ];
            });

        // Get top products (mock data for now - you can implement based on order items)
        $topProducts = [
            ['name' => 'Fried Chicken (1pc)', 'sold' => 45],
            ['name' => 'Chicken Bucket (8pc)', 'sold' => 32],
            ['name' => 'Spicy Wings (6pc)', 'sold' => 28],
        ];

        // Get low stock items (mock data - implement based on your inventory system)
        $lowStockItems = [
            ['item' => 'Chicken Breast', 'qty' => 15],
            ['item' => 'French Fries', 'qty' => 8],
            ['item' => 'Gravy', 'qty' => 5],
        ];

        return response()->json([
            'success' => true,
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'address' => $branch->address,
            ],
            'stats' => [
                'orders' => $stats['orders'],
                'completed' => $stats['completed'],
                'pending' => $stats['pending'],
                'sales' => '₱' . number_format($stats['sales'], 2),
            ],
            'summary' => [
                'totalEmployees' => $staffCount,
                'activeStaff' => $staffCount, // Could be enhanced with clock-in data
            ],
            'recentOrders' => $recentOrders,
            'productionQueue' => $productionQueue,
            'staffActivity' => $staffActivity,
            'topProducts' => $topProducts,
            'lowStockItems' => $lowStockItems,
        ]);
    }

    /**
     * Get Branch Staff List
     */
    public function getStaff(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $staff = User::where('branch_id', $user->branch_id)
            ->where('role', 'STAFF')
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'username' => $s->username,
                    'full_name' => $s->full_name,
                    'email' => $s->email,
                    'phone_number' => $s->phone_number,
                    'avatar_url' => $s->avatar_url,
                    'is_active' => $s->is_active,
                ];
            });

        return response()->json([
            'success' => true,
            'staff' => $staff,
        ]);
    }

    /**
     * Calculate date range based on filter
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
            case 'lastWeek':
                return [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()];
            case 'thisMonth':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'lastMonth':
                return [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()];
            default:
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
        }
    }
}
