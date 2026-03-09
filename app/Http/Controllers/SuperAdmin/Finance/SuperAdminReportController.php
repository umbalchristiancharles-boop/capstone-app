<?php

namespace App\Http\Controllers\SuperAdmin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Branch;

/**
 * SuperAdmin Report Controller
 * Handles financial report export for Super Admin
 *
 * Supports CSV export for various report types
 */
class SuperAdminReportController extends Controller
{
    /**
     * Resolve authenticated user
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
    private function getDateRange($fromDate, $toDate)
    {
        if ($fromDate && $toDate) {
            return [
                \Carbon\Carbon::parse($fromDate)->startOfDay(),
                \Carbon\Carbon::parse($toDate)->endOfDay(),
            ];
        }

        $now = now();
        return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
    }

    /**
     * GET /api/superadmin/finance/export
     *
     * Export financial reports
     *
     * Query Parameters:
     * - type: Report type (sales, expense, transaction, refund, branch_performance)
     * - format: Export format (csv) - only CSV supported for now
     * - from_date: Start date (optional)
     * - to_date: End date (optional)
     * - branch_id: Filter by specific branch (optional)
     */
    public function export(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get parameters
        $type = $request->query('type', 'sales');
        $format = $request->query('format', 'csv');
        $branchId = $request->query('branch_id');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        // Validate format
        if (!in_array($format, ['csv'])) {
            return response()->json(['ok' => false, 'message' => 'Unsupported format. Supported: csv'], 400);
        }

        // Validate report type
        $allowedTypes = ['sales', 'expense', 'transaction', 'refund', 'branch_performance'];
        if (!in_array($type, $allowedTypes)) {
            return response()->json(['ok' => false, 'message' => 'Unsupported report type. Allowed: ' . implode(', ', $allowedTypes)], 400);
        }

        $dateRange = $this->getDateRange($fromDate, $toDate);

        // Generate report based on type
        switch ($type) {
            case 'sales':
                return $this->exportSalesReport($dateRange, $branchId, $format);
            case 'expense':
                return $this->exportExpenseReport($dateRange, $branchId, $format);
            case 'transaction':
                return $this->exportTransactionReport($dateRange, $branchId, $format);
            case 'refund':
                return $this->exportRefundReport($dateRange, $branchId, $format);
            case 'branch_performance':
                return $this->exportBranchPerformanceReport($dateRange, $branchId, $format);
            default:
                return response()->json(['ok' => false, 'message' => 'Unsupported report type'], 400);
        }
    }

