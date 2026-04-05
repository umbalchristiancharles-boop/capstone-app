<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Order;
use App\Models\BudgetRequest;
use App\Models\ProcurementRequest;
use App\Models\SupplierOrder;
use App\Models\Branch;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

class ManagerProfileController extends Controller
{
    /**
     * Get the authenticated manager's profile
     * Handles auth:sanctum,web middleware which supports both Bearer tokens and sessions
     */
    private function getAuthenticatedManager(Request $request)
    {
        // 1. Try $request->user() first - set by the middleware for both Sanctum and session auth
        $user = $request->user();
        if ($user && $user instanceof User && $user->is_active) {
            Log::debug('[getAuthenticatedManager] request->user() success', ['user_id' => $user->id, 'username' => $user->username]);
            return $user;
        }

        // 2. Fallback: Laravel Auth (session-based or already authenticated)
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && $user instanceof User && $user->is_active) {
                Log::debug('[getAuthenticatedManager] Auth::check() success', ['user_id' => $user->id, 'username' => $user->username]);
                return $user;
            }
        }

        // 3. Session fallback (for edge cases)
        try {
            $userId = $request->session()->get('user_id');
            if ($userId) {
                $user = User::find($userId);
                if ($user && $user->is_active) {
                    Log::debug('[getAuthenticatedManager] Session fallback success', ['user_id' => $user->id]);
                    return $user;
                }
            }
        } catch (\Exception $e) {
            Log::debug('[getAuthenticatedManager] Session fallback error', ['error' => $e->getMessage()]);
        }

        // 4. Sanctum Bearer token manual lookup (last resort)
        try {
            $bearerToken = $request->bearerToken();
            if ($bearerToken && class_exists(PersonalAccessToken::class)) {
                $accessToken = PersonalAccessToken::findToken($bearerToken);
                if ($accessToken && $accessToken->tokenable instanceof User && $accessToken->tokenable->is_active) {
                    $user = $accessToken->tokenable;
                    Log::debug('[getAuthenticatedManager] Bearer token manual lookup success', ['user_id' => $user->id, 'username' => $user->username]);
                    // Populate Auth facade for controller compatibility
                    Auth::login($user);
                    return $user;
                }
            }
        } catch (\Exception $e) {
            Log::debug('[getAuthenticatedManager] Bearer token manual lookup failed', ['error' => $e->getMessage()]);
        }

        Log::warning('[getAuthenticatedManager] All auth methods failed. request->bearerToken: ' . ($request->bearerToken() ? 'present' : 'absent') . ', Auth::check: ' . (Auth::check() ? 'true' : 'false'));
        return null;
    }

    /**
     * Check whether given user is considered a manager (any manager flavor, includes CUSTOM).
     */
    private function isManager($user): bool
    {
        if (!$user) return false;
        $role = strtoupper($user->role ?? '');
        // Include CUSTOM since custom accounts can have any modules assigned
        if (in_array($role, ['MANAGER', 'MANAGER_HR', 'MANAGER_FINANCE', 'MANAGER_LOGISTICS', 'BRANCH_MANAGER', 'CUSTOM'])) return true;
        // Fallback: treat any role string containing 'MANAGER' as manager
        return str_contains($role, 'MANAGER');
    }

    /**
     * Check if user has a module/department access (supports custom accounts with permissions.modules).
     */
    private function customHasModule($user, string $module): bool
    {
        if (!$user) return false;

        $perms = $user->permissions ?? [];
        if (is_string($perms)) {
            try {
                $decoded = json_decode($perms, true);
                if (is_array($decoded)) $perms = $decoded;
            } catch (\Throwable $e) {
                $perms = [];
            }
        }

        $mods = [];
        if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
            $mods = $perms['modules'];
        } elseif (is_array($perms)) {
            $mods = $perms;
        }

        return collect($mods)
            ->filter()
            ->map(fn($m) => strtolower((string) $m))
            ->contains(strtolower($module));
    }

    /**
     * Generalized access check: managers with department, owners/admins, or custom accounts with module.
     */
    private function allowManagerDept($user, string $department): bool
    {
        if (!$user) return false;

        $role = strtoupper($user->role ?? '');
        if (in_array($role, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])) return true;

        if (in_array($role, ['MANAGER', 'MANAGER_HR', 'BRANCH_MANAGER'])) {
            return $this->hasDepartmentAccess($user, $department);
        }

        if ($role === 'CUSTOM') {
            return $this->customHasModule($user, $department);
        }

        return false;
    }

    /**
     * Check if user has access to specific department
     */
    private function hasDepartmentAccess($user, $department)
    {
        if (!$user) {
            return false;
        }

        $userDept = strtoupper($user->department ?? '');
        $targetDept = strtoupper($department);

        // MANAGER_HR has access to HR
        if (strtoupper($user->role ?? '') === 'MANAGER_HR' && $targetDept === 'HR') {
            return true;
        }

        return $userDept === $targetDept || $userDept === strtoupper($targetDept);
    }

    /**
     * Resolve branch record for authenticated manager.
     */
    private function resolveUserBranch($user)
    {
        if (!$user || !$user->branch_id) {
            return null;
        }

        return Branch::find($user->branch_id);
    }

    /**
     * Main branch logistics manager can select branches but cannot create procurement requests.
     */
    private function isMainBranchLogisticsManager($user)
    {
        if (!$user || !$this->allowManagerDept($user, 'logistics')) {
            return false;
        }

        $branch = $this->resolveUserBranch($user);
        if (!$branch) {
            return false;
        }

        $branchName = strtoupper(trim((string) ($branch->name ?? '')));
        return (int) $branch->id === 1 || str_contains($branchName, 'MAIN BRANCH');
    }

    /**
     * Main branch HR manager can view/manage across all branches.
     */
    private function isMainBranchHrManager($user)
    {
        if (!$user || !$this->allowManagerDept($user, 'hr')) {
            return false;
        }

        $branch = $this->resolveUserBranch($user);
        if (!$branch) {
            return false;
        }

        $branchName = strtoupper(trim((string) ($branch->name ?? '')));
        return (int) $branch->id === 1 || str_contains($branchName, 'MAIN BRANCH');
    }

    /**
     * Check if a user has an active session within the threshold (minutes)
     */
    private function isUserOnline(int $userId): bool
    {
        try {
            $threshold = now()->subMinutes(5);

            $sessionExists = DB::table('sessions')
                ->where('user_id', $userId)
                ->where('last_activity', '>=', $threshold->timestamp)
                ->exists();

            return $sessionExists;
        } catch (\Exception $e) {
            Log::warning('Error checking user online status (manager): ' . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // HR Manager Profile Endpoints
    // ==========================================

    public function hrProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'hr')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branch = $this->resolveUserBranch($user);

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
                'branch_id' => $user->branch_id,
                'branch_name' => $branch->name ?? null,
                'must_change_password' => (bool) $user->must_change_password,
            ]
        ]);
    }

    public function updateHrProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$user || !$this->isManager($user)) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ]
        ]);
    }

    public function hrDashboard(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'hr')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $isMainBranchHr = $this->isMainBranchHrManager($user);
        $branchId = $user->branch_id;

        if ($isMainBranchHr) {
            $branches = Branch::orderBy('name', 'asc')->get(['id', 'name']);

            $counts = User::whereNotIn('role', ['OWNER', 'SUPER_ADMIN'])
                ->whereNull('deleted_at')
                ->select(
                    'branch_id',
                    DB::raw('COUNT(*) as total_staff'),
                    DB::raw("SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_staff")
                )
                ->groupBy('branch_id')
                ->get()
                ->keyBy('branch_id');

            $branchStats = $branches->map(function ($branch) use ($counts) {
                $c = $counts->get($branch->id);
                return [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'total_staff' => (int) ($c->total_staff ?? 0),
                    'active_staff' => (int) ($c->active_staff ?? 0),
                ];
            })->values();

            return response()->json([
                'ok' => true,
                'totalStaff' => (int) $branchStats->sum('total_staff'),
                'activeStaff' => (int) $branchStats->sum('active_staff'),
                'onLeave' => 0,
                'branches' => $branchStats,
            ]);
        }

        $totalStaff = User::whereNotIn('role', ['OWNER', 'SUPER_ADMIN'])
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->count();

        $activeStaff = User::whereNotIn('role', ['OWNER', 'SUPER_ADMIN'])
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->count();

        return response()->json([
            'ok' => true,
            'totalStaff' => $totalStaff,
            'activeStaff' => $activeStaff,
            'onLeave' => 0,
        ]);
    }

    /**
     * Get staff for HR Manager - filtered by branch
     */
    public function hrStaff(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'hr')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $isMainBranchHr = $this->isMainBranchHrManager($user);
        $branchId = $user->branch_id;

        $branchNames = Branch::pluck('name', 'id');

        $staffQuery = User::whereNotIn('role', ['OWNER', 'SUPER_ADMIN'])
            ->whereNull('deleted_at')
            ->orderBy('full_name', 'asc');

        if (!$isMainBranchHr) {
            $staffQuery->where('branch_id', $branchId);
        }

        $staff = $staffQuery->get()->map(function ($s) use ($branchNames) {
            $isOnline = $this->isUserOnline($s->id);
            $roleUpper = strtoupper($s->role ?? '');
            $isManagerRole = $this->isManager($s);
            $status = $s->is_active ? ($isOnline ? ($isManagerRole ? 'Working' : 'On Duty') : 'Offline') : 'Inactive';

            return [
                'id' => $s->id,
                'username' => $s->username,
                'full_name' => $s->full_name,
                'email' => $s->email,
                'phone_number' => $s->phone_number,
                'role' => $s->role,
                'department' => $s->department,
                'is_active' => $s->is_active,
                'is_online' => $isOnline,
                'status' => $status,
                'branch_id' => $s->branch_id,
                'branch_name' => $branchNames[$s->branch_id] ?? null,
                'created_at' => $s->created_at,
            ];
        });

        if ($isMainBranchHr) {
            $grouped = $staff->groupBy('branch_id');
            $branches = Branch::orderBy('name', 'asc')->get(['id', 'name']);

            $branchPayload = $branches->map(function ($branch) use ($grouped) {
                $list = $grouped->get($branch->id, collect());
                return [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'total_staff' => $list->count(),
                    'active_staff' => $list->where('is_active', 1)->count(),
                    'staff' => $list->values(),
                ];
            })->values();

            return response()->json([
                'ok' => true,
                'staff' => $staff->values(),
                'branches' => $branchPayload,
                'total_staff' => $staff->count(),
                'total_active' => $staff->where('is_active', 1)->count(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'staff' => $staff
        ]);
    }

    /**
     * Create new staff for HR Manager - assigned to manager's branch
     */
    public function createHrStaff(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'hr')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'username' => 'nullable|string|unique:users,username',
            'email' => 'nullable|email|unique:users,email',
            'fullName' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|in:BRANCH_MANAGER,MANAGER,STAFF,HR,CUSTOM',
            'department' => 'nullable|string',
            'modules' => 'nullable|array',
            'modules.*' => 'string',
            'functions' => 'nullable|array',
            'functions.*' => 'string',
        ]);

        // Force branch_id to manager's branch
        $branchId = $user->branch_id;

        if (!$branchId) {
            return response()->json([
                'ok' => false,
                'message' => 'No branch assigned to manager'
            ], 400);
        }

        // Use default password if not provided
        $password = $validated['password'] ?? 'Chikintayo_123';

        // Generate username server-side if not provided
        $username = $validated['username'] ?? null;
        if (! $username || trim($username) === '') {
            $firstName = '';
            if (! empty($validated['fullName'])) {
                $parts = preg_split('/\s+/', trim($validated['fullName']));
                $firstName = strtolower($parts[0] ?? '');
            }
            $base = preg_replace('/[^a-z0-9]/', '', $firstName) ?: 'user';
            $base = substr($base, 0, 8);
            $candidate = strtoupper($base) . rand(100, 999);
            $tries = 0;
            while (DB::table('users')->where('username', $candidate)->exists() && $tries < 10) {
                $candidate = strtoupper($base) . rand(100, 999);
                $tries++;
            }
            if (DB::table('users')->where('username', $candidate)->exists()) {
                $candidate = strtoupper($base) . substr(time(), -6);
            }
            $username = $candidate;
        }

        // Determine role (allow manager to create MANAGER if requested)
        $role = strtoupper($validated['role'] ?? 'STAFF');

        // Normalize department value
        $department = isset($validated['department']) && is_string($validated['department']) ? strtoupper($validated['department']) : null;

        // If creating MANAGER or STAFF require a valid department
        if (in_array($role, ['MANAGER', 'STAFF'])) {
            $validDepts = ['HR','FINANCE','INVENTORY','LOGISTICS','CASHIER','KITCHEN','PROCUREMENT'];
            if (! $department || ! in_array($department, $validDepts)) {
                return response()->json(['ok' => false, 'message' => 'Department is required and must be valid for Manager/Staff'], 422);
            }
        } else {
            // For non-manager/staff roles, clear department
            $department = null;
        }

        // If creating a CUSTOM account, validate and normalize provided modules/functions
        $permissionsPayload = null;
        if ($role === 'CUSTOM') {
            // Log incoming raw payload for debugging
            Log::debug('createHrStaff incoming raw modules/functions', [
                'modules_raw' => $request->input('modules'),
                'functions_raw' => $request->input('functions'),
                'modules_array_key' => $request->input('modules[]'),
            ]);

            // Allowed permission catalog (keep in sync with frontend templates)
            $allowedModules = [
                'admin', 'finance', 'logistics', 'inventory', 'procurement', 'kitchen', 'cashier', 'hr', 'reports',
            ];
            $allowedFunctions = [
                'admin.users', 'admin.branches', 'admin.settings',
                'finance.dashboard', 'finance.budget', 'finance.reports', 'finance.expenses',
                'logistics.dispatch', 'logistics.receiving', 'logistics.transfers',
                'inventory.products', 'inventory.counts', 'inventory.adjustments',
                'procurement.purchase_orders', 'procurement.suppliers', 'procurement.approvals',
                'kitchen.orders', 'kitchen.production', 'kitchen.waste',
                'cashier.pos', 'cashier.refunds', 'cashier.shifts',
                'hr.attendance', 'hr.scheduling', 'hr.payroll',
                'reports.sales', 'reports.inventory', 'reports.finance',
            ];

            // Accept modules/functions from different possible payload shapes
            $rawModulesInput = $validated['modules'] ?? $request->input('modules') ?? $request->input('modules[]') ?? [];
            $rawFunctionsInput = $validated['functions'] ?? $request->input('functions') ?? $request->input('functions[]') ?? [];

            // Normalize single comma-separated strings into arrays
            if (is_string($rawModulesInput) && strpos($rawModulesInput, ',') !== false) {
                $rawModulesInput = array_map('trim', explode(',', $rawModulesInput));
            }
            if (is_string($rawFunctionsInput) && strpos($rawFunctionsInput, ',') !== false) {
                $rawFunctionsInput = array_map('trim', explode(',', $rawFunctionsInput));
            }

            $rawModulesInput = is_array($rawModulesInput) ? $rawModulesInput : [$rawModulesInput];
            $rawFunctionsInput = is_array($rawFunctionsInput) ? $rawFunctionsInput : [$rawFunctionsInput];

            $rawModules = array_filter($rawModulesInput, fn ($m) => is_string($m) && in_array(strtolower($m), $allowedModules, true));
            $rawFunctions = array_filter($rawFunctionsInput, fn ($f) => is_string($f) && in_array(strtolower($f), array_map('strtolower', $allowedFunctions), true));

            $modules = array_values(array_unique(array_map('strtolower', $rawModules)));
            $functions = array_values(array_unique(array_map('strtolower', $rawFunctions)));

            if (!empty($modules) || !empty($functions)) {
                $permissionsPayload = [
                    'modules' => $modules,
                    'functions' => $functions,
                ];
            }

            Log::debug('createHrStaff normalized permissions', ['permissions' => $permissionsPayload]);
        }

        $staffData = [
            'username' => $username,
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($password),
            'full_name' => $validated['fullName'],
            'phone_number' => $validated['phone'] ?? '',
            'department' => $department,
            'role' => $role,
            'branch_id' => $branchId,
            'is_active' => 1,
            'must_change_password' => true,
        ];

        if ($permissionsPayload) {
            $staffData['permissions'] = $permissionsPayload;
        }

        $staff = User::create($staffData);

        return response()->json([
            'ok' => true,
            'message' => 'Staff created successfully',
            'staff' => [
                'id' => $staff->id,
                'username' => $staff->username,
                'full_name' => $staff->full_name,
                'email' => $staff->email,
                'department' => $staff->department,
                'is_active' => $staff->is_active,
                'branch_id' => $staff->branch_id,
            ]
        ], 201);
    }

    /**
     * Update staff for HR Manager - must be same branch
     */
    public function updateHrStaff(Request $request, $id)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'hr')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $user->branch_id;

        // Find staff member - must be in same branch (and not the manager themselves)
        $staff = User::where('id', $id)
            ->where('branch_id', $branchId)
            ->where('id', '!=', $user->id) // Can't edit themselves
            ->whereNotIn('role', ['OWNER', 'SUPER_ADMIN']) // Can't edit owner/superadmin
            ->first();

        if (!$staff) {
            return response()->json([
                'ok' => false,
                'message' => 'Staff not found or access denied'
            ], 404);
        }

        $validated = $request->validate([
            'fullName' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'isActive' => 'nullable|boolean',
            'password' => 'nullable|string|min:8',
        ]);

        $updateData = [];

        if (!empty($validated['fullName'])) {
            $updateData['full_name'] = $validated['fullName'];
        }
        if (!empty($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }
        if (isset($validated['phone'])) {
            $updateData['phone_number'] = $validated['phone'];
        }
        if (isset($validated['department'])) {
            $updateData['department'] = $validated['department'];
        }
        if (isset($validated['isActive'])) {
            $updateData['is_active'] = $validated['isActive'];
        }
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $staff->update($updateData);

        return response()->json([
            'ok' => true,
            'message' => 'Staff updated successfully',
            'staff' => [
                'id' => $staff->id,
                'username' => $staff->username,
                'full_name' => $staff->full_name,
                'email' => $staff->email,
                'phone_number' => $staff->phone_number,
                'department' => $staff->department,
                'is_active' => $staff->is_active,
                'branch_id' => $staff->branch_id,
            ]
        ]);
    }

    /**
     * Delete (soft delete) staff for HR Manager - must be same branch
     */
    public function deleteHrStaff(Request $request, $id)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'hr')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $user->branch_id;

        // Find staff member - must be in same branch (and not the manager themselves)
        $staff = User::where('id', $id)
            ->where('branch_id', $branchId)
            ->where('id', '!=', $user->id) // Can't delete themselves
            ->whereNotIn('role', ['OWNER', 'SUPER_ADMIN']) // Can't delete owner/superadmin
            ->first();

        if (!$staff) {
            return response()->json([
                'ok' => false,
                'message' => 'Staff not found or access denied'
            ], 404);
        }

        // Soft delete
        $staff->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Staff deleted successfully'
        ]);
    }

    public function hrReports(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'hr')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Return empty reports for now - can be enhanced
        return response()->json([
            'ok' => true,
            'reports' => []
        ]);
    }

    // ==========================================
    // Finance Manager Profile Endpoints
    // ==========================================

    public function financeProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'finance')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
                'branch_id' => $user->branch_id,
                'must_change_password' => (bool) $user->must_change_password,
            ]
        ]);
    }

    public function updateFinanceProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'finance')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ]
        ]);
    }



    // ==========================================
    // Logistics Manager Profile Endpoints
    // ==========================================

    public function logisticsProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'logistics')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branch = $this->resolveUserBranch($user);
        $isMainBranchLogistics = $this->isMainBranchLogisticsManager($user);

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
                'branch_id' => $user->branch_id,
                'branch_name' => $branch->name ?? null,
                'must_change_password' => (bool) $user->must_change_password,
                'can_select_branch' => $isMainBranchLogistics,
                'can_request_procurement' => !$isMainBranchLogistics,
            ]
        ]);
    }

    /**
     * Return available branches for logistics branch selector.
     */
