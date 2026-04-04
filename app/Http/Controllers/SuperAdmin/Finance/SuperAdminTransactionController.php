<?php

namespace App\Http\Controllers\SuperAdmin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Branch;

/**
 * SuperAdmin Transaction Controller
 * Handles transaction monitoring for Super Admin
 *
 * Returns list of financial transactions across all branches
 * Derived from the orders table
 */
class SuperAdminTransactionController extends Controller
{
    use FinancialTrait;

    /**
     * GET /api/superadmin/finance/transactions
     *
     * Return a list of financial transactions across all branches
     * Filters by date range, branch, and status
     * Validates for data consistency and completeness
     *
     * Query Parameters:
     * - from_date: Start date (optional)
     * - to_date: End date (optional)
     * - branch_id: Filter by specific branch (optional)
     * - status: Filter by order status (optional)
     * - page: Page number (optional, default 1)
     * - per_page: Items per page (optional, default 15, max 100)
     *
     * Returns:
     * - transaction_id: Unique transaction ID
     * - branch_id, order_id, order_code: Order identifiers
     * - type: Transaction type (sale, refund, pending, processing)
     * - amount, amount_paid, change_amount: Financial amounts
     * - status: Order status
     * - provider: Payment provider
     */
    public function index(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return $this->unauthorizedResponse();
        }

        // Get filter parameters
        $branchId = $request->query('branch_id');
        $status = $request->query('status');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        // Validate pagination
        [$page, $perPage] = $this->validatePagination($page, $perPage);

        // Get date range
        $dateRange = $this->getDateRangeFromDates($fromDate, $toDate);

        // Build query
        $query = Order::with(['branch', 'cancelledBy'])
            ->whereBetween('created_at', $dateRange);

        // Apply filters
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($status) {
            // Validate status - only allow known statuses
            $validStatuses = ['completed', 'cancelled', 'pending', 'in_kitchen', 'approved'];
            if (in_array($status, $validStatuses)) {
                $query->where('status', $status);
            }
        }

        // Get total count
        $total = $query->count();

        // Get paginated results
        $transactions = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($order) {
                // Determine transaction type based on order status
                $type = match($order->status) {
                    'completed' => 'sale',
                    'cancelled' => 'refund',
                    'pending' => 'pending',
                    'in_kitchen' => 'processing',
                    'approved' => 'approved',
                    default => 'other',
                };

                // Provider - derive from payment method or default
                $provider = 'cash'; // Default payment provider
                if ($order->amount_paid > 0) {
                    $provider = 'cash';
                }

                return [
                    'transaction_id' => 'TXN-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'branch_id' => $order->branch_id,
                    'branch_name' => $order->branch ? $order->branch->name : 'N/A',
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'type' => $type,
                    'amount' => (float) $order->grand_total,
                    'fee' => 0.0, // No fee tracking in current system
                    'status' => $order->status,
                    'provider' => $provider,
                    'customer_name' => $order->customer_name,
                    'amount_paid' => (float) $order->amount_paid,
                    'change_amount' => (float) $order->change_amount,
                    'created_at' => $order->created_at->toISOString(),
                    'updated_at' => $order->updated_at->toISOString(),
                    // Refund / cancellation metadata
                    'cancelled_at' => $order->cancelled_at ? $order->cancelled_at->toISOString() : null,
                    'cancelled_by' => $order->cancelled_by,
                    'cancelled_by_name' => $order->cancelledBy ? ($order->cancelledBy->name ?? null) : null,
                    'refund_reason' => $order->refund_reason ?? null,
                ];
            });

        return response()->json([
            'ok' => true,
            'transactions' => $transactions,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
            ],
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_id' => $branchId,
                'status' => $status,
            ],
        ]);
    }

    /**
     * GET /api/superadmin/finance/transactions/{id}
     *
     * Get a specific transaction by ID
     */
    public function show(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $order = Order::with('branch')->find($id);

        if (!$order) {
            return response()->json(['ok' => false, 'message' => 'Transaction not found'], 404);
        }

        $type = match($order->status) {
            'completed' => 'sale',
            'cancelled' => 'refund',
            'pending' => 'pending',
            'in_kitchen' => 'processing',
            default => 'other',
        };

        return response()->json([
            'ok' => true,
            'transaction' => [
                'transaction_id' => 'TXN-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                'branch_id' => $order->branch_id,
                'branch_name' => $order->branch ? $order->branch->name : 'N/A',
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'type' => $type,
                'amount' => (float) $order->grand_total,
                'fee' => 0.0,
                'status' => $order->status,
                'provider' => 'cash',
                'customer_name' => $order->customer_name,
                'amount_paid' => (float) $order->amount_paid,
                'change_amount' => (float) $order->change_amount,
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
                'cancelled_at' => $order->cancelled_at ? $order->cancelled_at->toISOString() : null,
                'cancelled_by' => $order->cancelled_by,
                'cancelled_by_name' => $order->cancelledBy ? ($order->cancelledBy->name ?? null) : null,
                'refund_reason' => $order->refund_reason ?? null,
            ],
        ]);
    }
}

