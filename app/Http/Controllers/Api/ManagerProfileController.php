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

    /**
     * Return products for logistics manager (same as procurementProducts but for logistics dept)
     * GET /api/manager/logistics/products
     */
    public function logisticsProducts(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'logistics')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $user->branch_id;

        if (!$branchId) {
            return response()->json(['ok' => false, 'message' => 'Manager has no branch assigned'], 400);
        }

        $products = Product::where('branch_id', $branchId)
            ->select('id', 'name', 'slug', 'price', 'stock', 'sku', 'branch_id', 'supplier_name', 'is_published', 'created_at', 'updated_at')
            ->orderBy('name', 'asc')
            ->get();

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

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'procurement')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $user->branch_id;

        if (!$branchId) {
            return response()->json(['ok' => false, 'message' => 'Manager has no branch assigned'], 400);
        }

        // Fetch products that belong to the manager's branch. This returns products
        // supplied/registered under that branch (including supplier-added products).
        $products = Product::where('branch_id', $branchId)
            ->select('id', 'name', 'slug', 'price', 'stock', 'sku', 'branch_id', 'supplier_name', 'is_published', 'created_at', 'updated_at')
            ->orderBy('name', 'asc')
            ->get();

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

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'procurement')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $user->branch_id;

        $product = Product::where('id', $id)->where('branch_id', $branchId)->first();
        if (!$product) {
            return response()->json(['ok' => false, 'message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'quantity' => 'nullable|integer|min:0'
        ]);

        if (isset($validated['quantity'])) {
            $product->stock = max(0, $product->stock + (int)$validated['quantity']);
        }

        $product->is_published = 1;
        $product->save();

        return response()->json(['ok' => true, 'message' => 'Product placed into inventory', 'product' => $product]);
    }

    /**
     * Create a supplier account (Procurement Manager)
     * POST /api/manager/procurement/suppliers
     */
    public function createProcurementSupplier(Request $request)
    {
        $user = $this->getAuthenticatedManager($request);

        if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'procurement')) {
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
    $user = $this->getAuthenticatedManager($request);

    if (!$user || !$this->isManager($user) || !$this->hasDepartmentAccess($user, 'logistics')) {
        return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
    }

    $branchId = $user->branch_id;
    if (!$branchId) {
        return response()->json(['ok' => false, 'message' => 'No branch assigned'], 400);
    }

    $products = Product::where('branch_id', $branchId)
        ->select('id', 'name', 'price', 'stock', 'min_stock')
        ->get()
        ->map(function ($p) {
            $status = ($p->stock <= ($p->min_stock ?? 10)) ? 'LOW STOCK' : 'OK';
            $p->status = $status;
            return $p;
        });

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