public function logisticsBranches(Request $request)
    {
        Log::debug('=== logisticsBranches DEBUG ===');
        Log::debug('Auth::check(): ' . (Auth::check() ? 'YES' : 'NO'));
        Log::debug('Auth::user(): ' . json_encode(Auth::user() ? Auth::user() : null));
        Log::debug('Raw Bearer token present: ' . ($request->bearerToken() ? 'YES' : 'NO'));

        $user = $this->getAuthenticatedManager($request);
        Log::debug('getAuthenticatedManager result: ' . json_encode($user ? $user : null));

        if (!$this->allowManagerDept($user, 'logistics')) {
            Log::warning('logisticsBranches 401 BLOCKED', [
                'user' => $user ? $user->only(['id', 'username', 'role', 'department']) : null,
            ]);
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }
        Log::info('logisticsBranches PASSED auth check');

        if ($this->isMainBranchLogisticsManager($user)) {
            $branches = Branch::orderBy('name', 'asc')->get(['id', 'name']);
            return response()->json(['ok' => true, 'data' => $branches]);
        }

        $branches = Branch::where('id', $user->branch_id)->get(['id', 'name']);
        return response()->json(['ok' => true, 'data' => $branches]);
    }

    public function updateLogisticsProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'logistics')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ]
        ]);
    }

    public function logisticsDashboard(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        // Allow logistics managers OR procurement managers to list suppliers
        if (!($this->allowManagerDept($user, 'logistics') || $this->allowManagerDept($user, 'procurement'))) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'ok' => true,
            'totalDeliveries' => 0,
            'pendingDeliveries' => 0,
            'completedDeliveries' => 0,
        ]);
    }

    public function logisticsDeliveries(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'logistics')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'ok' => true,
            'deliveries' => []
        ]);
    }

    public function logisticsSuppliers(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'logistics')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $user->branch_id;
        // Allow main-branch logistics manager to view suppliers for a selected branch
        if ($this->isMainBranchLogisticsManager($user) && $request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
        }
        // Match supplier role case-insensitively and ensure active users
        $suppliers = User::whereRaw('UPPER(COALESCE(role, "")) = ?', ['SUPPLIER'])
            ->when($branchId, function ($q) use ($branchId) { return $q->where('branch_id', $branchId); })
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->select('id', 'username', 'full_name', 'email', 'phone_number')
            ->orderBy('full_name')
            ->get();

        return response()->json([
            'ok' => true,
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Return products for logistics manager (same as procurementProducts but for logistics dept)
     * GET /api/manager/logistics/products
     */
public function logisticsProducts(Request $request)
    {
        Log::debug('=== logisticsProducts DEBUG ===');
        Log::debug('Auth::check(): ' . (Auth::check() ? 'YES' : 'NO'));
        $authUser = Auth::user();
        Log::debug('Auth::user(): ' . json_encode($authUser ?? null));
        Log::debug('Raw Bearer token present: ' . ($request->bearerToken() ? 'YES' : 'NO'));

        $user = $this->getAuthenticatedManager($request);
        Log::debug('getAuthenticatedManager result: ' . json_encode($user ?? null));

        if (!$this->allowManagerDept($user, 'logistics')) {
            Log::warning('logisticsProducts 401 BLOCKED', [
                'user' => $user ? $user->only(['id', 'username', 'role', 'department']) : null,
            ]);
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }
        Log::info('logisticsProducts PASSED auth check');

        $branchId = $user->branch_id;
        if ($this->isMainBranchLogisticsManager($user) && $request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
        }

        if ($branchId && !Branch::where('id', $branchId)->exists()) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }

        if (!$branchId) {
            return response()->json(['ok' => false, 'message' => 'Manager has no branch assigned'], 400);
        }

        $products = Product::where('branch_id', $branchId)
            ->where('is_active', 1)
            ->with(['dishIngredients.dish'])
            ->select('id', 'name', 'slug', 'price', 'stock', 'sku', 'branch_id', 'supplier_name', 'is_published', 'created_at', 'updated_at', 'is_kitchen_dish')
            ->orderBy('name', 'asc')
            ->get();

        // Filter out products from unapproved kitchen dishes
        // Only show kitchen dish products if the dish has been approved by the owner
        $products = $products->filter(function ($product) {
            if (!$product->is_kitchen_dish) {
                // Non-kitchen dishes are always shown
                return true;
            }

            // For kitchen dishes, check if all related dishes are approved
            $dishIngredients = $product->dishIngredients;
            if ($dishIngredients->isEmpty()) {
                // No dish associations, show it
                return true;
            }

            // Only show if at least one associated dish is approved
            return $dishIngredients->some(function ($dishIng) {
                $dish = $dishIng->dish;
                return $dish && $dish->approval_status === 'approved';
            });
        });

        // Reindex the collection
        $products = $products->values();

        return response()->json([
            'ok' => true,
            'data' => $products,
        ]);
    }

    // ==========================================
    // Procurement Manager Profile Endpoints
    // ==========================================
    public function procurementProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'procurement')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
                'branch_id' => $user->branch_id,
                'must_change_password' => (bool) $user->must_change_password,
            ]
        ]);
    }

    public function updateProcurementProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'procurement')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ]
        ]);
    }

    public function procurementDashboard(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $role = strtoupper($user->role ?? '');

        // Allow Super Admin to query any branch by passing `branch_id` param.
        if ($role === 'SUPER_ADMIN') {
            $branchId = $request->query('branch_id') ?? 1;
        } else {
            if (!$this->allowManagerDept($user, 'procurement')) {
                return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            }
            $branchId = $user->branch_id;
        }

        $totalSuppliers = User::where('role', 'SUPPLIER')
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->count();

        $activeSuppliers = User::where('role', 'SUPPLIER')
            ->where('branch_id', $branchId)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->count();

        return response()->json([
            'ok' => true,
            'totalSuppliers' => $totalSuppliers,
            'activeSuppliers' => $activeSuppliers,
            'pendingRequests' => 0,
        ]);
    }

    /**
     * Return products available in the procurement manager's branch
     * GET /api/manager/procurement/products
     */
    public function procurementProducts(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'procurement')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $user->branch_id;

        Log::info('procurementProducts DEBUG', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'branch_id' => $branchId,
        ]);

        // All users (including CUSTOM accounts) see products from their assigned branch
        // If no branch assigned, show no products (require branch assignment)
        if (!$branchId) {
            Log::warning('procurementProducts: user has no branch assigned', ['user_id' => $user->id]);
            return response()->json(['ok' => true, 'data' => []]);
        }

        $query = Product::where('is_active', 1)
            ->where('branch_id', $branchId)
            ->select('id', 'name', 'slug', 'price', 'stock', 'sku', 'branch_id', 'supplier_name', 'supplier_id', 'is_published', 'created_at', 'updated_at');

        Log::info('procurementProducts: filtering by branch_id', ['branch_id' => $branchId]);

        $products = $query->orderBy('name', 'asc')->get();

        Log::info('procurementProducts: products count', ['count' => $products->count()]);

        // For each product, determine if procurement can acknowledge any pending request
        $products = $products->map(function ($p) use ($branchId) {
            // default: needs supplier input until supplier+price present
            $p->needs_supplier = true;
            if (!empty($p->supplier_id) && (float)($p->price ?? 0) > 0) {
                $p->needs_supplier = false;
            }

            // find a pending procurement request for this product in this branch
            $proc = ProcurementRequest::where('product_id', $p->id)
                ->where('branch_id', $branchId)
                ->where('status', 'pending')
                ->first(['id', 'status', 'budget_approved']);

            $p->procurement_request_id = $proc?->id ?? null;
            $p->procurement_status = $proc?->status ?? null;
            $p->procurement_budget_approved = $proc?->budget_approved ? true : false;

            // Acknowledge should be allowed only when a pending request exists AND supplier/price present
            $p->acknowledge_allowed = $p->procurement_request_id && !$p->needs_supplier;

            return $p;
        });

        return response()->json([
            'ok' => true,
            'data' => $products,
        ]);
    }

    /**
     * Place an order / accept a supplier product into inventory (mark as published)
     * POST /api/manager/procurement/products/{id}/place-order
     */
    public function placeOrderProduct(Request $request, $id)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'procurement')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $user->branch_id;

        if (!$branchId) {
            return response()->json(['ok' => false, 'message' => 'User has no branch assigned'], 400);
        }

        // Build query to find the product from this user's branch
        $productQuery = Product::where('id', $id)
            ->where('branch_id', $branchId);
        $product = $productQuery->first();

        if (!$product) {
            return response()->json(['ok' => false, 'message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:users,id',
            'procurement_request_id' => 'nullable|exists:procurement_requests,id'
        ]);

        Log::info('placeOrderProduct validation result', [
            'supplier_id' => $validated['supplier_id'] ?? 'not_provided',
            'procurement_request_id' => $validated['procurement_request_id'] ?? 'not_provided',
            'product_id_from_url' => $id,
            'user_branch' => $branchId
        ]);

        // Try to get procurement request to check for stored supplier_id
        $procReqForSupplierCheck = null;
        if (!empty($validated['procurement_request_id'])) {
            $procReqForSupplierCheck = ProcurementRequest::find($validated['procurement_request_id']);
        } else {
            // Try to find by product_id for backward compatibility
            $procReqForSupplierCheck = ProcurementRequest::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->first();
        }

        // Use supplier_id from request, or fall back to stored supplier_id on ProcurementRequest
        $supplierId = ($validated['supplier_id'] ?? null) ?: ($procReqForSupplierCheck?->supplier_id ?? null);

        Log::info('placeOrderProduct: supplier resolution', [
            'requested_supplier_id' => $validated['supplier_id'] ?? null,
            'stored_supplier_id' => $procReqForSupplierCheck?->supplier_id ?? null,
            'final_supplier_id' => $supplierId,
            'product_id' => $product->id
        ]);

        // If supplier_id is provided/found, create a SupplierOrder to request this product from that supplier
        if (!empty($supplierId)) {
            Log::info('placeOrderProduct SPECIFIC SUPPLIER: starting', [
                'product_id' => $product->id,
                'supplier_id' => $supplierId,
                'branch_id' => $branchId,
                'procurement_request_id' => $validated['procurement_request_id'] ?? null,
            ]);

            // If procurement_request_id provided, use it directly (for multi-supplier scenarios)
            if (!empty($validated['procurement_request_id'])) {
                $procReq = ProcurementRequest::where('id', $validated['procurement_request_id'])
                    ->where('branch_id', $branchId)
                    ->whereIn('status', ['pending', 'budget_pending', 'pending_order_to_supplier', 'awaiting_inventory_confirmation'])
                    ->first();
            } else {
                // Fallback: try to find by product_id for backward compatibility
                $procReq = ProcurementRequest::where('product_id', $product->id)
                    ->where('branch_id', $branchId)
                    ->whereIn('status', ['pending', 'pending_order_to_supplier', 'awaiting_inventory_confirmation'])
                    ->first();
            }

            if (!$procReq) {
                Log::warning('placeOrderProduct SPECIFIC SUPPLIER: no procurement request found', [
                    'product_id' => $product->id,
                    'procurement_request_id' => $validated['procurement_request_id'] ?? null,
                    'branch_id' => $branchId,
                    'searches' => [
                        'by_id' => $validated['procurement_request_id'] ?? 'not_provided',
                        'by_product' => $product->id
                    ]
                ]);

                $message = 'No pending procurement request found';
                if (!empty($validated['procurement_request_id'])) {
                    // Check if request exists at all
                    $anyReq = ProcurementRequest::find($validated['procurement_request_id']);
                    if (!$anyReq) {
                        $message = 'Procurement request not found';
                    } elseif ($anyReq->branch_id != $branchId) {
                        $message = 'Procurement request is in a different branch';
                    } else {
                        $message = "Procurement request status is '{$anyReq->status}', expected one of: pending, budget_pending, pending_order_to_supplier, awaiting_inventory_confirmation";
                    }
                }
                return response()->json(['ok' => false, 'message' => $message, 'debug' => config('app.debug') ? ['request_id' => $validated['procurement_request_id'] ?? null, 'product_id' => $product->id, 'branch_id' => $branchId] : null], 400);
            }

            // Ensure budget approved before ordering
            if (!$procReq->budget_approved) {
                // If status is 'pending', auto-acknowledge first (which creates BudgetRequest for Finance)
                if ($procReq->status === 'pending') {
                    try {
                        DB::transaction(function () use ($procReq, $user) {
                            $procReq->update([
                                'procurement_user_id' => $user->id,
                                'status' => 'budget_pending'
                            ]);

                            // Check if BudgetRequest already exists
                                $existingBudget = BudgetRequest::where('branch_id', $procReq->branch_id)
                                    ->where('purpose', 'LIKE', "%Procurement Request #{$procReq->id}%")
                                    ->first();

                                if (!$existingBudget) {
                                    BudgetRequest::create([
                                        'branch_id' => $procReq->branch_id,
                                        'user_id' => $user->id,
                                        'purpose' => "Procurement Request #{$procReq->id}: {$procReq->product->name} x{$procReq->quantity}",
                                        'requested_amount' => $procReq->total_amount,
                                        'status' => 'Pending',
                                        'date_requested' => now()->toDateString(),
                                    ]);
                                }
                        });
                        $procReq->refresh();
                        Log::info('placeOrderProduct: auto-acknowledged request', ['proc_id' => $procReq->id]);

                        // CRITICAL: Return early and tell user to wait for budget approval
                        // Do NOT continue to place order until budget is actually approved
                        return response()->json([
                            'ok' => false,
                            'message' => 'Request acknowledged! Budget request has been sent to Finance for approval. Please wait for approval before placing the order.',
                            'procurement_request' => $procReq->fresh()->load('product'),
                            'budget_pending' => true
                        ], 202);  // 202 Accepted - acknowledged but waiting for budget approval
                    } catch (\Exception $e) {
                        Log::error('placeOrderProduct: auto-acknowledge failed', ['error' => $e->getMessage()]);
                        return response()->json(['ok' => false, 'message' => 'Failed to acknowledge request: ' . $e->getMessage()], 500);
                    }
                } elseif ($procReq->status === 'budget_pending') {
                    // Budget request was created but not yet approved by Finance
                    Log::info('placeOrderProduct: budget still pending', ['proc_id' => $procReq->id]);
                    return response()->json([
                        'ok' => false,
                        'message' => 'Budget approval is still pending from Finance. Please wait for approval before placing the order.',
                        'procurement_request' => $procReq,
                        'budget_pending' => true
                    ], 202);
                } else {
                    // Status is not pending and budget not approved - can't proceed
                    Log::warning('placeOrderProduct SPECIFIC SUPPLIER: budget not approved', ['proc_id' => $procReq->id, 'status' => $procReq->status]);
                    return response()->json(['ok' => false, 'message' => 'Budget must be approved before ordering'], 400);
                }
            }

            // Use the quantity requested by logistics (cannot be changed by procurement manager)
            $quantity = $procReq->quantity;

            try {
                $supplierOrder = DB::transaction(function () use ($procReq, $supplierId, $quantity, $user, $product) {
                    // FIX: Find the EXISTING SupplierOrder for this supplier + procurement request
                    // (created during broadcast when supplier submitted their product)
                    $existingOrder = SupplierOrder::where('procurement_request_id', $procReq->id)
                        ->where('supplier_id', $supplierId)
                        ->whereNotNull('product_id')  // Only if supplier has confirmed
                        ->first();

                    if ($existingOrder) {
                        // Use the existing order which already has the correct supplier product
                        Log::info('placeOrderProduct SPECIFIC SUPPLIER: using existing SupplierOrder', [
                            'order_id' => $existingOrder->id,
                            'supplier_id' => $supplierId,
                            'product_id' => $existingOrder->product_id,
                            'product_id_submitted_by_supplier' => $existingOrder->product_id
                        ]);

                        // Update status and mark as non-broadcast so supplier can see it
                        $existingOrder->update([
                            'status' => 'pending',
                            'is_broadcast' => 0  // CRITICAL: Mark as specific order, not broadcast
                        ]);
                        $order = $existingOrder;
                    } else {
                        // Fallback: create new order if no existing one found (shouldn't happen in normal flow)
                        Log::warning('placeOrderProduct SPECIFIC SUPPLIER: no existing order found, creating new', [
                            'supplier_id' => $supplierId,
                            'proc_req_id' => $procReq->id
                        ]);

                        $order = SupplierOrder::create([
                            'procurement_request_id' => $procReq->id,
                            'product_id' => $product->id,  // Original product
                            'supplier_id' => $supplierId,
                            'quantity' => $quantity,
                            'status' => 'pending',
                            'is_broadcast' => 0,  // Specific order to one supplier
                            'branch_id' => $procReq->branch_id,
                        ]);
                    }

                    $procReq->update([
                        'procurement_user_id' => $user->id,
                        'status' => 'pending_order_to_supplier',
                        'supplier_confirmed' => false,
                    ]);

                    // Deduct branch budget if applicable
                    try {
                        $branch = Branch::where('id', $procReq->branch_id)->lockForUpdate()->first();
                        $deductAmount = $procReq->budget_amount ?? $procReq->total_amount ?? ($procReq->price * $quantity);
                        if ($branch && $deductAmount) {
                            $branch->budget = is_null($branch->budget) ? 0 : ($branch->budget - (float) $deductAmount);
                            $branch->save();
                        }
                    } catch (\Exception $e) {
                        throw $e;
                    }

                    return $order;
                });
            } catch (\Exception $e) {
                Log::error('Manager placeOrderProduct supplier-order failed', ['error' => $e->getMessage()]);
                return response()->json(['ok' => false, 'message' => 'Failed to place supplier order'], 500);
            }

            return response()->json(['ok' => true, 'message' => 'Supplier order created', 'supplier_order' => $supplierOrder, 'procurement_request' => $procReq->fresh()->load('product')]);
        }

        // If no supplier selected: create a broadcast SupplierOrder so suppliers can confirm
        // Always use the quantity requested by logistics (cannot be changed by procurement manager)

        try {
            $supplierOrder = DB::transaction(function () use ($product, $branchId, $user) {
                Log::info('placeOrderProduct BROADCAST: starting', [
                    'product_id' => $product->id,
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                ]);

                // Try to find the pending procurement request for this product in this branch
                $procReq = ProcurementRequest::where('product_id', $product->id)
                    ->where('branch_id', $branchId)
                    ->whereIn('status', ['pending', 'pending_order_to_supplier'])
                    ->first();

                if (!$procReq) {
                    Log::warning('placeOrderProduct BROADCAST: no procurement request found', [
                        'product_id' => $product->id,
                        'branch_id' => $branchId,
                    ]);
                    throw new \Exception('No pending procurement request found for this product');
                }

                Log::info('placeOrderProduct BROADCAST: found procurement request', [
                    'proc_id' => $procReq->id,
                ]);

                // Check if a broadcast supplier order already exists for this procurement request
                // Prevent duplicate broadcast orders (fix for: 1 product, 2 suppliers showing duplicate entries)
                $existingBroadcast = SupplierOrder::where('procurement_request_id', $procReq->id)
                    ->where('is_broadcast', true)
                    ->first();

                if ($existingBroadcast) {
                    Log::info('placeOrderProduct BROADCAST: broadcast order already exists', [
                        'proc_id' => $procReq->id,
                        'existing_order_id' => $existingBroadcast->id,
                    ]);

                    // Return the existing broadcast order instead of creating a duplicate
                    return $existingBroadcast;
                }

                // Use the quantity requested by logistics (cannot be changed by procurement manager)
                $qty = $procReq->quantity;

                $order = SupplierOrder::create([
                    'procurement_request_id' => $procReq->id,
                    'product_id' => $procReq->product_id,
                    'supplier_id' => null,
                    'quantity' => $qty,
                    'status' => 'pending',
                    'is_broadcast' => 1,
                    'branch_id' => $procReq->branch_id,
                ]);

                Log::info('placeOrderProduct BROADCAST: created supplier order', [
                    'order_id' => $order->id,
                ]);

                $procReq->update([
                    'procurement_user_id' => $user->id,
                    'status' => 'pending_order_to_supplier',
                    'supplier_confirmed' => false,
                ]);

                // Deduct branch budget if applicable
                // Only deduct when creating NEW order (not when returning existing one)
                try {
                    $branch = Branch::where('id', $procReq->branch_id)->lockForUpdate()->first();
                    $deductAmount = $procReq->budget_amount ?? $procReq->total_amount ?? ($procReq->price * $qty);
                    if ($branch && $deductAmount) {
                        $branch->budget = is_null($branch->budget) ? 0 : ($branch->budget - (float) $deductAmount);
                        $branch->save();
                    }
                } catch (\Exception $e) {
                    throw $e;
                }

                return $order;
            });
        } catch (\Exception $e) {
            Log::error('Manager placeOrderProduct broadcast supplier-order failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => $e->getMessage() ?: 'Failed to create broadcast supplier order'], 500);
        }

        // Return the created broadcast order and refreshed procurement request
        $procReq = ProcurementRequest::where('product_id', $product->id)
            ->where('branch_id', $branchId)
            ->where('status', 'pending_order_to_supplier')
            ->first();

        return response()->json(['ok' => true, 'message' => 'Broadcast supplier order created, waiting for supplier confirmation', 'supplier_order' => $supplierOrder, 'procurement_request' => $procReq]);
    }

    /**
     * Create a supplier account (Procurement Manager)
     * POST /api/manager/procurement/suppliers
     */
    public function createProcurementSupplier(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'procurement')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Follow HR create staff params: username, email, fullName, phone, department, password
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'nullable|email|unique:users,email',
            'fullName' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8',
        ]);

        $branchId = $user->branch_id;
        if (!$branchId) {
            return response()->json(['ok' => false, 'message' => 'Manager has no branch assigned'], 400);
        }

        // Use default password if not provided (same as HR flow)
        $password = $validated['password'] ?? 'Chikintayo_123';

        try {
            $supplier = new User();
            $supplier->username = $validated['username'];
            $supplier->email = $validated['email'] ?? null;
            $supplier->password = 'Chikintayo_123';  // Fixed: Use model mutator, default password
            $supplier->full_name = $validated['fullName'];
            $supplier->phone_number = $validated['phone'] ?? '';
            $supplier->role = 'SUPPLIER';
            $supplier->branch_id = $branchId;
            $supplier->is_active = 1;
            $supplier->must_change_password = true;
            $supplier->save();


            // Try to send email with account details if email provided
            if (!empty($supplier->email)) {
                $body = "Hello {$supplier->full_name},\n\n" .
                    "An account has been created for you on CHIKIN TAYO.\n\n" .
                    "Username: {$supplier->username}\n" .
                    "Default Password: {$password}\n\n" .
                    "Please login and change your password as soon as possible.\n\n" .
                    "Regards,\nCHIKIN TAYO Procurement Team";

                try {
                    Mail::raw($body, function ($message) use ($supplier) {
                        $message->to($supplier->email)
                                ->subject('CHIKIN TAYO - Account Details');
                    });
                } catch (\Exception $e) {
                    Log::error('Failed to send supplier account email: ' . $e->getMessage());
                    // continue — account created regardless of email success
                }
            }

            return response()->json([
                'ok' => true,
                'message' => 'Supplier created successfully',
                'supplier' => [
                    'id' => $supplier->id,
                    'username' => $supplier->username,
                    'email' => $supplier->email,
                    'full_name' => $supplier->full_name,
                ]
            ], 201);
        } catch (\Exception $ex) {
            Log::error('createProcurementSupplier error: ' . $ex->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to create supplier account'], 500);
        }
    }

