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
use App\Models\Position;
use App\Models\PositionOpenRequest;
use App\Models\StaffDocument;
use Illuminate\Support\Facades\Schema;
use App\Support\Permission;
use App\Services\BranchPasswordService;

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

        // Try Sanctum guard (token-based) if available
        try {
            $sanctumUser = auth('sanctum')->user();
            if ($sanctumUser) return $sanctumUser;
        } catch (\Throwable $e) {}

        // Try Personal Access Token lookup (plain bearer token)
        try {
            $bearer = $request->bearerToken();
            if ($bearer && class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
                $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($bearer);
                if ($tokenModel && $tokenModel->tokenable) return $tokenModel->tokenable;
            }
        } catch (\Throwable $e) {}

        $sessionUserId = $request->session()->get('user_id');
        if ($sessionUserId) {
            return User::find($sessionUserId);
        }

        return null;
    }

    /**
     * Create a default branch-role position record that can be applied to by candidates.
     * This mirrors the HR flow where accounts are created only after a successful application.
     */
    private function createApprovedBranchPositionRequest(Branch $branch, string $roleKey, int $requestedByUserId): array
    {
        $positionMap = [
            'admin' => ['name' => 'Admin', 'department' => 'ADMIN', 'description' => 'Branch administrator role'],
            'hr' => ['name' => 'HR Manager', 'department' => 'HR', 'description' => 'Manages human resources and staff affairs'],
            'finance' => ['name' => 'Finance Manager', 'department' => 'FINANCE', 'description' => 'Oversees financial operations and budgets'],
            'procurement' => ['name' => 'Procurement Manager', 'department' => 'PROCUREMENT', 'description' => 'Handles purchasing and supplier relations'],
            'logistics' => ['name' => 'Logistics Manager', 'department' => 'LOGISTICS', 'description' => 'Manages deliveries and logistics operations'],
        ];

        $config = $positionMap[$roleKey] ?? ['name' => ucfirst($roleKey), 'department' => strtoupper($roleKey), 'description' => 'Branch role'];

        $position = Position::query()->firstOrCreate(
            ['name' => $config['name']],
            [
                'description' => $config['description'],
                'department' => $config['department'],
                'is_active' => 1,
            ]
        );

        $request = PositionOpenRequest::create([
            'position_id' => $position->id,
            'branch_id' => $branch->id,
            'requested_by_user_id' => $requestedByUserId,
            'quantity' => 1,
            'notes' => 'Automatically broadcast during branch creation for ' . $branch->name . '.',
            'status' => 'Approved',
            'approved_by_user_id' => $requestedByUserId,
            'approved_at' => now(),
        ]);

        return [
            'position_name' => $position->name,
            'request_id' => $request->id,
        ];
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

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin']);
        if (! $allowed) {
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

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN'], ['admin']);
        if (! $allowed) {
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

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN'], ['admin']);
        if (! $allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $file = $request->file('avatar');

        try {
            // Generate a unique filename
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Create avatars directory if not exists
            $avatarsDir = public_path('avatars');
            if (!is_dir($avatarsDir)) {
                mkdir($avatarsDir, 0755, true);
            }

            // Store directly in public/avatars (avoids symlink issues on shared hosting)
            $avatarPath = $avatarsDir . DIRECTORY_SEPARATOR . $filename;
            $file->move($avatarsDir, $filename);

            // Update user avatar_url - use direct public path
            $storePath = '/avatars/' . $filename;
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

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN'], ['admin']);
        if (! $allowed) {
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
        ])->with(['user', 'user.branch'])->latest()->limit(20)->get()->map(function ($att) {
            return [
                'id' => $att->id,
                'user_name' => $att->user ? $att->user->full_name : 'Unknown',
                'branch_name' => $att->user && $att->user->branch ? $att->user->branch->name : 'N/A',
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
     * Return supplier orders across branches for SuperAdmin logistics views.
     * GET /api/superadmin/logistics/supplier-orders
     */
    public function logisticsSupplierOrders(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN'], ['admin']);
        if (! $allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $branchId = $request->query('branch_id');

            $query = \App\Models\SupplierOrder::with(['product', 'procurementRequest.logisticsUser', 'branch', 'supplier'])
                ->orderBy('created_at', 'desc');

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            $perPage = intval($request->query('per_page', 50));
            $orders = $query->paginate($perPage);

            return response()->json($orders);
        } catch (\Exception $e) {
            Log::error('logisticsSupplierOrders failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
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

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin']);
        if (! $allowed) {
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
            // Super admin-created products should NOT be visible in cashier
            // until a branch Admin publishes them. Keep unpublished by default.
            'is_published' => false,
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
        try {
            $user = $this->resolveAuthenticatedUser($request);

            if (!$user) {
                return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
            }

            $roleUpper = strtoupper($user->role ?? '');
            $isMainBranchAdmin = false;
            if ($roleUpper === 'ADMIN') {
                $b = Branch::find($user->branch_id);
                $isMainBranchAdmin = (bool) ($b && ($b->is_main_branch ?? false));
            }
            $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin', 'admin.branches']);
            if (! $allowed && ! $isMainBranchAdmin) {
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
                    'is_main_branch' => (bool) ($branch->is_main_branch ?? false),
                    'approval_status' => $branch->approval_status ?? 'approved',
                    'requested_by' => $branch->requested_by,
                    'approved_at' => $branch->approved_at,
                    'rejected_at' => $branch->rejected_at,
                    'can_delete' => !((bool) ($branch->is_main_branch ?? false)),
                    'staff_count' => $staffCount,
                    'default_password' => BranchPasswordService::getCurrentDefaultPassword($branch),
                    'default_password_updated_at' => $branch->default_password_updated_at,
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
        } catch (\Exception $e) {
            Log::error('branchesWithAccounts failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['ok' => false, 'message' => 'Failed to load branches'], 500);
        }
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
        $isMainBranchAdmin = false;
        if ($roleUpper === 'ADMIN') {
            $b = Branch::find($user->branch_id);
            $isMainBranchAdmin = (bool) ($b && ($b->is_main_branch ?? false));
        }
        $isMainBranchFinance = false;
        if ($roleUpper === 'MANAGER' && strtoupper($user->department ?? '') === 'FINANCE') {
            $b = Branch::find($user->branch_id);
            $isMainBranchFinance = (bool) ($b && ($b->is_main_branch ?? false));
        }
        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin', 'admin.branches']);
        if (! $allowed && ! $isMainBranchAdmin) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $requiresOwnerApproval = $isMainBranchAdmin && ! $allowed;
        $requiresFinanceApproval = $isMainBranchAdmin && ! $allowed;
        $approvalStatus = $requiresFinanceApproval ? 'pending_finance' : 'approved';
        $branchIsActive = $requiresFinanceApproval ? 0 : 1;
        $accountIsActive = $requiresFinanceApproval ? 0 : 1;

        $request->validate([
            'code' => 'nullable|string|max:20',
            'name' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'budget' => 'nullable|integer|min:100000|max:1000000',
            'square_meters' => 'nullable|numeric|min:0',
            'geofencing_radius' => 'nullable|numeric|min:0',
            'permit_bills' => 'nullable|array',
            'permit_bills.*.type' => 'nullable|string|max:100',
            'permit_bills.*.amount' => 'nullable|numeric|min:0',
            'construction_costs' => 'nullable|array',
            'construction_costs.*.category' => 'nullable|string|max:100',
            'construction_costs.*.amount' => 'nullable|numeric|min:0',
            'equipment_costs' => 'nullable|array',
            'equipment_costs.*.name' => 'nullable|string|max:150',
            'equipment_costs.*.type' => 'nullable|string|max:100',
            'equipment_costs.*.quantity' => 'nullable|integer|min:1',
            'equipment_costs.*.unit_cost' => 'nullable|numeric|min:0',
            'total_investment' => 'nullable|numeric|min:0',
            'accounts' => 'nullable|array',
            'accounts.admin' => 'nullable|boolean',
            'accounts.hr' => 'nullable|boolean',
            'accounts.finance' => 'nullable|boolean',
            'accounts.procurement' => 'nullable|boolean',
            'accounts.logistics' => 'nullable|boolean',
            'custom_account' => 'nullable|array',
            'custom_account.username' => 'nullable|string|max:60',
            'custom_account.password' => 'nullable|string|min:6|max:100',
            'custom_account.full_name' => 'nullable|string|max:120',
            'custom_account.modules' => 'nullable|array',
            'custom_account.modules.*' => 'string',
            'custom_account.functions' => 'nullable|array',
            'custom_account.functions.*' => 'string',
        ]);

        $code = $request->input('code');
        $name = $request->input('name');
        $address = $request->input('address');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $requestedBudget = (int) ($request->input('budget', 100000));
        $squareMeters = $request->input('square_meters');
        $geofencingRadius = $request->input('geofencing_radius');
        
        // Cost details
        $permitBills = $request->input('permit_bills', []);
        $constructionCosts = $request->input('construction_costs', []);
        $equipmentCosts = $request->input('equipment_costs', []);
        $totalInvestment = $request->input('total_investment');

        $defaultPassword = config('chikintayo.default_password', 'Chikintayo_123');

        // Allowed permission catalog for custom accounts
        // Permission catalog (keep in sync with frontend templates)
        $allowedModules = [
            'admin', 'finance', 'logistics', 'inventory', 'procurement', 'kitchen', 'cashier', 'hr', 'reports',
        ];
        $allowedFunctions = [
            // Admin
            'admin.users', 'admin.branches', 'admin.settings',
            // Finance
            'finance.dashboard', 'finance.budget', 'finance.reports', 'finance.expenses',
            // Logistics
            'logistics.dispatch', 'logistics.receiving', 'logistics.transfers',
            // Inventory
            'inventory.products', 'inventory.counts', 'inventory.adjustments',
            // Procurement
            'procurement.purchase_orders', 'procurement.suppliers', 'procurement.approvals',
            // Kitchen
            'kitchen.orders', 'kitchen.production', 'kitchen.waste',
            // Cashier
            'cashier.pos', 'cashier.refunds', 'cashier.shifts',
            // HR
            'hr.attendance', 'hr.scheduling', 'hr.payroll',
            // Reports
            'reports.sales', 'reports.inventory', 'reports.finance',
        ];

        $accountsInput = is_array($request->input('accounts')) ? $request->input('accounts') : [];
        $selectedAccounts = [
            'admin' => true,
            'hr' => array_key_exists('hr', $accountsInput) ? filter_var($accountsInput['hr'], FILTER_VALIDATE_BOOLEAN) : true,
            'finance' => array_key_exists('finance', $accountsInput) ? filter_var($accountsInput['finance'], FILTER_VALIDATE_BOOLEAN) : true,
            'procurement' => array_key_exists('procurement', $accountsInput) ? filter_var($accountsInput['procurement'], FILTER_VALIDATE_BOOLEAN) : true,
            'logistics' => array_key_exists('logistics', $accountsInput) ? filter_var($accountsInput['logistics'], FILTER_VALIDATE_BOOLEAN) : true,
        ];

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
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_active' => $branchIsActive,
                'is_main_branch' => 0,
                'approval_status' => $approvalStatus,
                'requested_by' => $user->id,
                'finance_confirmed_by' => null,
                'finance_confirmed_at' => null,
                'approved_by' => $requiresOwnerApproval ? null : $user->id,
                'approved_at' => $requiresOwnerApproval ? null : now(),
                'rejected_at' => null,
                // Use provided budget or default to 100000
                'budget' => $requestedBudget,
                'square_meters' => $squareMeters,
                'geofencing_radius' => $geofencingRadius,
                // Cost details
                'permit_bills' => !empty($permitBills) ? $permitBills : null,
                'construction_costs' => !empty($constructionCosts) ? $constructionCosts : null,
                'equipment_costs' => !empty($equipmentCosts) ? $equipmentCosts : null,
                'total_investment' => $totalInvestment,
            ]);

            $createdRoles = [];

            // Instead of creating real user accounts immediately, broadcast approved job openings
            // so the HR application flow creates accounts only after a successful application.
            foreach (['admin', 'hr', 'finance', 'procurement', 'logistics'] as $roleKey) {
                if (!empty($selectedAccounts[$roleKey])) {
                    $broadcast = $this->createApprovedBranchPositionRequest($branch, $roleKey, (int) $user->id);
                    $createdRoles[] = $broadcast['position_name'];
                }
            }

            // Optionally create a custom account with granular module/function access
            $customAccountInput = $request->input('custom_account', null);
            if (is_array($customAccountInput)) {
                $rawModules = array_filter($customAccountInput['modules'] ?? [], fn ($m) => in_array(strtolower($m), $allowedModules, true));
                $rawFunctions = array_filter($customAccountInput['functions'] ?? [], fn ($f) => in_array(strtolower($f), array_map('strtolower', $allowedFunctions), true));

                $modules = array_values(array_unique(array_map('strtolower', $rawModules)));
                $functions = array_values(array_unique(array_map('strtolower', $rawFunctions)));

                // Only create if there is at least one permission selected
                if (!empty($modules) || !empty($functions)) {
                    $customUsername = trim($customAccountInput['username'] ?? '');
                    if (empty($customUsername)) {
                        $customUsername = 'custom_' . $codeSlug;
                        if (User::where('username', $customUsername)->exists()) {
                            $customUsername = 'custom_' . $codeSlug . '_' . $branch->id;
                        }
                    }

                    $customPassword = trim($customAccountInput['password'] ?? '') ?: $defaultPassword;
                    $customFullName = trim($customAccountInput['full_name'] ?? '') ?: ('Custom Account - ' . $name);

                    User::create([
                        'username' => $customUsername,
                        'email' => null,
                        'password' => $customPassword, // Mutator hashes
                        'full_name' => $customFullName,
                        'role' => 'CUSTOM',
                        'department' => null,
                        'branch_id' => $branch->id,
                        'is_active' => $accountIsActive,
                        'must_change_password' => 1,
                        'permissions' => [
                            'modules' => $modules,
                            'functions' => $functions,
                        ],
                    ]);

                    $createdRoles[] = 'Custom Account';
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => $requiresFinanceApproval
                    ? "Branch '{$name}' created and awaiting finance confirmation."
                    : "Branch '{$name}' created" . (!empty($createdRoles) ? ' with ' . implode(', ', $createdRoles) . ' account' . (count($createdRoles) === 1 ? '' : 's') . '.' : '.'),
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
     * List pending branch creation requests for owner approval.
     */
    public function pendingBranchRequests(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'], ['admin']);
        if (! $allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branches = Branch::where('approval_status', 'pending_owner')
            ->orderBy('created_at', 'desc')
            ->get();

        $requesterIds = $branches->pluck('requested_by')->filter()->unique()->values();
        $requesters = User::whereIn('id', $requesterIds)->get()->keyBy('id');

        $result = $branches->map(function ($branch) use ($requesters) {
            $requester = $branch->requested_by ? ($requesters[$branch->requested_by] ?? null) : null;
            return [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'address' => $branch->address,
                'budget' => isset($branch->budget) ? (int) $branch->budget : 0,
                'created_at' => $branch->created_at,
                'requested_by' => $requester ? [
                    'id' => $requester->id,
                    'full_name' => $requester->full_name,
                    'username' => $requester->username,
                    'role' => $requester->role,
                ] : null,
            ];
        });

        return response()->json(['ok' => true, 'branches' => $result]);
    }

    /**
     * Approve a pending branch request.
     */
    public function approveBranchRequest(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'], ['admin']);
        if (! $allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branch = Branch::find($id);
        if (! $branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }
        if (($branch->approval_status ?? 'approved') !== 'pending_owner') {
            return response()->json(['ok' => false, 'message' => 'Branch is not pending approval'], 409);
        }

        DB::beginTransaction();
        try {
            $mainBranch = Branch::where('is_main_branch', 1)->lockForUpdate()->first();
            if (! $mainBranch) {
                DB::rollBack();
                return response()->json(['ok' => false, 'message' => 'Main branch not found for budget allocation.'], 422);
            }

            $allocation = (int) ($branch->budget ?? 0);
            $mainBudget = (int) ($mainBranch->budget ?? 0);
            if ($mainBudget < $allocation) {
                DB::rollBack();
                return response()->json(['ok' => false, 'message' => 'Insufficient main branch budget for this allocation.'], 422);
            }

            $mainBranch->budget = $mainBudget - $allocation;
            $mainBranch->save();

            $branch->approval_status = 'approved';
            $branch->approved_by = $user->id;
            $branch->approved_at = now();
            $branch->rejected_at = null;
            $branch->is_active = 1;
            $branch->save();

            User::where('branch_id', $branch->id)->update(['is_active' => 1]);

            DB::commit();
            return response()->json(['ok' => true, 'message' => 'Branch approved and activated.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveBranchRequest error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to approve branch'], 500);
        }
    }

    /**
     * Reject a pending branch request.
     */
    public function rejectBranchRequest(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'], ['admin']);
        if (! $allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branch = Branch::find($id);
        if (! $branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }
        if (($branch->approval_status ?? 'approved') !== 'pending_owner') {
            return response()->json(['ok' => false, 'message' => 'Branch is not pending approval'], 409);
        }

        DB::beginTransaction();
        try {
            $branch->approval_status = 'rejected';
            $branch->approved_by = $user->id;
            $branch->approved_at = null;
            $branch->rejected_at = now();
            $branch->is_active = 0;
            $branch->save();

            User::where('branch_id', $branch->id)->update(['is_active' => 0]);

            DB::commit();
            return response()->json(['ok' => true, 'message' => 'Branch rejected.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectBranchRequest error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to reject branch'], 500);
        }
    }

    /**
     * List pending branch requests for main branch finance confirmation.
     */
    public function pendingFinanceBranchRequests(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        $isMainBranchFinance = false;
        if ($roleUpper === 'MANAGER' && strtoupper($user->department ?? '') === 'FINANCE') {
            $b = Branch::find($user->branch_id);
            $isMainBranchFinance = (bool) ($b && ($b->is_main_branch ?? false));
        }
        if (! $isMainBranchFinance) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branches = Branch::where('approval_status', 'pending_finance')
            ->orderBy('created_at', 'desc')
            ->get();

        $requesterIds = $branches->pluck('requested_by')->filter()->unique()->values();
        $requesters = User::whereIn('id', $requesterIds)->get()->keyBy('id');

        $result = $branches->map(function ($branch) use ($requesters) {
            $requester = $branch->requested_by ? ($requesters[$branch->requested_by] ?? null) : null;
            return [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'address' => $branch->address,
                'budget' => isset($branch->budget) ? (int) $branch->budget : 0,
                'created_at' => $branch->created_at,
                'requested_by' => $requester ? [
                    'id' => $requester->id,
                    'full_name' => $requester->full_name,
                    'username' => $requester->username,
                    'role' => $requester->role,
                ] : null,
            ];
        });

        return response()->json(['ok' => true, 'branches' => $result]);
    }

    /**
     * Main branch finance confirms budget allocation for a branch request.
     */
    public function approveFinanceBranchRequest(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        $isMainBranchFinance = false;
        if ($roleUpper === 'MANAGER' && strtoupper($user->department ?? '') === 'FINANCE') {
            $b = Branch::find($user->branch_id);
            $isMainBranchFinance = (bool) ($b && ($b->is_main_branch ?? false));
        }
        if (! $isMainBranchFinance) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branch = Branch::find($id);
        if (! $branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }
        if (($branch->approval_status ?? 'approved') !== 'pending_finance') {
            return response()->json(['ok' => false, 'message' => 'Branch is not pending finance confirmation'], 409);
        }

        DB::beginTransaction();
        try {
            $branch->approval_status = 'pending_owner';
            $branch->finance_confirmed_by = $user->id;
            $branch->finance_confirmed_at = now();
            $branch->save();

            DB::commit();
            return response()->json(['ok' => true, 'message' => 'Budget confirmed. Awaiting owner approval.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveFinanceBranchRequest error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to confirm budget allocation'], 500);
        }
    }

    /**
     * Main branch finance rejects budget allocation for a branch request.
     */
    public function rejectFinanceBranchRequest(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        $isMainBranchFinance = false;
        if ($roleUpper === 'MANAGER' && strtoupper($user->department ?? '') === 'FINANCE') {
            $b = Branch::find($user->branch_id);
            $isMainBranchFinance = (bool) ($b && ($b->is_main_branch ?? false));
        }
        if (! $isMainBranchFinance) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branch = Branch::find($id);
        if (! $branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }
        if (($branch->approval_status ?? 'approved') !== 'pending_finance') {
            return response()->json(['ok' => false, 'message' => 'Branch is not pending finance confirmation'], 409);
        }

        DB::beginTransaction();
        try {
            $mainBranch = Branch::where('is_main_branch', 1)->lockForUpdate()->first();
            if (! $mainBranch) {
                DB::rollBack();
                return response()->json(['ok' => false, 'message' => 'Main branch not found for budget allocation.'], 422);
            }

            $allocation = (int) ($branch->budget ?? 0);
            $mainBudget = (int) ($mainBranch->budget ?? 0);
            if ($mainBudget < $allocation) {
                DB::rollBack();
                return response()->json(['ok' => false, 'message' => 'Insufficient main branch budget for this allocation.'], 422);
            }

            $mainBranch->budget = $mainBudget - $allocation;
            $mainBranch->save();

            $branch->approval_status = 'approved';
            $branch->approved_by = $user->id;
            $branch->approved_at = now();
            $branch->rejected_at = null;
            $branch->is_active = 1;
            $branch->save();

            User::where('branch_id', $branch->id)->update(['is_active' => 1]);

            DB::commit();
            return response()->json(['ok' => true, 'message' => 'Branch approved and activated.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveBranchRequest error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to approve branch'], 500);
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
        $isMainBranchAdmin = false;
        if ($roleUpper === 'ADMIN') {
            $b = Branch::find($user->branch_id);
            $isMainBranchAdmin = (bool) ($b && ($b->is_main_branch ?? false));
        }
        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin']);
        if (! $allowed && ! $isMainBranchAdmin) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branch = Branch::find($id);
        if (!$branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }

        $branchCodeUpper = strtoupper((string) ($branch->code ?? ''));
        $branchNameUpper = strtoupper((string) ($branch->name ?? ''));
        $isMainBranch = (bool) ($branch->is_main_branch ?? false)
            || in_array($branchCodeUpper, ['MAIN', 'MAIN_HQ', 'HQ', 'MAINBRANCH'])
            || in_array($branchNameUpper, ['MAIN BRANCH', 'MAIN HQ BRANCH', 'HEADQUARTERS']);

        if ($isMainBranch) {
            return response()->json([
                'ok' => false,
                'message' => 'Main Branch (HQ) is protected and cannot be deleted.',
            ], 422);
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

    /**
     * Deactivate a branch (prevent login for accounts in that branch)
     * PATCH /api/superadmin/branches/{id}/deactivate
     */
    public function deactivateBranch(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        $isMainBranchAdmin = false;
        if ($roleUpper === 'ADMIN') {
            $b = Branch::find($user->branch_id);
            $isMainBranchAdmin = (bool) ($b && ($b->is_main_branch ?? false));
        }
        $allowed = Permission::allowed($user, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'], ['admin']);
        if (!$allowed && !$isMainBranchAdmin) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branch = Branch::find($id);
        if (!$branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }

        if ($branch->is_main_branch) {
            return response()->json([
                'ok' => false,
                'message' => 'Main Branch (HQ) is protected and cannot be deactivated.',
            ], 422);
        }

        try {
            $branch->is_active = 0;
            $branch->save();

            Log::info('Branch deactivated', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'deactivated_by' => $user->username,
            ]);

            return response()->json([
                'ok' => true,
                'message' => "Branch '{$branch->name}' has been deactivated. Accounts in this branch can no longer login.",
            ]);
        } catch (\Exception $e) {
            Log::error('deactivateBranch error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to deactivate branch: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reactivate a branch (allow login for accounts in that branch)
     * PATCH /api/superadmin/branches/{id}/reactivate
     */
    public function reactivateBranch(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        $isMainBranchAdmin = false;
        if ($roleUpper === 'ADMIN') {
            $b = Branch::find($user->branch_id);
            $isMainBranchAdmin = (bool) ($b && ($b->is_main_branch ?? false));
        }
        $allowed = Permission::allowed($user, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'], ['admin']);
        if (!$allowed && !$isMainBranchAdmin) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branch = Branch::find($id);
        if (!$branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }

        if ($branch->is_main_branch) {
            return response()->json([
                'ok' => false,
                'message' => 'Main Branch (HQ) is protected and cannot be managed.',
            ], 422);
        }

        try {
            $branch->is_active = 1;
            $branch->save();

            Log::info('Branch reactivated', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'reactivated_by' => $user->username,
            ]);

            return response()->json([
                'ok' => true,
                'message' => "Branch '{$branch->name}' has been reactivated. Accounts in this branch can now login.",
            ]);
        } catch (\Exception $e) {
            Log::error('reactivateBranch error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to reactivate branch: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get all logistics transactions (deliveries) across all branches
     * GET /api/superadmin/logistics/deliveries
     */
    public function logisticsDeliveries(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin']);
        if (!$allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $branchId = $request->query('branch_id');
            $status = $request->query('status'); // Filter by status (pending, in_transit, delivered, etc)
            $perPage = (int) $request->query('per_page', 50);

            $query = \App\Models\LogisticsTransaction::with([
                'procurementRequest',
                'product',
                'branch'
            ])
            ->orderBy('created_at', 'desc');

            if ($branchId) {
                $query->where('branch_id', (int) $branchId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $transactions = $query->paginate($perPage);

            return response()->json([
                'data' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('logisticsDeliveries failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to load deliveries'], 500);
        }
    }

    /**
     * Get all suppliers across all branches with comprehensive data validation
     * GET /api/superadmin/suppliers
     */
    public function suppliers(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin']);
        if (!$allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $branchId = $request->query('branch_id');
            $includeDetails = $request->query('details', false);

            // Use SupplierService for data validation and consistency checking
            $suppliers = \App\Services\SupplierService::getAllSuppliers($branchId ? (int) $branchId : null);

            if ($includeDetails) {
                // Include detailed validation information
                $suppliers = $suppliers->map(function ($supplier) {
                    $detailed = \App\Services\SupplierService::getSupplierDetail($supplier['id']);
                    return $detailed;
                });
            }

            return response()->json([
                'ok' => true,
                'data' => $suppliers,
                'count' => $suppliers->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('suppliers endpoint error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to load suppliers'], 500);
        }
    }

    /**
     * Get detailed supplier profile with full consistency verification
     * GET /api/superadmin/suppliers/{id}
     */
    public function supplierDetail(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin']);
        if (!$allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $supplier = \App\Services\SupplierService::getSupplierDetail((int) $id);

            if (!$supplier) {
                return response()->json(['ok' => false, 'message' => 'Supplier not found'], 404);
            }

            // Get additional validation data
            $orderValidation = \App\Services\SupplierService::validateSupplierOrders((int) $id);
            $pricingValidation = \App\Services\SupplierService::verifyProductPricingConsistency((int) $id);
            $activityHistory = \App\Services\SupplierService::getSupplierActivityHistory((int) $id, 50);

            return response()->json([
                'ok' => true,
                'supplier' => $supplier,
                'validations' => [
                    'orders' => $orderValidation,
                    'pricing' => $pricingValidation,
                ],
                'activity_history' => $activityHistory,
            ]);
        } catch (\Exception $e) {
            Log::error('supplierDetail error', ['supplier_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to load supplier details'], 500);
        }
    }

    /**
     * Validate supplier data consistency and identify issues
     * GET /api/superadmin/suppliers/{id}/validate
     */
    public function validateSupplier(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin']);
        if (!$allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $supplier = User::find((int) $id);
            if (!$supplier || strtoupper($supplier->role ?? '') !== 'SUPPLIER') {
                return response()->json(['ok' => false, 'message' => 'Supplier not found'], 404);
            }

            $orderValidation = \App\Services\SupplierService::validateSupplierOrders((int) $id);
            $pricingValidation = \App\Services\SupplierService::verifyProductPricingConsistency((int) $id);
            $hasDuplicates = \App\Services\SupplierService::hasDuplicateEntries((int) $id);

            return response()->json([
                'ok' => true,
                'supplier_id' => (int) $id,
                'supplier_name' => $supplier->full_name,
                'validations' => [
                    'orders' => $orderValidation,
                    'pricing' => $pricingValidation,
                    'has_duplicate_entries' => $hasDuplicates,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('validateSupplier error', ['supplier_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Validation failed'], 500);
        }
    }

    /**
     * Check for duplicate supplier entries
     * GET /api/superadmin/suppliers/{id}/duplicates
     */
    public function checkDuplicates(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin']);
        if (!$allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $supplier = User::find((int) $id);
            if (!$supplier || strtoupper($supplier->role ?? '') !== 'SUPPLIER') {
                return response()->json(['ok' => false, 'message' => 'Supplier not found'], 404);
            }

            $duplicates = \App\Services\SupplierService::findPotentialDuplicates($supplier);

            return response()->json([
                'ok' => true,
                'supplier_id' => (int) $id,
                'duplicates_found' => $duplicates->count(),
                'duplicates' => $duplicates->map(function ($dup) {
                    return [
                        'id' => $dup->id,
                        'name' => $dup->full_name,
                        'email' => $dup->email,
                        'phone' => $dup->phone_number,
                        'branch_id' => $dup->branch_id,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('checkDuplicates error', ['supplier_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Check failed'], 500);
        }
    }

    /**
     * Update supplier status (active/inactive)
     * PUT /api/superadmin/suppliers/{id}/status
     */
    public function updateSupplierStatus(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin']);
        if (!$allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        try {
            $result = \App\Services\SupplierService::updateSupplierStatus(
                (int) $id,
                (bool) $validated['is_active'],
                $user->id
            );

            if ($result['status'] !== 'success') {
                return response()->json($result, 400);
            }

            return response()->json([
                'ok' => true,
                'message' => $result['message'],
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('updateSupplierStatus error', ['supplier_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to update supplier status'], 500);
        }
    }

    /**
     * Get supplier activity history/audit log
     * GET /api/superadmin/suppliers/{id}/activity
     */
    public function supplierActivityHistory(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin']);
        if (!$allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $limit = (int) $request->query('limit', 100);
            $history = \App\Services\SupplierService::getSupplierActivityHistory((int) $id, $limit);

            return response()->json([
                'ok' => true,
                'supplier_id' => (int) $id,
                'activity_count' => $history->count(),
                'activities' => $history,
            ]);
        } catch (\Exception $e) {
            Log::error('supplierActivityHistory error', ['supplier_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to load activity history'], 500);
        }
    }

    /**
     * Get supplier audit logs with filtering
     * GET /api/superadmin/suppliers/audit/logs
     */
    public function supplierAuditLogs(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin']);
        if (!$allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $supplierId = $request->query('supplier_id');
            $action = $request->query('action');
            $severity = $request->query('severity'); // info, warning, critical
            $daysBack = (int) $request->query('days', 30);
            $limit = (int) $request->query('limit', 100);

            $query = \App\Models\SupplierAuditLog::query();

            if ($supplierId) {
                $query->where('supplier_id', (int) $supplierId);
            }

            if ($action) {
                $query->where('action', $action);
            }

            if ($severity) {
                $query->where('severity', $severity);
            }

            if ($daysBack > 0) {
                $query->where('created_at', '>=', now()->subDays($daysBack));
            }

            $logs = $query->with(['supplier', 'triggeredBy'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'ok' => true,
                'logs' => $logs,
                'count' => $logs->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('supplierAuditLogs error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to load audit logs'], 500);
        }
    }

    /**
     * Update staff account (SuperAdmin staff management)
     * PUT /api/superadmin/staff/{id}
     */
    public function updateStaff(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $roleUpper = strtoupper($user->role ?? '');
        if ($roleUpper !== 'SUPER_ADMIN' && $roleUpper !== 'SUPERADMIN') {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            // Validate incoming data - all fields are optional for partial updates
            $validated = $request->validate([
                'username' => 'sometimes|string|max:255|unique:users,username,' . $id,
                'full_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
                'phone_number' => 'sometimes|string|max:20',
                'role' => 'sometimes|string|max:100',
                'department' => 'sometimes|string|max:100',
                'is_active' => 'sometimes|boolean',
                'branch_id' => 'sometimes|integer|exists:branches,id',
            ]);

            // Find the user
            $staffUser = User::find($id);
            if (!$staffUser) {
                return response()->json(['ok' => false, 'message' => 'Staff member not found'], 404);
            }

            // Update only the fields that are provided
            $staffUser->update($validated);

            Log::info('Staff account updated by SuperAdmin', [
                'staff_id' => $id,
                'updated_by' => $user->id,
                'fields' => array_keys($validated),
            ]);

            // Reload to get updated data
            $staffUser->refresh();

            return response()->json([
                'ok' => true,
                'message' => 'Staff account updated successfully',
                'data' => [
                    'id' => $staffUser->id,
                    'username' => $staffUser->username,
                    'full_name' => $staffUser->full_name,
                    'email' => $staffUser->email,
                    'phone_number' => $staffUser->phone_number,
                    'role' => $staffUser->role,
                    'department' => $staffUser->department,
                    'is_active' => $staffUser->is_active,
                    'branch_id' => $staffUser->branch_id,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Staff update validation error', [
                'staff_id' => $id,
                'errors' => $e->errors(),
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Staff update error: ' . $e->getMessage(), [
                'staff_id' => $id,
                'updated_by' => $user->id ?? null,
                'request_data' => $request->all(),
            ]);
            return response()->json(['ok' => false, 'message' => 'Failed to update account: ' . $e->getMessage()], 500);
        }
    }
}

