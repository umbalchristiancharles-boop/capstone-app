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
     * GET /api/superadmin/finance/settlements
     *
     * Return settlement or payout records
     */
    public function index(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $request->query('branch_id');
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        $perPage = min(max($perPage, 1), 100);

        // Build query with filters
        $query = Settlement::with(['branch', 'processor']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Get total amount
        $totalAmount = (float) $query->sum('amount');

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
                    'amount' => $settlement->amount,
                    'description' => $settlement->description,
                    'status' => $settlement->status,
                    'processed_by' => $settlement->processed_by,
                    'processor_name' => $settlement->processor ? $settlement->processor->full_name : null,
                    'created_at' => $settlement->created_at,
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
            ],
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * GET /api/superadmin/finance/settlements/summary
     *
     * Get settlement summary
     */
    public function summary(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user || !$this->isSuperAdmin($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $request->query('branch_id');

        $query = Settlement::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return response()->json([
            'ok' => true,
            'summary' => [
                'total_settlements' => (float) $query->sum('amount'),
                'pending' => (int) $query->where('status', 'pending')->count(),
                'completed' => (int) $query->where('status', 'completed')->count(),
                'cancelled' => (int) $query->where('status', 'cancelled')->count(),
            ],
        ]);
    }
}