    /**
     * Export Sales Report
     */
    private function exportSalesReport($dateRange, $branchId, $format)
    {
        $query = Order::with('branch')
            ->whereBetween('created_at', $dateRange)
            ->where('status', 'completed');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $data = $orders->map(function ($order) {
            return [
                'Order ID' => $order->id,
                'Order Code' => $order->order_code,
                'Branch' => $order->branch ? $order->branch->name : 'N/A',
                'Customer' => $order->customer_name ?? 'N/A',
                'Grand Total' => number_format($order->grand_total, 2),
                'Amount Paid' => number_format($order->amount_paid, 2),
                'Change' => number_format($order->change_amount, 2),
                'Date' => $order->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return $this->generateCsvResponse($data->toArray(), 'sales_report');
    }

    /**
     * Export Expense Report (placeholder)
     */
    private function exportExpenseReport($dateRange, $branchId, $format)
    {
        // Placeholder - no expenses table yet
        $data = [];

        return $this->generateCsvResponse($data, 'expense_report', [
            'message' => 'Expenses tracking will be available once the expenses module is implemented.'
        ]);
    }

    /**
     * Export Transaction Report
     */
    private function exportTransactionReport($dateRange, $branchId, $format)
    {
        $query = Order::with('branch')
            ->whereBetween('created_at', $dateRange);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $data = $orders->map(function ($order) {
            $type = match($order->status) {
                'completed' => 'sale',
                'cancelled' => 'refund',
                'pending' => 'pending',
                'in_kitchen' => 'processing',
                default => 'other',
            };

            return [
                'Transaction ID' => 'TXN-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                'Order ID' => $order->id,
                'Order Code' => $order->order_code,
                'Branch' => $order->branch ? $order->branch->name : 'N/A',
                'Type' => $type,
                'Amount' => number_format($order->grand_total, 2),
                'Status' => $order->status,
                'Provider' => 'cash',
                'Date' => $order->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return $this->generateCsvResponse($data->toArray(), 'transaction_report');
    }

    /**
     * Export Refund Report
     */
    private function exportRefundReport($dateRange, $branchId, $format)
    {
        $query = Order::with('branch')
            ->whereBetween('created_at', $dateRange)
            ->where('status', 'cancelled');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $data = $orders->map(function ($order) {
            return [
                'Refund ID' => 'RFD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                'Order ID' => $order->id,
                'Order Code' => $order->order_code,
                'Branch' => $order->branch ? $order->branch->name : 'N/A',
                'Amount' => number_format($order->grand_total, 2),
                'Reason' => 'Order cancelled',
                'Status' => 'completed',
                'Date' => $order->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return $this->generateCsvResponse($data->toArray(), 'refund_report');
    }

    /**
     * Export Branch Performance Report
     */
    private function exportBranchPerformanceReport($dateRange, $branchId, $format)
    {
        $branchesQuery = Branch::query();

        if ($branchId) {
            $branchesQuery->where('id', $branchId);
        }

        $branches = $branchesQuery->get()->map(function ($branch) use ($dateRange) {
            $totalSales = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', $dateRange)
                ->where('status', 'completed')
                ->sum('grand_total');

            $totalOrders = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', $dateRange)
                ->count();

            $totalRefunds = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', $dateRange)
                ->where('status', 'cancelled')
                ->sum('grand_total');

            $netProfit = $totalSales - $totalRefunds;

            return [
                'Branch Code' => $branch->code,
                'Branch Name' => $branch->name,
                'Total Sales' => number_format($totalSales, 2),
                'Total Orders' => $totalOrders,
                'Total Refunds' => number_format($totalRefunds, 2),
                'Net Profit' => number_format($netProfit, 2),
                'Is Active' => $branch->is_active ? 'Yes' : 'No',
            ];
        });

        return $this->generateCsvResponse($branches->toArray(), 'branch_performance_report');
    }

    /**
     * Generate CSV response
     */
    private function generateCsvResponse($data, $filename, $extra = [])
    {
        $headers = [];
        if (!empty($extra)) {
            $headers = $extra;
        }

        if (empty($data)) {
            return response()->json([
                'ok' => true,
                'message' => 'No data available for export',
                'filename' => $filename . '.csv',
            ]);
        }

        // Create CSV content
        $csvContent = [];

        // Add headers
        $csvContent[] = array_keys($data[0]);

        // Add data rows
        foreach ($data as $row) {
            $csvContent[] = array_values($row);
        }

        // Convert to CSV string
        $csvString = '';
        foreach ($csvContent as $line) {
            $csvString .= implode(',', $line) . "\n";
        }

        $filename = $filename . '_' . now()->format('Y-m-d_His') . '.csv';

        return response($csvString, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * GET /api/superadmin/finance/reports
     *
     * Get available report types
     */
    public function index(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $reports = [
            [
                'id' => 'sales',
                'name' => 'Sales Report',
                'description' => 'Export completed orders with sales data',
                'formats' => ['csv'],
            ],
            [
                'id' => 'expense',
                'name' => 'Expense Report',
                'description' => 'Export expense records (coming soon)',
                'formats' => ['csv'],
                'coming_soon' => true,
            ],
            [
                'id' => 'transaction',
                'name' => 'Transaction Report',
                'description' => 'Export all financial transactions',
                'formats' => ['csv'],
            ],
            [
                'id' => 'refund',
                'name' => 'Refund Report',
                'description' => 'Export refund/cancelled order records',
                'formats' => ['csv'],
            ],
            [
                'id' => 'branch_performance',
                'name' => 'Branch Performance Report',
                'description' => 'Export financial performance by branch',
                'formats' => ['csv'],
            ],
        ];

        return response()->json([
            'ok' => true,
            'reports' => $reports,
        ]);
    }
}

