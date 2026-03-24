<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Branch;
use App\Models\Order;
    use Illuminate\Support\Facades\Schema;

class SuperAdminController extends Controller
{
    /**
     * Get authenticated user - same approach as AuthController
     */
    private function resolveAuthenticatedUser($request)
    {
        if (Auth::check()) {
            return Auth::user();
        }

        $sessionUserId = $request->session()->get('user_id');
        if ($sessionUserId) {
            return User::find($sessionUserId);
        }

        return null;
    }

    /**
     * Get super admin profile
     */
    public function profile(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        // Check if user is super admin
        $roleUpper = strtoupper($user->role ?? '');
        // Allow Super Admins and Owners to send announcements
        if (!in_array($roleUpper, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'])) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        // Generate full absolute URL for avatar if it exists
        $avatarUrl = null;
        if ($user->avatar_url) {
            if (strpos($user->avatar_url, 'http') === 0) {
                $avatarUrl = $user->avatar_url;
            } else {
                $avatarUrl = url($user->avatar_url);
            }
        }

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'fullName' => $user->full_name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'contact' => $user->phone_number,
                'accountId' => 'sa' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'avatarUrl' => $avatarUrl,
            ]
        ]);
    }

    /**
     * Update super admin profile
     */
    public function updateProfile(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if ($roleUpper !== 'SUPER_ADMIN' && $roleUpper !== 'SUPERADMIN') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $userId = $user->id;

        // Simple validation - no password validation required
        $validated = $request->validate([
            'fullName' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $userId,
            'email' => 'nullable|email|max:255|unique:users,email,' . $userId,
            'contact' => 'nullable|string|max:20',
        ]);

        $updateData = [];

        if ($request->has('fullName') && !empty($validated['fullName'])) {
            $updateData['full_name'] = $validated['fullName'];
        }
        if ($request->has('username') && !empty($validated['username'])) {
            $updateData['username'] = $validated['username'];
        }
        if ($request->has('email') && !empty($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }
        if ($request->has('contact') && !empty($validated['contact'])) {
            $updateData['phone_number'] = $validated['contact'];
        }

        if (!empty($updateData)) {
            DB::table('users')
                ->where('id', $userId)
                ->update($updateData);
        }

        // Fetch and return updated user data
        $updatedUser = User::find($userId);

        // Generate full absolute URL for avatar if it exists
        $avatarUrl = null;
        if ($updatedUser->avatar_url) {
            if (strpos($updatedUser->avatar_url, 'http') === 0) {
                $avatarUrl = $updatedUser->avatar_url;
            } else {
                $avatarUrl = url($updatedUser->avatar_url);
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $updatedUser->id,
                'fullName' => $updatedUser->full_name,
                'username' => $updatedUser->username,
                'email' => $updatedUser->email,
                'role' => $updatedUser->role,
                'contact' => $updatedUser->phone_number,
                'accountId' => 'sa' . str_pad($updatedUser->id, 4, '0', STR_PAD_LEFT),
                'avatarUrl' => $avatarUrl,
            ]
        ]);
    }

    /**
     * Upload avatar for super admin
     */
    public function uploadAvatar(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if ($roleUpper !== 'SUPER_ADMIN' && $roleUpper !== 'SUPERADMIN') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $file = $request->file('avatar');

        try {
            // Generate a unique filename
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Store in public/storage/avatars
            $path = $file->storeAs('avatars', $filename, 'public');

            // Update user avatar_url
            $storePath = '/storage/' . $path;
            DB::table('users')
                ->where('id', $user->id)
                ->update(['avatar_url' => $storePath]);

            return response()->json([
                'ok' => true,
                'message' => 'Avatar uploaded successfully',
                'avatarUrl' => url($storePath),
            ]);
        } catch (\Exception $ex) {
            return response()->json(['ok' => false, 'message' => 'Failed to upload avatar'], 500);
        }
    }

    /**
     * Get super admin dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if ($roleUpper !== 'SUPER_ADMIN' && $roleUpper !== 'SUPERADMIN') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $range = $request->query('range', 'today');

        // Calculate date range based on selection
        $dateRange = $this->getDateRange($range);

        // Get totals
        $totalBranches = Branch::count();
        $totalEmployees = User::where('role', '!=', 'SUPER_ADMIN')
            ->where('role', '!=', 'SUPERADMIN')
            ->whereNull('deleted_at')
            ->count();
        $totalAdmins = User::where('role', 'ADMIN')
            ->whereNull('deleted_at')
            ->count();

        // Get order stats
        $orders = Order::whereBetween('created_at', $dateRange)->count();
        $completed = Order::whereBetween('created_at', $dateRange)
            ->where('status', 'completed')
            ->count();
        $pending = Order::whereBetween('created_at', $dateRange)
            ->whereIn('status', ['pending', 'in_kitchen'])
            ->count();
        $sales = Order::whereBetween('created_at', $dateRange)
            ->where('status', 'completed')
            ->sum('grand_total');

        // Get branch stats
        $branchStats = Branch::withCount(['users' => function ($query) {
            $query->whereNull('deleted_at');
        }])->get()->map(function ($branch) use ($dateRange) {
            $orders = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', $dateRange)
                ->count();
            $sales = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', $dateRange)
                ->where('status', 'completed')
                ->sum('grand_total');

            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'orders' => $orders,
                'sales' => '₱' . number_format($sales, 2),
                'staff_count' => $branch->users_count,
                'is_active' => $branch->is_active ?? true,
            ];
        });

        // Get top products from actual order data
        $topProducts = \App\Models\OrderItem::select('product_id', 'product_name', DB::raw('SUM(quantity) as total_ordered'))
            ->whereHas('order', function ($query) use ($dateRange) {
                $query->whereBetween('created_at', $dateRange)
                    ->where('status', 'completed');
            })
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_ordered')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product_name ?? 'Unknown Product',
                    'orders' => (int) $item->total_ordered,
                ];
            });

        // Get low stock items from products where stock < min_stock
        $lowStockItems = \App\Models\Product::select('id', 'name', 'stock', 'min_stock')
            ->where('is_active', true)
            ->whereColumn('stock', '<', 'min_stock')
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'stock' => $item->stock,
                ];
            });

        // Get attendance data
        $attendance = \App\Models\Attendance::whereBetween('date', [
            $dateRange[0]->format('Y-m-d'),
            $dateRange[1]->format('Y-m-d')
        ])->with('user', 'branch')->latest()->limit(20)->get()->map(function ($att) {
            return [
                'id' => $att->id,
                'user_name' => $att->user ? $att->user->full_name : 'Unknown',
                'branch_name' => $att->branch ? $att->branch->name : 'N/A',
                'status' => $att->status ?? 'present',
            ];
        });

        // System activity (mock data)
        $systemActivity = [
            ['id' => 1, 'title' => 'System started', 'description' => 'Server initialized', 'status' => 'Normal', 'badgeClass' => 'badge--success'],
            ['id' => 2, 'title' => 'Database backup', 'description' => 'Auto-backup completed', 'status' => 'Success', 'badgeClass' => 'badge--success'],
            ['id' => 3, 'title' => 'API health check', 'description' => 'All endpoints responding', 'status' => 'OK', 'badgeClass' => 'badge--success'],
        ];

        return response()->json([
            'ok' => true,
            'totalBranches' => $totalBranches,
            'totalEmployees' => $totalEmployees,
            'totalAdmins' => $totalAdmins,
            'orders' => $orders,
            'completed' => $completed,
            'pending' => $pending,
            'sales' => '₱' . number_format($sales, 2),
            'branchStats' => $branchStats,
            'topProducts' => $topProducts,
            'lowStockItems' => $lowStockItems,
            'attendance' => $attendance,
            'systemActivity' => $systemActivity,
        ]);
    }

    /**
     * Send announcement to all branches/staff
     * Validates input, saves to database, and returns appropriate response
     */
    public function sendAnnouncement(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            Log::warning('Announcement send attempt without authentication', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        // Allow Super Admins and Owners to send announcements
        if (!in_array($roleUpper, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'])) {
            Log::warning('Unauthorized announcement attempt', [
                'user_id' => $user->id ?? null,
                'role' => $user->role ?? null,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        // Validate incoming request data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target' => 'nullable|in:all,staff,managers',
        ]);

        // Set default target if not provided
        $target = $validated['target'] ?? 'all';

        try {
            // Save announcement to database
            $announcement = \App\Models\Announcement::create([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'target' => $target,
                'sender_id' => $user->id,
            ]);

            // Get count of users who will receive this announcement (for logging purposes)
            $targetCount = $this->getTargetUserCount($target);

            // Log the announcement activity
            Log::info("Announcement sent by user ID {$user->id}: '{$validated['title']}' to target: {$target} ({$targetCount} users)");

            return response()->json([
                'ok' => true,
                'message' => 'Announcement sent successfully!',
                'announcement' => [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'target' => $announcement->target,
                    'created_at' => $announcement->created_at->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send announcement: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'message' => 'Failed to send announcement. Please try again.',
            ], 500);
        }
    }

    /**
     * Get the count of users matching the target audience
     * Used for logging and notification purposes
     */
    private function getTargetUserCount(string $target): int
    {
        $query = User::whereNull('deleted_at')
            ->where('is_active', true);

        switch ($target) {
            case 'managers':
                // Only managers (role = MANAGER)
                return $query->where('role', 'MANAGER')->count();
            case 'staff':
                // Staff members (not MANAGER, not ADMIN, not SUPER_ADMIN)
                return $query->whereNotIn('role', ['MANAGER', 'ADMIN', 'SUPER_ADMIN', 'SUPERADMIN'])->count();
            case 'all':
            default:
                // All active users except super admins
                return $query->whereNotIn('role', ['SUPER_ADMIN', 'SUPERADMIN'])->count();
        }
    }

    /**
     * Update terms and agreement
     */
    public function updateTerms(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if ($roleUpper !== 'SUPER_ADMIN' && $roleUpper !== 'SUPERADMIN') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        // In a real implementation, you would store this in config or database
        // For now, we'll just return success
        return response()->json([
            'ok' => true,
            'message' => 'Terms updated successfully!',
        ]);
    }

    /**
     * Helper function to get date range based on selection
     */
    private function getDateRange($range)
    {
        $now = now();

        switch ($range) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'yesterday':
                return [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()];
            case 'thisWeek':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'thisMonth':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            default:
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
        }
    }

/**
     * Get all staff members for SuperAdmin HR Staff Management
     */
    public function allStaff(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if ($roleUpper !== 'SUPER_ADMIN' && $roleUpper !== 'SUPERADMIN') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        // Get all staff (excluding super admins)
        $staff = User::where('role', '!=', 'SUPER_ADMIN')
            ->where('role', '!=', 'SUPERADMIN')
            ->whereNull('deleted_at')
            ->with('branch')
            ->orderBy('full_name', 'asc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'department' => $user->department,
                    'phone_number' => $user->phone_number,
                    'branch_name' => $user->branch ? $user->branch->name : null,
                    'is_active' => $user->is_active ?? true,
                    'is_online' => false, // Default to false, can be enhanced with online tracking
                    'created_at' => $user->created_at,
                ];
            });

        return response()->json([
            'ok' => true,
            'staff' => $staff,
        ]);
    }

/**
     * Get all products across all branches for SuperAdmin Logistics
     */
    public function logisticsProducts(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if ($roleUpper !== 'SUPER_ADMIN' && $roleUpper !== 'SUPERADMIN') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        // Get all products with branch information (optionally filter by branch_id)
        $branchFilter = $request->query('branch_id');
        $prodQuery = \App\Models\Product::with('branch')->orderBy('name', 'asc');
        if ($branchFilter) {
            $prodQuery->where('branch_id', $branchFilter);
        }

        $products = $prodQuery->get()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'min_stock' => $product->min_stock ?? 10,
                    'branch_id' => $product->branch_id,
                    'branch_name' => $product->branch ? $product->branch->name : 'N/A',
                    'image_url' => $product->image_url,
                    'is_active' => $product->is_active ?? true,
                    'created_at' => $product->created_at,
                ];
            });

        return response()->json($products);
    }

    /**
     * Store a new product (SuperAdmin Logistics)
     */
    public function logisticsStoreProduct(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if ($roleUpper !== 'SUPER_ADMIN' && $roleUpper !== 'SUPERADMIN') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'branch_id' => 'required|exists:branches,id',
        ]);

        // Generate slug from name if not provided
        $slug = \Illuminate\Support\Str::slug($validated['name']);

        // Make slug unique if it already exists
        $originalSlug = $slug;
        $counter = 1;
        while (\App\Models\Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Generate SKU if not provided
        $sku = $validated['sku'] ?? 'SKU-' . strtoupper(\Illuminate\Support\Str::random(6));

        $isDish = \App\Models\Dish::whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($validated['name']))])
            ->where('branch_id', $validated['branch_id'])
            ->exists();

        $product = \App\Models\Product::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'sku' => $sku,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'min_stock' => $validated['min_stock'] ?? 10,
            'branch_id' => $validated['branch_id'],
            'is_active' => true,
            'is_kitchen_dish' => $isDish,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Product created successfully',
            'product' => $product,
        ]);
    }

    /**
     * Update a product (SuperAdmin Logistics)
     */
    public function logisticsUpdateProduct(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if ($roleUpper !== 'SUPER_ADMIN' && $roleUpper !== 'SUPERADMIN') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $product = \App\Models\Product::find($id);
        if (!$product) {
            return response()->json(['ok' => false, 'message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|max:100|unique:products,sku,' . $id,
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
            'branch_id' => 'sometimes|exists:branches,id',
        ]);

        $product->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }

/**
     * Delete a product (SuperAdmin Logistics)
     */
    public function logisticsDestroyProduct(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if ($roleUpper !== 'SUPER_ADMIN' && $roleUpper !== 'SUPERADMIN') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $product = \App\Models\Product::find($id);
        if (!$product) {
            return response()->json(['ok' => false, 'message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Get all branches for SuperAdmin
     */
    public function logisticsBranches(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if ($roleUpper !== 'SUPER_ADMIN' && $roleUpper !== 'SUPERADMIN') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branches = \App\Models\Branch::orderBy('name', 'asc')->get();

        return response()->json($branches);
    }

    /**
     * Get all branches with their default Admin, HR Manager, Finance Manager, and Logistics Manager accounts.
     */
    public function branchesWithAccounts(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if (!in_array($roleUpper, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'])) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branches = Branch::orderBy('name', 'asc')->get();

        $result = $branches->map(function ($branch) {
            $adminUser = User::where('branch_id', $branch->id)
                ->where('role', 'ADMIN')
                ->whereNull('deleted_at')
                ->first();

            $hrManager = User::where('branch_id', $branch->id)
                ->where('role', 'MANAGER')
                ->where('department', 'HR')
                ->whereNull('deleted_at')
                ->first();

            $financeManager = User::where('branch_id', $branch->id)
                ->where('role', 'MANAGER')
                ->where('department', 'FINANCE')
                ->whereNull('deleted_at')
                ->first();

            $procurementManager = User::where('branch_id', $branch->id)
                ->where('role', 'MANAGER')
                ->where('department', 'PROCUREMENT')
                ->whereNull('deleted_at')
                ->first();

            $logisticsManager = User::where('branch_id', $branch->id)
                ->where('role', 'MANAGER')
                ->where('department', 'Logistics')
                ->whereNull('deleted_at')
                ->first();

            $staffCount = User::where('branch_id', $branch->id)
                ->whereNull('deleted_at')
                ->count();

            return [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'address' => $branch->address,
                'budget' => isset($branch->budget) ? (int) $branch->budget : 0,
                'is_active' => (bool) $branch->is_active,
                'staff_count' => $staffCount,
                'admin_user' => $adminUser ? [
                    'id' => $adminUser->id,
                    'username' => $adminUser->username,
                    'email' => $adminUser->email,
                    'is_active' => (bool) $adminUser->is_active,
                ] : null,
                'hr_manager' => $hrManager ? [
                    'id' => $hrManager->id,
                    'username' => $hrManager->username,
                    'email' => $hrManager->email,
                    'is_active' => (bool) $hrManager->is_active,
                ] : null,
                'finance_manager' => $financeManager ? [
                    'id' => $financeManager->id,
                    'username' => $financeManager->username,
                    'email' => $financeManager->email,
                    'is_active' => (bool) $financeManager->is_active,
                ] : null,
                'procurement_manager' => $procurementManager ? [
                    'id' => $procurementManager->id,
                    'username' => $procurementManager->username,
                    'email' => $procurementManager->email,
                    'is_active' => (bool) $procurementManager->is_active,
                ] : null,
                'logistics_manager' => $logisticsManager ? [
                    'id' => $logisticsManager->id,
                    'username' => $logisticsManager->username,
                    'email' => $logisticsManager->email,
                    'is_active' => (bool) $logisticsManager->is_active,
                ] : null,
            ];
        });

        return response()->json(['ok' => true, 'branches' => $result]);
    }

    /**
     * Create a new branch with default Admin, HR Manager, Finance Manager, and Logistics Manager accounts.
     */
    public function storeBranch(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if (!in_array($roleUpper, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'])) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'code' => 'nullable|string|max:20',
            'name' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
        ]);

        $code = $request->input('code');
        $name = $request->input('name');
        $address = $request->input('address');

        $defaultPassword = config('chikintayo.default_password', 'Chikintayo_123');

        // If no code provided, auto-generate from name. Ensure uniqueness.
        if (empty($code)) {
            $base = preg_replace('/[^A-Za-z0-9]/', '', strtolower($name));
            $base = substr($base, 0, 8);
            if (empty($base)) {
                $base = 'br' . substr(time(), -6);
            }
            $candidate = strtoupper($base);
            $suffix = 0;
            while (DB::table('branches')->where('code', $candidate)->exists()) {
                $suffix++;
                $candidate = strtoupper($base) . '_' . $suffix;
                if ($suffix > 999) break;
            }
            $code = $candidate;
        }

        $codeSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $code));

        DB::beginTransaction();
        try {
            // Create the branch
            $branch = Branch::create([
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'is_active' => 1,
                // Use provided budget or default to 100000
                'budget' => (int) ($request->input('budget', 100000)),
            ]);

            // Create default ADMIN account for this branch (no email; user will add/verify later)
            $adminUsername = 'admin_' . $codeSlug;

            // Check if username already exists
            if (User::where('username', $adminUsername)->exists()) {
                $adminUsername = 'admin_' . $codeSlug . '_' . $branch->id;
            }

            User::create([
                'username' => $adminUsername,
                'email' => null,
                'password' => $defaultPassword, // Mutator will hash this automatically
                'full_name' => 'Admin - ' . $name,
                'role' => 'ADMIN',
                'department' => null,
                'branch_id' => $branch->id,
                'is_active' => 1,
                'must_change_password' => 1,
            ]);

            // Create default HR Manager account for this branch
            $hrUsername = 'hr_' . $codeSlug;

            if (User::where('username', $hrUsername)->exists()) {
                $hrUsername = 'hr_' . $codeSlug . '_' . $branch->id;
            }

            User::create([
                'username' => $hrUsername,
                'email' => null,
                'password' => $defaultPassword, // Mutator will hash this automatically
                'full_name' => 'HR Manager - ' . $name,
                'role' => 'MANAGER',
                'department' => 'HR',
                'branch_id' => $branch->id,
                'is_active' => 1,
                'must_change_password' => 1,
            ]);

            // Create default Finance Manager account for this branch
            $financeUsername = 'finance_' . $codeSlug;

            if (User::where('username', $financeUsername)->exists()) {
                $financeUsername = 'finance_' . $codeSlug . '_' . $branch->id;
            }

            User::create([
                'username' => $financeUsername,
                'email' => null,
                'password' => $defaultPassword, // Mutator will hash this automatically
                'full_name' => 'Finance Manager - ' . $name,
                'role' => 'MANAGER',
                'department' => 'Finance',
                'branch_id' => $branch->id,
                'is_active' => 1,
                'must_change_password' => 1,
            ]);

            // Create default Procurement Manager account for this branch
            $procurementUsername = 'procurement_' . $codeSlug;

            if (User::where('username', $procurementUsername)->exists()) {
                $procurementUsername = 'procurement_' . $codeSlug . '_' . $branch->id;
            }

            User::create([
                'username' => $procurementUsername,
                'email' => null,
                'password' => $defaultPassword, // Mutator will hash this automatically
                'full_name' => 'Procurement Manager - ' . $name,
                'role' => 'MANAGER',
                'department' => 'PROCUREMENT',
                'branch_id' => $branch->id,
                'is_active' => 1,
                'must_change_password' => 1,
            ]);

            // Create default Logistics Manager account for this branch
            $logisticsUsername = 'logistics_' . $codeSlug;

            if (User::where('username', $logisticsUsername)->exists()) {
                $logisticsUsername = 'logistics_' . $codeSlug . '_' . $branch->id;
            }

            User::create([
                'username' => $logisticsUsername,
                'email' => null,
                'password' => $defaultPassword, // Mutator will hash this automatically
                'full_name' => 'Logistics Manager - ' . $name,
                'role' => 'MANAGER',
                'department' => 'Logistics',
                'branch_id' => $branch->id,
                'is_active' => 1,
                'must_change_password' => 1,
            ]);

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => "Branch '{$name}' created with default Admin, HR Manager, Finance Manager, and Logistics Manager accounts.",
                'branch_id' => $branch->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('storeBranch error: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'message' => 'Failed to create branch: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a branch and associated user accounts (soft-delete).
     */
    public function deleteBranch(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if (!in_array($roleUpper, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'])) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branch = Branch::find($id);
        if (!$branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }

        DB::beginTransaction();
        try {
            // Collect all user IDs belonging to this branch (including soft-deleted)
            $userIds = User::withTrashed()->where('branch_id', $branch->id)->pluck('id')->toArray();

            // First, delete records tied to the branch to avoid FK constraint errors
            $branchTables = [
                'orders',
                'procurement_requests',
                'purchase_requests',
                'purchase_orders',
                'settlements',
                'expenses',
                'supplier_orders',
                'products',
                'attendance',
                'attendance_settings',
                'budget_requests',
                'staff_documents',
                'messages',
                'announcements',
                'customer_accounts'
            ];

            foreach ($branchTables as $table) {
                try {
                    if (Schema::hasColumn($table, 'branch_id')) {
                        DB::table($table)->where('branch_id', $branch->id)->delete();
                        continue;
                    }

                    // Some tables (like purchase_orders) don't have branch_id but can be linked
                    if ($table === 'purchase_orders') {
                        if (Schema::hasColumn('purchase_orders', 'purchase_request_id') && Schema::hasColumn('purchase_requests', 'branch_id')) {
                            $prIds = DB::table('purchase_requests')->where('branch_id', $branch->id)->pluck('id')->toArray();
                            if (!empty($prIds)) {
                                DB::table('purchase_orders')->whereIn('purchase_request_id', $prIds)->delete();
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Log and continue — we don't want missing columns to abort the whole delete
                    Log::warning("deleteBranch: skipping table {$table} cleanup: " . $e->getMessage());
                    continue;
                }
            }

            // Delete orders that reference these users in approval fields (if any remain)
            if (!empty($userIds)) {
                DB::table('orders')->whereIn('approved_by', $userIds)->delete();
            }

            // Delete records that reference users by user-specific columns
            if (!empty($userIds)) {
                if (Schema::hasColumn('messages', 'from_user_id') && Schema::hasColumn('messages', 'to_user_id')) {
                    DB::table('messages')->whereIn('from_user_id', $userIds)->orWhereIn('to_user_id', $userIds)->delete();
                }

                if (Schema::hasColumn('settlements', 'processed_by')) {
                    DB::table('settlements')->whereIn('processed_by', $userIds)->delete();
                }

                if (Schema::hasColumn('purchase_requests', 'requester_id')) {
                    DB::table('purchase_requests')->whereIn('requester_id', $userIds)->delete();
                }

                if (Schema::hasColumn('procurement_requests', 'logistics_user_id') || Schema::hasColumn('procurement_requests', 'procurement_user_id') || Schema::hasColumn('procurement_requests', 'finance_user_id')) {
                    $q = DB::table('procurement_requests');
                    if (Schema::hasColumn('procurement_requests', 'logistics_user_id')) $q->whereIn('logistics_user_id', $userIds);
                    if (Schema::hasColumn('procurement_requests', 'procurement_user_id')) $q->orWhereIn('procurement_user_id', $userIds);
                    if (Schema::hasColumn('procurement_requests', 'finance_user_id')) $q->orWhereIn('finance_user_id', $userIds);
                    $q->delete();
                }

                if (Schema::hasColumn('expenses', 'created_by')) {
                    DB::table('expenses')->whereIn('created_by', $userIds)->delete();
                }

                if (Schema::hasColumn('announcements', 'sender_id')) {
                    DB::table('announcements')->whereIn('sender_id', $userIds)->delete();
                }

                if (Schema::hasColumn('attendance', 'user_id')) {
                    DB::table('attendance')->whereIn('user_id', $userIds)->delete();
                }

                if (Schema::hasColumn('staff_documents', 'user_id')) {
                    DB::table('staff_documents')->whereIn('user_id', $userIds)->delete();
                }

                if (Schema::hasColumn('product_comments', 'user_id')) {
                    DB::table('product_comments')->whereIn('user_id', $userIds)->delete();
                }

                if (Schema::hasColumn('customer_accounts', 'user_id')) {
                    DB::table('customer_accounts')->whereIn('user_id', $userIds)->delete();
                }

                if (Schema::hasColumn('purchase_orders', 'supplier_id')) {
                    DB::table('purchase_orders')->whereIn('supplier_id', $userIds)->delete();
                }
            }

            // Permanently delete all users belonging to this branch
            User::withTrashed()->where('branch_id', $branch->id)->forceDelete();

            // Permanently delete the branch itself
            $branch->forceDelete();

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => "Branch '{$branch->name}' and all connected data were permanently deleted.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('deleteBranch error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to delete branch: ' . $e->getMessage()], 500);
        }
    }
}