// ==========================================
// Logistics Manager - Inventory Endpoint (for procurement requests)
// ==========================================
/**
 * Return branch inventory for logistics manager with stock status
 * GET /api/manager/logistics/inventory
 */
public function logisticsInventory(Request $request)
{
    Log::debug('=== logisticsInventory DEBUG ===');
    Log::debug('Auth::check(): ' . (Auth::check() ? 'YES' : 'NO'));
    Log::debug('Auth::user(): ' . json_encode(Auth::user() ?? null));
    Log::debug('Raw Bearer token present: ' . ($request->bearerToken() ? 'YES' : 'NO'));

    $user = $this->getAuthenticatedManager($request);
    Log::debug('getAuthenticatedManager result: ' . json_encode($user ?? null));

    if (!$this->allowManagerDept($user, 'logistics')) {
        Log::warning('logisticsInventory 401 BLOCKED', [
            'user' => $user ? $user->only(['id', 'username', 'role', 'department']) : null,
        ]);
        return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
    }
    Log::info('logisticsInventory PASSED auth check');

    $branchId = $user->branch_id;
    if ($this->isMainBranchLogisticsManager($user) && $request->filled('branch_id')) {
        $branchId = (int) $request->input('branch_id');
    }

    if ($branchId && !Branch::where('id', $branchId)->exists()) {
        return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
    }

    if (!$branchId) {
        return response()->json(['ok' => false, 'message' => 'No branch assigned'], 400);
    }

    $allProducts = Product::where('branch_id', $branchId)
        ->where('is_active', 1)
        ->with(['dishIngredients.dish'])
        ->select('id', 'name', 'category', 'price', 'stock', 'min_stock', 'expires_at', 'branch_id', 'is_kitchen_dish')
        ->get()
        ->filter(function ($product) {
            // Filter out products from unapproved kitchen dishes
            // Only show kitchen dish products if the dish has been approved by the owner
            if (!$product->is_kitchen_dish) {
                // Non-kitchen dishes are always shown
                return true;
            }

            // For kitchen dishes, check if all related dishes are approved
            $dishIngredients = $product->dishIngredients;
            if ($dishIngredients->isEmpty()) {
                // No dish associations, show it
                return true;
            }

            // Only show if at least one associated dish is approved
            return $dishIngredients->some(function ($dishIng) {
                $dish = $dishIng->dish;
                return $dish && $dish->approval_status === 'approved';
            });
        });

    // Group products by name to avoid showing duplicates when multiple suppliers submit the same product
    $groupedProducts = [];

    foreach ($allProducts as $product) {
        $productName = $product->name;

        if (!isset($groupedProducts[$productName])) {
            // First product with this name - use it as base
            // Ensure we can convert the item to an array even if it's a stdClass
            if (is_object($product) && method_exists($product, 'toArray')) {
                $base = $product->toArray();
            } else {
                $base = (array) $product;
            }
            $groupedProducts[$productName] = $base;
            $groupedProducts[$productName]['stock'] = (int) $product->stock;
            $groupedProducts[$productName]['min_stock'] = (int) $product->min_stock > 0 ? (int) $product->min_stock : 10;
            $groupedProducts[$productName]['related_products'] = 1; // Track how many products are grouped
        } else {
            // Subsequent products with same name - combine stock and track variants
            $groupedProducts[$productName]['stock'] += (int) $product->stock;
            $groupedProducts[$productName]['related_products'] += 1;

            // Update expiry to the earliest (most urgent)
            $currentExpiry = $groupedProducts[$productName]['expires_at'];
            if ($product->expires_at && (!$currentExpiry || $product->expires_at < $currentExpiry)) {
                $groupedProducts[$productName]['expires_at'] = $product->expires_at;
            }
        }
    }

    // Convert to array and add status
    $products = collect($groupedProducts)->map(function ($p) {
        $status = ($p['stock'] <= $p['min_stock']) ? 'LOW STOCK' : 'OK';
        return array_merge($p, ['status' => $status]);
    })->values();

    return response()->json([
        'ok' => true,
        'data' => $products,
    ]);
}

