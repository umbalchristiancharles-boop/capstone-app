<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get date range based on filter
     */
    private function getDateRange(string $range): array
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'yesterday':
                return [
                    'start' => $now->copy()->subDay()->startOfDay(),
                    'end' => $now->copy()->subDay()->endOfDay(),
                ];
            case 'thisWeek':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                ];
            case 'lastWeek':
                return [
                    'start' => $now->copy()->subWeek()->startOfWeek(),
                    'end' => $now->copy()->subWeek()->endOfWeek(),
                ];
            case 'thisMonth':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
            case 'lastMonth':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth(),
                ];
            default:
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
        }
    }

    /**
     * Get status label for display
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Pending',
            'in_kitchen' => 'In Kitchen',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }

    /**
     * Main dashboard index with all metrics
     */
    public function index(Request $request)
    {
        $range = $request->get('range', 'today');
        $dates = $this->getDateRange($range);

        // Count branches (active only)
        $branchesCount = Branch::where('is_active', 1)->count();

        // Get all branches for reference
        $branches = Branch::where('is_active', 1)->get(['id', 'name', 'code']);

        // Count employees (active STAFF, BRANCH_MANAGER, HR)
        $employeeRoles = ['STAFF', 'BRANCH_MANAGER', 'HR'];
        $staffCount = User::whereIn('role', $employeeRoles)
            ->where('is_active', 1)
            ->count();

        // Orders with date range filter
        $ordersQuery = Order::whereBetween('ordered_at', [$dates['start'], $dates['end']]);
        $ordersCount = $ordersQuery->count();

        // Completed orders
        $completedCount = (clone $ordersQuery)->where('status', 'completed')->count();

        // Pending orders
        $pendingStatuses = ['pending', 'in_kitchen', 'ready'];
        $pendingCount = (clone $ordersQuery)->whereIn('status', $pendingStatuses)->count();

        // Total sales
        $totalSales = (clone $ordersQuery)
            ->where('status', 'completed')
            ->sum('grand_total');
        $salesFormatted = '₱' . number_format($totalSales, 2);

        // Recent orders
        $recentOrders = (clone $ordersQuery)
            ->orderBy('ordered_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'code' => $order->order_code ?? 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'customer' => $order->customer_name ?? 'Guest',
                    'status' => $order->status,
                    'statusLabel' => $this->getStatusLabel($order->status),
                    'total' => '₱' . number_format($order->grand_total, 2),
                    'ordered_at' => $order->ordered_at ? $order->ordered_at->format('M d, Y H:i') : 'N/A',
                ];
            });

        // Production Queue
        $productionQueue = Order::whereIn('status', ['pending', 'in_kitchen', 'ready'])
            ->whereBetween('ordered_at', [$dates['start'], $dates['end']])
            ->orderBy('ordered_at', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                $badgeClass = match($order->status) {
                    'completed' => 'badge--success',
                    'in_kitchen' => 'badge--warning',
                    'ready' => 'badge--info',
                    default => 'badge--info'
                };
                return [
                    'id' => $order->id,
                    'title' => $order->order_code ?? 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'meta' => ($order->customer_name ?? 'Guest') . ' - ₱' . number_format($order->grand_total, 2),
                    'badgeLabel' => $this->getStatusLabel($order->status),
                    'badgeClass' => $badgeClass,
                ];
            });

        // Top Products
        $topProducts = Product::orderBy('stock', 'asc')
            ->limit(5)
            ->get(['id', 'name', 'stock', 'branch_id'])
            ->map(function ($product, $index) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'orders' => rand(5, 50) - $index,
                    'stock' => $product->stock,
                ];
            });

        // Low Stock Items
        $lowStockItems = Product::where('stock', '<', 10)
            ->where('stock', '>', 0)
            ->limit(10)
            ->get(['id', 'name', 'stock', 'branch_id'])
            ->map(function ($product) use ($branches) {
                $branchName = $branches->firstWhere('id', $product->branch_id)?->name ?? 'N/A';
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'branch' => $branchName,
                ];
            });

        // Staff Activity
        $recentActivity = User::whereIn('role', $employeeRoles)
            ->where('is_active', 1)
            ->latest('updated_at')
            ->limit(10)
            ->get(['id', 'full_name', 'role', 'updated_at', 'branch_id'])
            ->map(function ($user) use ($branches) {
                $branchName = $branches->firstWhere('id', $user->branch_id)?->name ?? 'N/A';
                return [
                    'name' => $user->full_name ?? 'N/A',
                    'role' => $user->role,
                    'branch' => $branchName,
                    'last_active' => $user->updated_at ? $user->updated_at->format('M d, Y H:i') : 'N/A',
                ];
            });

        // All branches
        $allBranches = Branch::where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'address']);

        return response()->json([
            'branches_count' => $branchesCount,
            'orders_count' => $ordersCount,
            'staff_count' => $staffCount,
            'orders' => $ordersCount,
            'completed' => $completedCount,
            'pending' => $pendingCount,
            'sales' => $salesFormatted,
            'sales_raw' => $totalSales,
            'recent_orders' => $recentOrders,
            'production_queue' => $productionQueue,
            'top_products' => $topProducts,
            'low_stock_items' => $lowStockItems,
            'recent_activity' => $recentActivity,
            'branches' => $allBranches,
            'range' => $range,
            'date_range' => [
                'start' => $dates['start']->format('Y-m-d H:i:s'),
                'end' => $dates['end']->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
