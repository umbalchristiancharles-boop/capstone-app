<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ManagerProfileController extends Controller
{
    /**
     * Get the authenticated manager's profile
     * Uses auth() helper which works with 'auth' middleware
     */
    private function getAuthenticatedManager(Request $request)
    {
        // Use auth() helper - works with 'auth' middleware (web guard)
        if (Auth::check()) {
            return Auth::user();
        }
        
        // Fallback: try to get user from session
        $userId = $request->session()->get('user_id');
        if ($userId) {
            return User::find($userId);
        }
        
        return null;
    }

    /**
     * Check if user is a manager
     */
    private function isManager($user)
    {
        if (!$user) {
            return false;
        }
        
        $role = strtoupper($user->role ?? '');
        
        // Allow MANAGER, MANAGER_HR, BRANCH_MANAGER roles
        return in_array($role, ['MANAGER', 'MANAGER_HR', 'BRANCH_MANAGER']);
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

    // ==========================================
    // HR Manager Profile Endpoints
    // ==========================================
    
    public function hrProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);
        
        if (!$user || !$this->isManager($user)) {
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
        
        if (!$user || !$this->isManager($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get HR-specific dashboard data - filtered by branch
        $branchId = $user->branch_id;
        
        $totalStaff = User::where('role', 'STAFF')
            ->where('branch_id', $branchId)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->count();

        $activeStaff = $totalStaff; // Could be enhanced with attendance data

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
        
        if (!$user || !$this->isManager($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Filter by branch_id - get all staff in the branch (excluding OWNER and SUPER_ADMIN)
        $branchId = $user->branch_id;
        
        $staff = User::where('branch_id', $branchId)
            ->whereNotIn('role', ['OWNER', 'SUPER_ADMIN'])
            ->whereNull('deleted_at')
            ->orderBy('full_name', 'asc')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'username' => $s->username,
                    'full_name' => $s->full_name,
                    'email' => $s->email,
                    'phone_number' => $s->phone_number,
                    'role' => $s->role,
                    'department' => $s->department,
                    'is_active' => $s->is_active,
                    'branch_id' => $s->branch_id,
                    'created_at' => $s->created_at,
                ];
            });

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
        
        if (!$user || !$this->isManager($user)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'nullable|email|unique:users,email',
            'fullName' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8',
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

        $staff = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'full_name' => $validated['fullName'],
            'phone_number' => $validated['phone'] ?? '',
            'department' => $validated['department'] ?? 'Staff',
            'role' => 'STAFF',
            'branch_id' => $branchId,
            'is_active' => 1,
            'must_change_password' => true,
        ]);

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
        
        if (!$user || !$this->isManager($user)) {
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
        
        if (!$user || !$this->isManager($user)) {
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
        
        if (!$user || !$this->isManager($user)) {
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
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'finance')) {
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
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'finance')) {
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

    public function financeDashboard(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'finance')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'ok' => true,
            'totalRevenue' => 0,
            'totalExpenses' => 0,
            'netProfit' => 0,
        ]);
    }

    public function financeReports(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'finance')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'ok' => true,
            'reports' => []
        ]);
    }

    public function financeTransactions(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'finance')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'ok' => true,
            'transactions' => []
        ]);
    }

    // ==========================================
    // Logistics Manager Profile Endpoints
    // ==========================================
    
    public function logisticsProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'logistics')) {
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

    public function updateLogisticsProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'logistics')) {
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
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'logistics')) {
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
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'logistics')) {
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
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'logistics')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'ok' => true,
            'suppliers' => []
        ]);
    }

    // ==========================================
    // Procurement Manager Profile Endpoints
    // ==========================================
    public function procurementProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'procurement')) {
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

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'procurement')) {
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

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'procurement')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $user->branch_id;

        $totalStaff = User::where('role', 'STAFF')
            ->where('branch_id', $branchId)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->count();

        $activeStaff = $totalStaff;

        return response()->json([
            'ok' => true,
            'totalStaff' => $totalStaff,
            'activeStaff' => $activeStaff,
            'pendingRequests' => 0,
        ]);
    }

    // ==========================================
    // Inventory Manager Profile Endpoints
    // ==========================================
    
    public function inventoryProfile(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'inventory')) {
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
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'inventory')) {
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

    public function inventoryDashboard(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'inventory')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'ok' => true,
            'totalProducts' => 0,
            'lowStockItems' => 0,
            'outOfStock' => 0,
        ]);
    }

    public function inventoryProducts(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'inventory')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'ok' => true,
            'products' => []
        ]);
    }

    public function inventoryReports(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);
        
        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'inventory')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'ok' => true,
            'reports' => []
        ]);
    }
}