// ==========================================
// Inventory Manager Profile Endpoints
// ==========================================

    public function inventoryProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'inventory')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
                'branch_id' => $user->branch_id,
                'must_change_password' => (bool) $user->must_change_password,
            ]
        ]);
    }

    public function updateInventoryProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'inventory')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ]
        ]);
    }

    // ==========================================
    // Inventory Manager Profile Endpoints
    // ==========================================

    public function invProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'inventory')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
                'branch_id' => $user->branch_id,
                'must_change_password' => (bool) $user->must_change_password,
            ]
        ]);
    }

    public function updateInvProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'inventory')) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ]
        ]);
    }

    public function invDashboard(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'inventory')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Fetch products for this branch
        $products = Product::where('branch_id', $user->branch_id)->get();
        $totalProducts = $products->count();
        $lowStockItems = $products->where('stock', '>', 0)->where('stock', '<=', function($q) {
            return $q->select('min_stock');
        })->count();
        $outOfStock = $products->where('stock', 0)->count();

        return response()->json([
            'ok' => true,
            'totalProducts' => $totalProducts,
            'lowStockItems' => $lowStockItems,
            'outOfStock' => $outOfStock,
        ]);
    }

    public function invProducts(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'inventory')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Fetch products for this branch
        $products = Product::where('branch_id', $user->branch_id)
            ->where('is_active', true)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'cost_price' => $product->cost_price,
                    'stock' => $product->stock,
                    'min_stock' => $product->min_stock,
                    'is_low_stock' => $product->stock <= $product->min_stock,
                    'is_out_of_stock' => $product->stock == 0,
                    'supplier_name' => $product->supplier_name,
                ];
            });

        return response()->json([
            'ok' => true,
            'products' => $products
        ]);
    }

    public function invReports(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'inventory')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Fetch inventory reports for this branch
        $products = Product::where('branch_id', $user->branch_id)
            ->where('is_active', true)
            ->get();

        $reports = [
            [
                'id' => 1,
                'title' => 'Stock Status Report',
                'type' => 'stock_status',
                'generated_at' => now()->toIso8601String(),
                'summary' => [
                    'total_products' => $products->count(),
                    'low_stock_items' => $products->filter(fn($p) => $p->stock > 0 && $p->stock <= $p->min_stock)->count(),
                    'out_of_stock_items' => $products->filter(fn($p) => $p->stock == 0)->count(),
                    'total_inventory_value' => $products->sum(fn($p) => $p->stock * $p->cost_price),
                ]
            ]
        ];

        return response()->json([
            'ok' => true,
            'reports' => $reports
        ]);
    }

    public function invPendingProcurements(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'inventory')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $requests = ProcurementRequest::with(['product:id,name,sku,price', 'logisticsUser'])
                ->where('branch_id', $user->branch_id)
                ->where('status', 'awaiting_inventory_confirmation')
                ->orderBy('created_at', 'desc')
                ->get();

            $payload = $requests->map(function ($r) {
                return [
                    'id' => $r->id,
                    'procurement_request_id' => $r->id,
                    'product_id' => $r->product_id,
                    'product_name' => $r->product?->name,
                    'quantity' => $r->quantity,
                    'price' => $r->price,
                    'receipt_path' => $r->receipt_path ?? null,
                    'created_at' => $r->created_at,
                ];
            });

            return response()->json($payload);
        } catch (\Exception $e) {
            Log::error('Failed to load pending procurements for manager', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch pending procurements'], 500);
        }
    }

    public function invConfirmedProcurements(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$this->allowManagerDept($user, 'inventory')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $requests = ProcurementRequest::with(['product', 'procurementUser'])
                ->where('branch_id', $user->branch_id)
                ->where('status', 'completed')
                ->orderBy('updated_at', 'desc')
                ->limit(50)
                ->get();

            $payload = $requests->map(function ($r) {
                return [
                    'id' => $r->id,
                    'product_id' => $r->product_id,
                    'product_name' => $r->product?->name,
                    'quantity' => $r->quantity,
                    'confirmed_by' => $r->procurementUser?->full_name ?? $r->procurementUser?->username ?? null,
                    'confirmed_at' => $r->updated_at,
                ];
            });

            return response()->json($payload);
        } catch (\Exception $e) {
            Log::error('Failed to load confirmed procurements for manager', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch confirmed procurements'], 500);
        }
    }
}
