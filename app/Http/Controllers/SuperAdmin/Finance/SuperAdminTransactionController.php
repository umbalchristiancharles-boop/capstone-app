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

        // Default to today
        $now = now();
        return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
    }

    /**
     * GET /api/superadmin/finance/transactions
     *
     * Return a list of financial transactions across all branches
     *
     * Query Parameters:
     * - from_date: Start date (optional)
     * - to_date: End date (optional)
     * - branch_id: Filter by specific branch (optional)
     * - status: Filter by order status (optional)
     * - page: Page number (optional, default 1)
     * - per_page: Items per page (optional, default 15)
     *
     * Fields returned:
     * - transaction_id
     * - branch_id
     * - order_id
     * - type
     * - amount
     * - fee
     * - status
     * - provider
     * - created_at
     */
    public function index(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get filter parameters
        $branchId = $request->query('branch_id');
        $status = $request->query('status');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        // Validate per_page
        $perPage = min(max($perPage, 1), 100);

        // Get date range
        $dateRange = $this->getDateRange($fromDate, $toDate);

        // Build query
        $query = Order::with('branch')->whereBetween('created_at', $dateRange);

        // Apply filters
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($status) {
            $query->where('status', $status);
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
            ],
        ]);
    }
}

