<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Settlement;

/**
 * Staff Finance Controller
 * Handles finance-related operations for staff with finance department
 */
class StaffFinanceController extends Controller
{
    /**
     * Get finance logs for the staff's branch
     * Returns orders, expenses, and settlements for the user's assigned branch
     */
    public function logs(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Get the user's branch
        $branchId = $user->branch_id;

        if (!$branchId) {
            return response()->json([
                'ok' => false,
                'message' => 'No branch assigned to user'
            ], 400);
        }

        // Get filter parameters
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $type = $request->query('type', 'all'); // all, orders, expenses, settlements
        $perPage = (int) $request->query('per_page', 50);
        $page = (int) $request->query('page', 1);

        $perPage = min(max($perPage, 1), 100);

        $logs = [];

        // Get orders/transactions for the branch
        if ($type === 'all' || $type === 'orders') {
            $ordersQuery = Order::where('branch_id', $branchId);

            if ($fromDate) {
                $ordersQuery->whereDate('ordered_at', '>=', $fromDate);
            }
            if ($toDate) {
                $ordersQuery->whereDate('ordered_at', '<=', $toDate);
            }

            $orders = $ordersQuery->orderBy('ordered_at', 'desc')
                ->limit($perPage)
                ->get();

            foreach ($orders as $order) {
                $logs[] = [
                    'id' => $order->id,
                    'type' => 'order',
                    'description' => 'Sale - Order #' . $order->order_code,
                    'amount' => $order->grand_total,
                    'status' => $order->status,
                    'created_at' => $order->ordered_at,
                ];
            }
        }

        // Get expenses for the branch
        if ($type === 'all' || $type === 'expenses') {
            $expensesQuery = Expense::where('branch_id', $branchId);

            if ($fromDate) {
                $expensesQuery->whereDate('created_at', '>=', $fromDate);
            }
            if ($toDate) {
                $expensesQuery->whereDate('created_at', '<=', $toDate);
            }

            $expenses = $expensesQuery->orderBy('created_at', 'desc')
                ->limit($perPage)
                ->get();

            foreach ($expenses as $expense) {
                $logs[] = [
                    'id' => $expense->id,
                    'type' => 'expense',
                    'description' => $expense->description ?? 'Expense',
                    'amount' => $expense->amount,
                    'status' => $expense->status,
                    'created_at' => $expense->created_at,
                ];
            }
        }

        // Get settlements for the branch
        if ($type === 'all' || $type === 'settlements') {
            $settlementsQuery = Settlement::where('branch_id', $branchId);

            if ($fromDate) {
                $settlementsQuery->whereDate('created_at', '>=', $fromDate);
            }
            if ($toDate) {
                $settlementsQuery->whereDate('created_at', '<=', $toDate);
            }

            $settlements = $settlementsQuery->orderBy('created_at', 'desc')
                ->limit($perPage)
                ->get();

            foreach ($settlements as $settlement) {
                $logs[] = [
                    'id' => $settlement->id,
                    'type' => 'settlement',
                    'description' => $settlement->description ?? 'Settlement',
                    'amount' => $settlement->amount,
                    'status' => $settlement->status,
                    'created_at' => $settlement->created_at,
                ];
            }
        }

        // Sort logs by date (newest first)
        usort($logs, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Apply pagination
        $total = count($logs);
        $logs = array_slice($logs, ($page - 1) * $perPage, $perPage);

        // Calculate totals
        $totalOrders = Order::where('branch_id', $branchId)
            ->where('status', 'completed')
            ->sum('grand_total');
        $totalExpenses = Expense::where('branch_id', $branchId)
            ->where('status', 'approved')
            ->sum('amount');
        $totalSettlements = Settlement::where('branch_id', $branchId)
            ->where('status', 'completed')
            ->sum('amount');

        return response()->json([
            'ok' => true,
            'logs' => $logs,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
            ],
            'summary' => [
                'total_sales' => (float) $totalOrders,
                'total_expenses' => (float) $totalExpenses,
                'total_settlements' => (float) $totalSettlements,
                'net' => (float) ($totalOrders - $totalExpenses - $totalSettlements),
            ],
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'type' => $type,
            ]
        ]);
    }
}

