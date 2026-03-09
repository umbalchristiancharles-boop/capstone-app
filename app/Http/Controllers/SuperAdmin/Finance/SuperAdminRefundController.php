<?php

namespace App\Http\Controllers\SuperAdmin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Branch;

class SuperAdminRefundController extends Controller
{
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

    private function isSuperAdmin($user)
    {
        if (!$user) {
            return false;
        }
        $roleUpper = strtoupper($user->role ?? '');
        return $roleUpper === 'SUPER_ADMIN' || $roleUpper === 'SUPERADMIN';
    }

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

    public function index(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $request->query('branch_id');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        $perPage = min(max($perPage, 1), 100);
        $dateRange = $this->getDateRange($fromDate, $toDate);

        $query = Order::with('branch')
            ->whereBetween('created_at', $dateRange)
            ->where('status', 'cancelled');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $total = $query->count();
        $totalAmount = (float) $query->sum('grand_total');

        $refunds = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($order) {
                return [
                    'refund_id' => 'RFD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'order_id' => $order->id,
                    'transaction_id' => 'TXN-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'branch_id' => $order->branch_id,
                    'branch_name' => $order->branch ? $order->branch->name : 'N/A',
                    'amount' => (float) $order->grand_total,
                    'reason' => 'Order cancelled',
                    'status' => 'completed',
                    'processed_by' => $order->cashier_id ? 'Cashier #' . $order->cashier_id : 'System',
                    'processed_at' => $order->updated_at->toISOString(),
                    'original_order_code' => $order->order_code,
                    'customer_name' => $order->customer_name,
                    'created_at' => $order->created_at->toISOString(),
                ];
            });

        return response()->json([
            'ok' => true,
            'refunds' => $refunds,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
            ],
            'total_amount' => $totalAmount,
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_id' => $branchId,
            ],
        ]);
    }

    public function summary(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $request->query('branch_id');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $dateRange = $this->getDateRange($fromDate, $toDate);

        $query = Order::whereBetween('created_at', $dateRange)
            ->where('status', 'cancelled');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $totalRefunds = (int) $query->count();
        $totalAmount = (float) $query->sum('grand_total');
        $averageRefund = $totalRefunds > 0 ? $totalAmount / $totalRefunds : 0;

        $byBranch = Order::select('branch_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(grand_total) as total'))
            ->whereBetween('created_at', $dateRange)
            ->where('status', 'cancelled')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->groupBy('branch_id')
            ->with('branch')
            ->get()
            ->map(function ($item) {
                return [
                    'branch_id' => $item->branch_id,
                    'branch_name' => $item->branch ? $item->branch->name : 'N/A',
                    'count' => (int) $item->count,
                    'total' => (float) $item->total,
                ];
            });

        return response()->json([
            'ok' => true,
            'summary' => [
                'total_refunds' => $totalRefunds,
                'total_amount' => $totalAmount,
                'average_refund' => $averageRefund,
                'by_branch' => $byBranch,
            ],
        ]);
    }
}

