<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    /**
     * Generate sales report for branch
     */
    public function salesReport(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Get orders within date range
        $orders = Order::where('branch_id', $user->branch_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalSales = $orders->where('status', 'completed')->sum('grand_total');
        $totalOrders = $orders->count();
        $completedOrders = $orders->where('status', 'completed')->count();
        $cancelledOrders = $orders->where('status', 'cancelled')->count();

        // Daily breakdown
        $dailySales = $orders->where('status', 'completed')
            ->groupBy(function ($order) {
                return $order->created_at->format('Y-m-d');
            })
            ->map(function ($dayOrders) {
                return [
                    'total' => $dayOrders->sum('grand_total'),
                    'count' => $dayOrders->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'report' => [
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
                'summary' => [
                    'total_sales' => $totalSales,
                    'total_orders' => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'cancelled_orders' => $cancelledOrders,
                    'average_order_value' => $completedOrders > 0 ? $totalSales / $completedOrders : 0,
                ],
                'daily_sales' => $dailySales,
            ],
        ]);
    }

    /**
     * Generate staff performance report
     */
    public function staffPerformanceReport(Request $request)
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
                // TODO: Add actual performance metrics from orders/attendance
                return [
                    'id' => $s->id,
                    'name' => $s->full_name,
                    'orders_handled' => rand(20, 50), // Mock data
                    'attendance_rate' => rand(85, 100), // Mock data
                    'avg_prep_time' => rand(10, 15) . ' min', // Mock data
                    'customer_rating' => number_format(rand(40, 50) / 10, 1), // Mock data
                ];
            });

        return response()->json([
            'success' => true,
            'report' => [
                'total_staff' => $staff->count(),
                'staff_performance' => $staff,
            ],
        ]);
    }

    /**
     * Generate inventory usage report
     */
    public function inventoryReport(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Mock inventory usage data
        $report = [
            [
                'item' => 'Chicken Breast',
                'starting_stock' => 100,
                'received' => 50,
                'used' => 105,
                'current_stock' => 45,
                'waste' => 2,
            ],
            [
                'item' => 'French Fries',
                'starting_stock' => 30,
                'received' => 20,
                'used' => 42,
                'current_stock' => 8,
                'waste' => 1,
            ],
            [
                'item' => 'Gravy',
                'starting_stock' => 20,
                'received' => 10,
                'used' => 25,
                'current_stock' => 5,
                'waste' => 0.5,
            ],
        ];

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Export report as CSV
     */
    public function exportCSV(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $type = $request->query('type', 'sales');

        // TODO: Implement actual CSV generation
        // For now, return success message
        return response()->json([
            'success' => true,
            'message' => 'Report export initiated',
            'download_url' => '/api/manager/reports/download/' . $type . '/' . time(),
        ]);
    }
}
