<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;

class ManagerFinanceController extends Controller
{
    /**
     * List branches (for finance manager visibility). Owners/superadmins see all; managers see own branch.
     */
    public function branches(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $branchId = $user->branch_id ?? null;

        if (in_array($role, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])) {
            $branches = Branch::where('is_active', true)->get();
        } else {
            $branches = Branch::where('is_active', true)->when($branchId, function ($q) use ($branchId) {
                return $q->where('id', $branchId);
            })->get();
        }

        return response()->json(['ok' => true, 'branches' => $branches]);
    }

    /**
     * Update a branch's budget (finance manager action).
     */
    public function updateBranchBudget(Request $request, $id)
    {
        $request->validate([
            'budget' => 'required|numeric'
        ]);

        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        // Only finance manager / owner / superadmin can update budgets
        $hasFinanceModule = false;
        if ($role === 'CUSTOM') {
            $perms = $user->permissions ?? [];
            if (is_string($perms)) {
                try { $decoded = json_decode($perms, true); if (is_array($decoded)) $perms = $decoded; } catch (\Throwable $e) { $perms = []; }
            }
            if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                $hasFinanceModule = collect($perms['modules'])->map(fn($m)=>strtolower((string)$m))->contains('finance');
            }
        }

        if (!in_array($role, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN', 'FINANCE_MANAGER', 'MANAGER']) && !$hasFinanceModule) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branch = Branch::where('id', $id)->lockForUpdate()->first();
        if (!$branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }

        try {
            $branch->budget = (float) $request->input('budget');
            $branch->save();
            return response()->json(['ok' => true, 'branch' => $branch]);
        } catch (\Exception $e) {
            Log::error('Failed to update branch budget', ['error' => $e->getMessage(), 'branch_id' => $id]);
            return response()->json(['ok' => false, 'message' => 'Failed to update budget'], 500);
        }
    }
}
