<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LogisticsTransaction;
use App\Models\ProcurementRequest;
use App\Models\Product;
use App\Services\LogisticsService;
use Illuminate\Support\Facades\Log;

class LogisticsMonitoringController extends Controller
{
    /**
     * Get logistics dashboard data
     * GET /api/superadmin/logistics/dashboard
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        // Only superadmin and logistics managers can access
        $role = strtoupper($user->role ?? '');
        if ($role !== 'SUPER_ADMIN' && !in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $branchId = $request->query('branch_id');

        try {
            $logisticsService = new LogisticsService();
            $dashboardData = $logisticsService->getDashboardData($branchId);

            return response()->json([
                'ok' => true,
                'data' => $dashboardData,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch logistics dashboard', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch dashboard data'], 500);
        }
    }

    /**
     * Get all logistics transactions with filters
     * GET /api/superadmin/logistics/transactions
     */
    public function transactions(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $role = strtoupper($user->role ?? '');
        if ($role !== 'SUPER_ADMIN' && !in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $query = LogisticsTransaction::with([
                'procurementRequest.product',
                'product',
                'sourceBranch',
                'destinationBranch',
                'branch',
                'createdByUser:id,full_name,username',
                'verifiedByUser:id,full_name,username',
            ]);

            // Apply filters
            $branchId = $request->query('branch_id');
            if ($branchId && $role === 'SUPER_ADMIN') {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhere('source_branch_id', $branchId)
                      ->orWhere('destination_branch_id', $branchId);
                });
            }

            $type = $request->query('type');
            if ($type) {
                $query->where('type', $type);
            }

            $status = $request->query('status');
            if ($status) {
                $query->where('status', $status);
            }

            // Filter by procurement ID if provided
            $procurementId = $request->query('procurement_id');
            if ($procurementId) {
                $query->where('procurement_request_id', $procurementId);
            }

            // Filter by date range if provided
            $fromDate = $request->query('from_date');
            if ($fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            }

            $toDate = $request->query('to_date');
            if ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            }

            // Exclude duplicates by default (unless explicitly requested)
            $includeDuplicates = $request->boolean('include_duplicates', false);
            if (!$includeDuplicates) {
                $query->where('is_duplicate', false);
            }

            // Get recent transactions (paginated)
            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($request->query('per_page', 25));

            return response()->json([
                'ok' => true,
                'data' => $transactions,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch logistics transactions', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch transactions'], 500);
        }
    }

    /**
     * Get transactions requiring verification
     * GET /api/superadmin/logistics/pending-verification
     */
    public function pendingVerification(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $role = strtoupper($user->role ?? '');
        if ($role !== 'SUPER_ADMIN' && !in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $query = LogisticsTransaction::where('status', 'at_destination')
                ->with([
                    'procurementRequest.product',
                    'product',
                    'sourceBranch',
                    'destinationBranch',
                    'createdByUser:id,full_name,username',
                ]);

            $branchId = $request->query('branch_id');
            if ($branchId && $role === 'SUPER_ADMIN') {
                $query->where('destination_branch_id', $branchId);
            }

            $transactions = $query->orderBy('at_destination_at', 'asc')
                ->get();

            return response()->json([
                'ok' => true,
                'count' => $transactions->count(),
                'data' => $transactions,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch pending verification', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch pending'], 500);
        }
    }

    /**
     * Get inventory variance report
     * GET /api/superadmin/logistics/variances
     */
    public function variances(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $role = strtoupper($user->role ?? '');
        if ($role !== 'SUPER_ADMIN' && !in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $logisticsService = new LogisticsService();
            $branchId = $request->query('branch_id');
            $variances = $logisticsService->reconcileInventory($branchId);

            return response()->json([
                'ok' => true,
                'count' => count($variances),
                'data' => $variances,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate variance report', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to generate report'], 500);
        }
    }

    /**
     * Update logistics transaction status
     * POST /api/superadmin/logistics/transactions/{id}/update-status
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $role = strtoupper($user->role ?? '');
        if ($role !== 'SUPER_ADMIN' && !in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_transit,at_destination,verified,confirmed,completed,cancelled',
            'actual_quantity' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $logisticsService = new LogisticsService();
            $transaction = $logisticsService->updateTransactionStatus(
                $id,
                $validated['status'],
                $user->id,
                [
                    'actual_quantity' => $validated['actual_quantity'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            return response()->json([
                'ok' => true,
                'message' => 'Transaction status updated',
                'data' => $transaction,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update logistics transaction', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to update: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Get logistics report for period
     * GET /api/superadmin/logistics/report
     */
    public function report(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $role = strtoupper($user->role ?? '');
        if ($role !== 'SUPER_ADMIN' && !in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $fromDate = $request->query('from_date');
            $toDate = $request->query('to_date');
            $branchId = $request->query('branch_id');

            $query = LogisticsTransaction::where('is_duplicate', false);

            if ($fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            }

            if ($branchId && $role === 'SUPER_ADMIN') {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhere('source_branch_id', $branchId)
                      ->orWhere('destination_branch_id', $branchId);
                });
            }

            // Calculate statistics
            $totalTransactions = clone $query;
            $byStatus = clone $query;
            $byType = clone $query;
            $withVariance = clone $query;

            $report = [
                'period' => [
                    'from' => $fromDate ?? 'All time',
                    'to' => $toDate ?? 'Present',
                ],
                'summary' => [
                    'total_transactions' => $totalTransactions->count(),
                    'by_status' => $byStatus->select('status')
                        ->groupBy('status')
                        ->selectRaw('status, count(*) as count')
                        ->get()
                        ->pluck('count', 'status')
                        ->toArray(),
                    'by_type' => $byType->select('type')
                        ->groupBy('type')
                        ->selectRaw('type, count(*) as count')
                        ->get()
                        ->pluck('count', 'type')
                        ->toArray(),
                ],
                'variances' => [
                    'total_with_variance' => $withVariance
                        ->whereNotNull('actual_quantity')
                        ->whereRaw('actual_quantity != expected_quantity')
                        ->count(),
                ],
            ];

            return response()->json([
                'ok' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate report', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to generate report'], 500);
        }
    }
}
