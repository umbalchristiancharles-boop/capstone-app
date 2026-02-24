<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffDashboardController extends Controller
{
    /**
     * Get Staff Dashboard Data
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        // Only STAFF can access this
        if ($user->role !== 'STAFF') {
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

        // Get personal statistics (orders handled by this staff member)
        // Note: You may need to add a staff_id column to orders table to track who handled each order
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
                ->sum('grand_total'),
        ];

        // Get recent orders for this branch
        $recentOrders = Order::where('branch_id', $user->branch_id)
            ->whereBetween('created_at', $dateRange)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'code' => $order->order_code ?? 'ORD-' . $order->id,
                    'customer' => $order->customer_name ?? 'Guest',
                    'status' => $order->status,
                    'statusLabel' => ucfirst(str_replace('_', ' ', $order->status)),
                    'total' => '₱' . number_format($order->grand_total ?? 0, 2),
                    'created_at' => $order->created_at->format('M d, Y H:i'),
                ];
            });

        // Get assigned tasks (pending orders for this branch)
        $myTasks = Order::where('branch_id', $user->branch_id)
            ->whereIn('status', ['pending', 'in_kitchen'])
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'title' => 'Order #' . ($order->order_code ?? $order->id),
                    'meta' => ($order->customer_name ?? 'Guest') . ' • ' . $order->created_at->diffForHumans(),
                    'badgeLabel' => ucfirst(str_replace('_', ' ', $order->status)),
                    'badgeClass' => $order->status === 'in_kitchen' ? 'badge--warning' : 'badge--info',
                ];
            });

        // Performance stats
        $performance = [
            'completedOrders' => $stats['completed'],
            'avgPrepTime' => '12 min', // Mock - implement based on order completion times
            'customerRating' => '4.8', // Mock - implement based on feedback
        ];

        // Recent notifications (mock data)
        $notifications = [
            ['message' => 'New order received', 'time' => '2 min ago'],
            ['message' => 'Low stock: Gravy', 'time' => '15 min ago'],
        ];

        return response()->json([
            'success' => true,
            'staff' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
            ],
            'stats' => [
                'orders' => $stats['orders'],
                'completed' => $stats['completed'],
                'pending' => $stats['pending'],
                'sales' => '₱' . number_format($stats['sales'], 2),
            ],
            'recentOrders' => $recentOrders,
            'myTasks' => $myTasks,
            'performance' => $performance,
            'notifications' => $notifications,
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
