<?php

namespace App\Http\Controllers\SuperAdmin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Settlement;

/**
 * SuperAdmin Settlement Controller
 * Handles settlement/payout monitoring for Super Admin
 */
class SuperAdminSettlementController extends Controller
{
    use FinancialTrait;

    /**
     * GET /api/superadmin/finance/settlements
     *
     * Return settlement or payout records with validation
     */
    public function index(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return $this->unauthorizedResponse();
        }

        $branchId = $request->query('branch_id');
        $status = $request->query('status');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        // Validate pagination
        [$page, $perPage] = $this->validatePagination($page, $perPage);

        // Build query with filters
        $query = Settlement::with(['branch', 'processor']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($status) {
            // Validate settlement status
            $validStatuses = ['pending', 'completed', 'cancelled'];
            if (in_array($status, $validStatuses)) {
                $query->where('status', $status);
            }
        }

        // Apply date range filter if provided
        if ($fromDate && $toDate) {
            $dateRange = $this->getDateRangeFromDates($fromDate, $toDate);
            $query = $this->applyDateRangeFilter($query, $dateRange);
        }

        // Get total amount - only completed settlements count
        $completedQuery = (clone $query)->where('status', 'completed');
        $totalAmount = (float) $completedQuery->sum('amount');

        // Paginate
        $settlements = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $total = $query->count();

        return response()->json([
            'ok' => true,
            'settlements' => $settlements->map(function($settlement) {
                return [
                    'id' => $settlement->id,
                    'branch_id' => $settlement->branch_id,
                    'branch_name' => $settlement->branch ? $settlement->branch->name : null,
                    'amount' => (float) $settlement->amount,
                    'description' => $settlement->description,
                    'status' => $settlement->status,
                    'processed_by' => $settlement->processed_by,
                    'processor_name' => $settlement->processor ? $settlement->processor->full_name : null,
                    'created_at' => $settlement->created_at->toISOString(),
                ];
            }),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
            ],
            'filters' => [
                'branch_id' => $branchId,
                'status' => $status,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
            'total_completed_amount' => $totalAmount,
        ]);
    }

    /**
     * GET /api/superadmin/finance/settlements/summary
     *
     * Get settlement summary with status breakdown
     */
    public function summary(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return $this->unauthorizedResponse();
        }

        $branchId = $request->query('branch_id');

        $query = Settlement::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return response()->json([
            'ok' => true,
            'summary' => [
                'total_settlements' => (float) (clone $query)->where('status', 'completed')->sum('amount'),
                'pending' => (int) (clone $query)->where('status', 'pending')->count(),
                'completed' => (int) (clone $query)->where('status', 'completed')->count(),
                'cancelled' => (int) (clone $query)->where('status', 'cancelled')->count(),
            ],
        ]);
    }
}

