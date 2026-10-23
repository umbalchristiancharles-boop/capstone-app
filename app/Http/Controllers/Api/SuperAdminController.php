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
use App\Models\PositionOpenRequest;
use App\Models\StaffDocument;
use App\Models\PriceMarkupPercentage;
use Illuminate\Support\Facades\Schema;
use App\Support\Permission;
use App\Services\BranchPasswordService;

class SuperAdminController extends Controller
{
    private const BRANCH_REJECTION_REASONS = [
        'Incomplete or inaccurate branch information',
        'Insufficient budget or financial justification',
        'Location does not meet branch requirements',
        'Missing or invalid supporting documents',
        'Does not meet branch approval criteria',
        'Other',
    ];

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

    private function costAttachmentsForResponse(Branch $branch): array
    {
        $attachments = $branch->cost_attachments ?? [];
        $result = [];

        foreach (['permit_bills', 'construction_costs', 'equipment_costs'] as $category) {
            $result[$category] = array_map(
                fn (array $attachment, int $index) => array_merge($attachment, [
                    'url' => "/api/superadmin/branches/{$branch->id}/cost-attachments/{$category}/{$index}",
                ]),
                $attachments[$category] ?? [],
                array_keys($attachments[$category] ?? [])
            );
        }

        return $result;
    }

    public function downloadBranchCostAttachment(Request $request, int $id, string $category, int $index)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (! $user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $branch = Branch::find($id);
        if (! $branch) {
            return response()->json(['ok' => false, 'message' => 'Branch not found'], 404);
        }

        $roleUpper = strtoupper($user->role ?? '');
        $isMainBranchAdmin = false;
        if ($roleUpper === 'ADMIN') {
            $userBranch = Branch::find($user->branch_id);
            $isMainBranchAdmin = (bool) ($userBranch && ($userBranch->is_main_branch ?? false));
        }

        $isMainBranchFinance = in_array($roleUpper, ['SUPER_ADMIN', 'SUPERADMIN'], true);
        if (! $isMainBranchFinance && in_array($roleUpper, ['MANAGER', 'MANAGER_FINANCE'], true) && strtoupper($user->department ?? '') === 'FINANCE') {
            $userBranch = Branch::find($user->branch_id);
            $isMainBranchFinance = (bool) ($userBranch && ($userBranch->is_main_branch ?? false));
        }

        $isOwner = Permission::allowed($user, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'], ['admin', 'admin.branches']);
        if (! $isOwner && ! $isMainBranchAdmin && ! $isMainBranchFinance) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        if (! in_array($category, ['permit_bills', 'construction_costs', 'equipment_costs'], true)) {
            return response()->json(['ok' => false, 'message' => 'Attachment not found'], 404);
        }

        $attachment = $branch->cost_attachments[$category][$index] ?? null;
        $path = is_array($attachment) ? ($attachment['path'] ?? null) : null;
        if ($index < 0 || ! is_string($path) || ! str_starts_with($path, 'branch-cost-attachments/') || ! Storage::disk('local')->exists($path)) {
            return response()->json(['ok' => false, 'message' => 'Attachment not found'], 404);
        }

        return Storage::disk('local')->download($path, basename($attachment['name'] ?? 'attachment'));
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

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'ADMIN', 'OWNER'], ['admin', 'logistics']);
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
            ->whereIn('status', ['pending', 'in_kitchen', 'preparing', 'ready'])
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

        // Build the activity feed from persisted records only. There is no generic
        // activity table, so each entry identifies the source record and event time.
        $activity = collect();

        Order::with(['owner:id,full_name,role', 'cashier:id,full_name,role', 'branch:id,name'])
            ->whereBetween('created_at', $dateRange)
            ->latest('created_at')
            ->limit(25)
            ->get()
            ->each(function ($order) use ($activity) {
                $actor = $order->cashier ?: $order->owner;
                $actorName = $actor?->full_name ?: 'Unknown user';
                $actorRole = $actor?->role ? strtoupper(str_replace('_', ' ', $actor->role)) : 'Unknown role';
                $status = strtoupper(str_replace('_', ' ', (string) ($order->status ?: 'created')));

                $activity->push([
                    'id' => 'order-created-' . $order->id,
                    'title' => 'Order ' . ($order->order_code ?: '#' . $order->id) . ' recorded',
                    'description' => "{$actorName} ({$actorRole}) created an order in " . ($order->branch?->name ?: 'Unknown branch') . ". Status: {$status}.",
                    'actor' => $actorName,
                    'role' => $actorRole,
                    'module' => 'Orders',
                    'branch' => $order->branch?->name,
                    'timestamp' => optional($order->created_at)->toIso8601String(),
                    'status' => ucfirst(strtolower($status)),
                    'badgeClass' => in_array(strtolower((string) $order->status), ['cancelled', 'refunded'], true) ? 'badge--warning' : 'badge--success',
                ]);
            });

        User::with('branch:id,name')
            ->whereBetween('created_at', $dateRange)
            ->whereNull('deleted_at')
            ->whereNotIn('role', ['SUPER_ADMIN', 'SUPERADMIN'])
            ->latest('created_at')
            ->limit(25)
            ->get(['id', 'full_name', 'role', 'branch_id', 'created_at'])
            ->each(function ($createdUser) use ($activity) {
                $role = $createdUser->role ? strtoupper(str_replace('_', ' ', $createdUser->role)) : 'Unknown role';
                $name = $createdUser->full_name ?: 'Unknown user';

                $activity->push([
                    'id' => 'user-created-' . $createdUser->id,
                    'title' => 'User account created',
                    'description' => "{$name} ({$role}) was added to " . ($createdUser->branch?->name ?: 'Unknown branch') . ".",
                    'actor' => $name,
                    'role' => $role,
                    'module' => 'User Management',
                    'branch' => $createdUser->branch?->name,
                    'timestamp' => optional($createdUser->created_at)->toIso8601String(),
                    'status' => 'Recorded',
                    'badgeClass' => 'badge--info',
                ]);
            });

        \App\Models\Attendance::with(['user:id,full_name,role,branch_id', 'user.branch:id,name'])
            ->whereBetween('date', [$dateRange[0]->format('Y-m-d'), $dateRange[1]->format('Y-m-d')])
            ->latest('date')
            ->latest('time_in')
            ->limit(25)
            ->get()
            ->each(function ($attendance) use ($activity) {
                $user = $attendance->user;
                $name = $user?->full_name ?: 'Unknown user';
                $role = $user?->role ? strtoupper(str_replace('_', ' ', $user->role)) : 'Unknown role';
                $status = strtolower((string) ($attendance->status ?: 'recorded'));
                $eventTime = $attendance->time_in ?: $attendance->created_at;

                $activity->push([
                    'id' => 'attendance-' . $attendance->id,
                    'title' => 'Attendance ' . ucfirst($status),
                    'description' => "{$name} ({$role}) recorded attendance in " . ($user?->branch?->name ?: 'Unknown branch') . ".",
                    'actor' => $name,
                    'role' => $role,
                    'module' => 'Attendance',
                    'branch' => $user?->branch?->name,
                    'timestamp' => optional($eventTime)->toIso8601String(),
                    'status' => ucfirst($status),
                    'badgeClass' => in_array($status, ['absent', 'late'], true) ? 'badge--warning' : 'badge--success',
                ]);
            });

        \App\Models\Announcement::with('sender:id,full_name,role')
            ->whereBetween('created_at', $dateRange)
            ->latest('created_at')
            ->limit(25)
            ->get()
            ->each(function ($announcement) use ($activity) {
                $sender = $announcement->sender;
                $name = $sender?->full_name ?: 'Unknown user';
                $role = $sender?->role ? strtoupper(str_replace('_', ' ', $sender->role)) : 'Unknown role';

                $activity->push([
                    'id' => 'announcement-' . $announcement->id,
                    'title' => 'Announcement published',
                    'description' => "{$name} ({$role}) published “{$announcement->title}” to " . ($announcement->target ?: 'selected users') . ".",
                    'actor' => $name,
                    'role' => $role,
                    'module' => 'Announcements',
                    'branch' => null,
                    'timestamp' => optional($announcement->created_at)->toIso8601String(),
                    'status' => 'Published',
                    'badgeClass' => 'badge--info',
                ]);
            });

        $systemActivity = $activity
            ->filter(fn ($item) => !empty($item['timestamp']))
            ->sortByDesc('timestamp')
            ->take(15)
            ->values()
            ->all();

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

        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'ADMIN', 'OWNER'], ['admin']);
        if (! $allowed) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $branchId = $request->query('branch_id');

            $query = \App\Models\SupplierOrder::with(['product', 'procurementRequest.logisticsUser', 'procurementRequest.product', 'branch', 'supplier'])
                ->whereNotNull('supplier_id')
                ->whereHas('product', function ($productQuery) {
                    $productQuery->whereNotNull('image_path')
                        ->where(function ($identifierQuery) {
                            $identifierQuery->whereNotNull('barcode')
                                ->orWhereNotNull('sku');
                        });
                })
                ->orderBy('created_at', 'desc');

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            $perPage = intval($request->query('per_page', 50));
            $orders = $query->paginate($perPage);
            $procurementProductIds = $orders->getCollection()
                ->pluck('procurementRequest.product_id')
                ->filter()
                ->unique();
            $adminRequestProductIds = \App\Models\ProductRequest::whereIn('product_id', $procurementProductIds)
                ->pluck('product_id')
                ->flip();

            $branchMarkups = PriceMarkupPercentage::whereIn('branch_id', $orders->getCollection()->pluck('branch_id')->filter()->unique())
                ->where('is_active', true)
                ->pluck('percentage', 'branch_id');
            $orders->getCollection()->transform(function ($order) use ($branchMarkups, $adminRequestProductIds) {
                $supplierPrice = (float) ($order->product?->price ?? $order->procurementRequest?->price ?? 0);
                $markupPercentage = (float) ($branchMarkups->get($order->branch_id) ?? PriceMarkupPercentage::defaultPercentage());
                $procurementProduct = $order->procurementRequest?->product;
                $isAdminProductRequest = $procurementProduct
                    && $adminRequestProductIds->has($procurementProduct->id);
                $order->supplier_price = $supplierPrice;
                $order->supplier_total = round($supplierPrice * max(1, (int) $order->quantity), 2);
                $order->markup_percentage = $markupPercentage;
                $order->expected_selling_price = round($supplierPrice * (1 + ($markupPercentage / 100)), 2);
                $order->expected_profit = round($supplierPrice * ($markupPercentage / 100), 2);
                $order->is_admin_product_request = (bool) $isAdminProductRequest;
                $order->is_owner_direct_product = !$isAdminProductRequest
                    && (bool) ($procurementProduct?->approved_by_owner || $order->product?->status === 'pending_owner');

                return $order;
            });

            return response()->json($orders);
        } catch (\Exception $e) {
            Log::error('logisticsSupplierOrders failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function confirmSupplierOrder(Request $request, $id)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user || !Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'ADMIN', 'OWNER'], ['admin'])) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $order = \App\Models\SupplierOrder::with(['product', 'procurementRequest'])->findOrFail($id);
        if (!$order->product_id || !$order->product || !$order->product->image_path || (!$order->product->barcode && !$order->product->sku)) {
            return response()->json(['ok' => false, 'message' => 'Supplier has not submitted complete product details yet.'], 422);
        }

        $productRequest = $order->procurementRequest
            ? \App\Models\ProductRequest::where('product_id', $order->procurementRequest->product_id)->first()
            : null;
        $isOwnerDirectProduct = !$productRequest && (bool) (
            $order->procurementRequest?->product?->approved_by_owner
            || $order->product?->status === 'pending_owner'
        );
        if ($productRequest && $productRequest->status !== 'pending_supplier') {
            return response()->json(['ok' => false, 'message' => 'A supplier has already been selected or this request is no longer awaiting supplier pricing.'], 409);
        }
        $procurementRequest = DB::transaction(function () use ($order, $productRequest, $isOwnerDirectProduct, $user) {
            $procurementRequest = $order->procurementRequest;
            $price = (float) ($order->product?->price ?? $procurementRequest?->price ?? 0);
            $quantity = max(1, (int) $order->quantity);

            if (!$procurementRequest && $isOwnerDirectProduct) {
                $procurementRequest = \App\Models\ProcurementRequest::create([
                    'logistics_user_id' => $user->id,
                    'product_id' => $order->product_id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total_amount' => $price * $quantity,
                    'status' => 'pending',
                    'budget_approved' => false,
                    'supplier_confirmed' => false,
                    'branch_id' => $order->branch_id,
                ]);
                $order->procurement_request_id = $procurementRequest->id;
            }

            $order->update([
                'admin_confirmed' => true,
                'admin_confirmed_by' => $user->id,
                'admin_confirmed_at' => now(),
                'procurement_request_id' => $procurementRequest?->id ?? $order->procurement_request_id,
            ]);

            if ($procurementRequest) {
                $procurementRequest->update([
                    // Admin confirmation makes the quote selectable; only
                    // Procurement may assign the supplier.
                    'supplier_confirmed' => false,
                    'price' => $price,
                    'total_amount' => $price * $quantity,
                ]);
            }

            return $procurementRequest;
        });

        $message = $productRequest
            ? 'Supplier selected and request sent to Main Branch Logistics.'
            : ($isOwnerDirectProduct
                ? 'Supplier confirmed for the owner-created product.'
                : 'Supplier confirmed.');

        return response()->json(['ok' => true, 'message' => $message, 'order' => $order->fresh()->load(['product', 'supplier', 'procurementRequest'])]);
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
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
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
            'real_stock' => $validated['stock'],
            'min_stock' => $validated['min_stock'] ?? 10,
            'branch_id' => $validated['branch_id'],
            'is_active' => true,
            'is_kitchen_dish' => $isDish,
            // Super admin-created products should NOT be visible in cashier
            // until a branch Admin publishes them. Keep unpublished by default.
            'is_published' => false,
        ]);

        if ($request->hasFile('image')) {
            $imagesDirectory = public_path('product-images');
            if (!is_dir($imagesDirectory)) {
                mkdir($imagesDirectory, 0755, true);
            }
            $imageName = $request->file('image')->hashName();
            $request->file('image')->move($imagesDirectory, $imageName);
            $product->image_path = 'product-images/' . $imageName;
            $product->save();
        }

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $product->update($validated);

        \App\Models\Product::recomputeRealStockForGroup(
            (int) $product->branch_id,
            $product->sku,
            $product->name
        );

        if ($request->hasFile('image')) {
            $imagesDirectory = public_path('product-images');
            if (!is_dir($imagesDirectory)) {
                mkdir($imagesDirectory, 0755, true);
            }
            $imageName = $request->file('image')->hashName();
            $request->file('image')->move($imagesDirectory, $imageName);
            $product->image_path = 'product-images/' . $imageName;
            $product->save();
        }

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
                    'budget' => round((float) ($branch->budget ?? 0), 2),
                    'initial_budget' => round((float) ($branch->initial_budget ?? $branch->budget ?? 0), 2),
                    'is_active' => (bool) $branch->is_active,
                    'is_main_branch' => (bool) ($branch->is_main_branch ?? false),
                    'approval_status' => $branch->approval_status ?? 'approved',
                    'rejection_reason' => $branch->rejection_reason,
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
        $allowed = Permission::allowed($user, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], ['admin', 'admin.branches']);
        if (! $allowed && ! $isMainBranchAdmin) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $requiresOwnerApproval = $isMainBranchAdmin && ! $allowed;
        $approvalStatus = $requiresOwnerApproval ? 'pending_finance' : 'approved';
        $branchIsActive = $requiresOwnerApproval ? 0 : 1;

        $request->validate([
            'code' => 'nullable|string|max:20',
            'name' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'square_meters' => 'nullable|numeric|min:0',
            'geofencing_radius' => 'nullable|numeric|min:0',
            'cost_attachments' => 'nullable|array',
            'cost_attachments.permit_bills' => 'nullable|array|max:10',
            'cost_attachments.permit_bills.*' => 'file|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx|max:10240',
            'cost_attachments.construction_costs' => 'nullable|array|max:10',
            'cost_attachments.construction_costs.*' => 'file|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx|max:10240',
            'cost_attachments.equipment_costs' => 'nullable|array|max:10',
            'cost_attachments.equipment_costs.*' => 'file|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx|max:10240',
            'cost_attachment_amounts' => 'nullable|array',
            'cost_attachment_amounts.permit_bills' => 'nullable|array',
            'cost_attachment_amounts.permit_bills.*' => 'required|numeric|min:0|max:9999999999999.99',
            'cost_attachment_amounts.construction_costs' => 'nullable|array',
            'cost_attachment_amounts.construction_costs.*' => 'required|numeric|min:0|max:9999999999999.99',
            'cost_attachment_amounts.equipment_costs' => 'nullable|array',
            'cost_attachment_amounts.equipment_costs.*' => 'required|numeric|min:0|max:9999999999999.99',
        ]);

        foreach (['permit_bills', 'construction_costs', 'equipment_costs'] as $category) {
            $fileCount = count((array) $request->file("cost_attachments.{$category}", []));
            $amountCount = count((array) $request->input("cost_attachment_amounts.{$category}", []));
            if ($fileCount !== $amountCount) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "cost_attachment_amounts.{$category}" => 'Enter an amount for every attached file.',
                ]);
            }
        }

        $code = $request->input('code');
        $name = $request->input('name');
        $address = $request->input('address');
        if (!is_string($address) || trim($address) === '') {
            $addressParts = array_filter([
                $request->input('barangay'),
                $request->input('city'),
                $request->input('province'),
                $request->input('region'),
            ], fn ($part) => is_string($part) && trim($part) !== '');
            $address = implode(', ', $addressParts);
        }
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $squareMeters = $request->input('square_meters');
        $geofencingRadius = $request->input('geofencing_radius');
        
        // Cost details
        $calculatedInvestment = collect(['permit_bills', 'construction_costs', 'equipment_costs'])
            ->sum(fn ($category) => collect($request->input("cost_attachment_amounts.{$category}", []))
                ->sum(fn ($amount) => (float) $amount));
        $calculatedInvestment = round($calculatedInvestment, 2);
        if ($calculatedInvestment > 9999999999999.99) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cost_attachment_amounts' => 'The total attachment amount exceeds the maximum supported investment.',
            ]);
        }
        $totalInvestment = $calculatedInvestment > 0 ? $calculatedInvestment : null;

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
        $storedAttachmentPaths = [];
        try {
            $costAttachments = [];
            foreach (['permit_bills', 'construction_costs', 'equipment_costs'] as $category) {
                $costAttachments[$category] = [];
                $attachmentAmounts = array_values((array) $request->input("cost_attachment_amounts.{$category}", []));
                foreach (array_values((array) $request->file("cost_attachments.{$category}", [])) as $index => $file) {
                    $path = $file->store('branch-cost-attachments', 'local');
                    if (! is_string($path)) {
                        throw new \RuntimeException('Failed to store a branch cost attachment.');
                    }
                    $storedAttachmentPaths[] = $path;
                    $costAttachments[$category][] = [
                        'name' => $file->getClientOriginalName(),
                        'amount' => (float) $attachmentAmounts[$index],
                        'path' => $path,
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ];
                }
            }

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
                'square_meters' => $squareMeters,
                'geofencing_radius' => $geofencingRadius,
                'cost_attachments' => collect($costAttachments)->flatten(1)->isNotEmpty() ? $costAttachments : null,
                'total_investment' => $totalInvestment,
            ]);

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => $requiresOwnerApproval
                    ? "Branch '{$name}' created and awaiting Main Branch Finance invoice and budget confirmation."
                    : "Branch '{$name}' created.",
                'branch_id' => $branch->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($storedAttachmentPaths as $path) {
                Storage::disk('local')->delete($path);
            }
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
            ->whereNull('approved_by')
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
                'budget' => round((float) ($branch->budget ?? 0), 2),
                'initial_budget' => round((float) ($branch->initial_budget ?? $branch->budget ?? 0), 2),
                'latitude' => $branch->latitude,
                'longitude' => $branch->longitude,
                'square_meters' => $branch->square_meters,
                'geofencing_radius' => $branch->geofencing_radius,
                'permit_bills' => $branch->permit_bills ?? [],
                'construction_costs' => $branch->construction_costs ?? [],
                'equipment_costs' => $branch->equipment_costs ?? [],
                'cost_attachments' => $this->costAttachmentsForResponse($branch),
                'total_investment' => $branch->total_investment === null ? null : (float) $branch->total_investment,
                'finance_confirmed_at' => $branch->finance_confirmed_at,
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
        if ($branch->approval_status !== 'pending_owner') {
            return response()->json(['ok' => false, 'message' => 'Branch must be confirmed by Main Branch Finance before Owner approval.'], 409);
        }
        if ($branch->approved_by) {
            return response()->json(['ok' => false, 'message' => 'Owner approval has already been recorded.'], 409);
        }
        if (! $branch->finance_confirmed_by || ! $branch->finance_confirmed_at || (int) ($branch->budget ?? 0) < 1) {
            return response()->json(['ok' => false, 'message' => 'Main Branch Finance must confirm the invoice and assign a budget before Owner approval.'], 409);
        }

        DB::beginTransaction();
        try {
            $branch->approved_by = $user->id;
            $branch->approved_at = now();
            $branch->rejected_at = null;
            $branch->is_active = 0;
            $branch->save();

            $activationError = $this->activateBranchAfterApprovals($branch);
            if ($activationError !== null) {
                DB::rollBack();
                return response()->json(['ok' => false, 'message' => $activationError], 422);
            }

            PositionOpenRequest::where('branch_id', $branch->id)
                ->where('status', 'Pending')
                ->where('notes', 'like', 'Automatically broadcast during branch creation%')
                ->update([
                    'status' => 'Approved',
                    'approved_by_user_id' => $user->id,
                    'approved_at' => now(),
                ]);

            DB::commit();
            return response()->json(['ok' => true, 'message' => 'Branch approved and activated.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveBranchRequest error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to approve branch'], 500);
        }
    }

    private function activateBranchAfterApprovals(Branch $branch): ?string
    {
        if (! $branch->approved_by || ! $branch->finance_confirmed_by || ! $branch->finance_confirmed_at) {
            return 'Both Owner and Main Branch Finance approvals are required before branch activation.';
        }

        $allocation = (int) ($branch->budget ?? 0);
        if ($allocation < 1) {
            return 'Main Branch Finance must assign a budget before branch activation.';
        }
        $invoiceTotal = round((float) ($branch->total_investment ?? 0), 2);
        $totalDeduction = round($allocation + $invoiceTotal, 2);

        $mainBranch = Branch::where('is_main_branch', 1)->lockForUpdate()->first();
        if (! $mainBranch) {
            return 'Main branch not found for budget allocation.';
        }

        $mainBudget = (float) ($mainBranch->budget ?? 0);
        if ($mainBudget < $totalDeduction) {
            return 'Insufficient Main Branch budget for the branch budget and invoice total.';
        }

        $mainBranch->budget = round($mainBudget - $totalDeduction, 2);
        $mainBranch->save();

        $branch->approval_status = 'approved';
        $branch->is_active = true;
        $branch->save();
        User::where('branch_id', $branch->id)->update(['is_active' => true]);

        return null;
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
        if ($branch->approval_status !== 'pending_owner') {
            return response()->json(['ok' => false, 'message' => 'Branch must be confirmed by Main Branch Finance before Owner review.'], 409);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', \Illuminate\Validation\Rule::in(self::BRANCH_REJECTION_REASONS)],
            'reason_details' => ['required_if:reason,Other', 'nullable', 'string', 'max:1000'],
        ]);
        $rejectionReason = $validated['reason'] === 'Other'
            ? 'Other: ' . trim($validated['reason_details'])
            : $validated['reason'];

        DB::beginTransaction();
        try {
            $branch->approval_status = 'rejected';
            $branch->approved_by = $user->id;
            $branch->approved_at = null;
            $branch->rejected_at = now();
            $branch->rejection_reason = $rejectionReason;
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
        $isMainBranchFinance = in_array($roleUpper, ['SUPER_ADMIN', 'SUPERADMIN'], true);
        if (! $isMainBranchFinance && in_array($roleUpper, ['MANAGER', 'MANAGER_FINANCE'], true) && strtoupper($user->department ?? '') === 'FINANCE') {
            $b = Branch::find($user->branch_id);
            $isMainBranchFinance = (bool) ($b && ($b->is_main_branch ?? false));
        }
        if (! $isMainBranchFinance) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $branches = Branch::where('approval_status', 'pending_finance')
            ->orderBy('created_at', 'desc')
            ->get();
        $mainBranch = Branch::where('is_main_branch', 1)->first();
        $reservedBudget = (float) Branch::where('approval_status', 'pending_owner')
            ->whereNotNull('finance_confirmed_at')
            ->sum('budget');
        $reservedInvoiceTotal = (float) Branch::where('approval_status', 'pending_owner')
            ->whereNotNull('finance_confirmed_at')
            ->sum('total_investment');
        $availableMainBranchBudget = $mainBranch
            ? max(0, round((float) ($mainBranch->budget ?? 0) - $reservedBudget - $reservedInvoiceTotal, 2))
            : 0;

        $requesterIds = $branches->pluck('requested_by')->filter()->unique()->values();
        $requesters = User::whereIn('id', $requesterIds)->get()->keyBy('id');

        $result = $branches->map(function ($branch) use ($requesters, $availableMainBranchBudget) {
            $requester = $branch->requested_by ? ($requesters[$branch->requested_by] ?? null) : null;
            $totalInvestment = $branch->total_investment === null ? null : (float) $branch->total_investment;
            return [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'address' => $branch->address,
                'budget' => round((float) ($branch->budget ?? 0), 2),
                'initial_budget' => round((float) ($branch->initial_budget ?? $branch->budget ?? 0), 2),
                'latitude' => $branch->latitude,
                'longitude' => $branch->longitude,
                'square_meters' => $branch->square_meters,
                'geofencing_radius' => $branch->geofencing_radius,
                'permit_bills' => $branch->permit_bills ?? [],
                'construction_costs' => $branch->construction_costs ?? [],
                'equipment_costs' => $branch->equipment_costs ?? [],
                'cost_attachments' => $this->costAttachmentsForResponse($branch),
                'total_investment' => $totalInvestment,
                'available_main_branch_budget' => $availableMainBranchBudget,
                'max_assignable_budget' => max(0, (int) floor(round($availableMainBranchBudget - ($totalInvestment ?? 0), 2))),
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
        $isMainBranchFinance = in_array($roleUpper, ['SUPER_ADMIN', 'SUPERADMIN'], true);
        if (! $isMainBranchFinance && in_array($roleUpper, ['MANAGER', 'MANAGER_FINANCE'], true) && strtoupper($user->department ?? '') === 'FINANCE') {
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

        $validated = $request->validate([
            'budget' => ['required', 'integer', 'min:1'],
        ]);
        $budget = (int) $validated['budget'];

        DB::beginTransaction();
        try {
            $mainBranch = Branch::where('is_main_branch', 1)->lockForUpdate()->first();
            if (! $mainBranch) {
                DB::rollBack();
                return response()->json(['ok' => false, 'message' => 'Main branch not found for budget allocation.'], 422);
            }

            $branch = Branch::whereKey($id)->lockForUpdate()->first();
            if (! $branch || ($branch->approval_status ?? 'approved') !== 'pending_finance') {
                DB::rollBack();
                return response()->json(['ok' => false, 'message' => 'Branch is no longer pending finance confirmation.'], 409);
            }

            $reservedBudget = (float) Branch::where('approval_status', 'pending_owner')
                ->whereNotNull('finance_confirmed_at')
                ->sum('budget');
            $reservedInvoiceTotal = (float) Branch::where('approval_status', 'pending_owner')
                ->whereNotNull('finance_confirmed_at')
                ->sum('total_investment');
            $availableBudget = max(0, round((float) ($mainBranch->budget ?? 0) - $reservedBudget - $reservedInvoiceTotal, 2));
            $invoiceTotal = round((float) ($branch->total_investment ?? 0), 2);
            $maxAssignableBudget = max(0, (int) floor(round($availableBudget - $invoiceTotal, 2)));
            if ($budget > $maxAssignableBudget) {
                DB::rollBack();
                return response()->json([
                    'ok' => false,
                    'message' => 'The branch budget and invoice total cannot exceed the available Main Branch budget.',
                    'errors' => [
                        'budget' => ["The maximum branch budget after the invoice is {$maxAssignableBudget} PHP."],
                    ],
                    'available_main_branch_budget' => $availableBudget,
                    'max_assignable_budget' => $maxAssignableBudget,
                ], 422);
            }

            $branch->is_active = 0;
            $branch->budget = $budget;
            $branch->initial_budget = $budget;
            $branch->finance_confirmed_by = $user->id;
            $branch->finance_confirmed_at = now();
            if ($branch->approved_by) {
                $activationError = $this->activateBranchAfterApprovals($branch);
                if ($activationError !== null) {
                    DB::rollBack();
                    return response()->json(['ok' => false, 'message' => $activationError], 422);
                }
            } else {
                $branch->approval_status = 'pending_owner';
            }
            $branch->save();
            if (! $branch->is_active) {
                User::where('branch_id', $branch->id)->update(['is_active' => 0]);
            }

            DB::commit();
            return response()->json([
                'ok' => true,
                'message' => $branch->is_active
                    ? 'Invoice and budget confirmed. Owner approval was already recorded, so the branch is now active.'
                    : 'Budget confirmed. Awaiting owner approval.',
            ]);
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
        $isMainBranchFinance = in_array($roleUpper, ['SUPER_ADMIN', 'SUPERADMIN'], true);
        if (! $isMainBranchFinance && in_array($roleUpper, ['MANAGER', 'MANAGER_FINANCE'], true) && strtoupper($user->department ?? '') === 'FINANCE') {
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

        $validated = $request->validate([
            'reason' => ['required', 'string', \Illuminate\Validation\Rule::in(self::BRANCH_REJECTION_REASONS)],
            'reason_details' => ['required_if:reason,Other', 'nullable', 'string', 'max:1000'],
        ]);
        $rejectionReason = $validated['reason'] === 'Other'
            ? 'Other: ' . trim($validated['reason_details'])
            : $validated['reason'];

        DB::beginTransaction();
        try {
            $branch->approval_status = 'rejected';
            $branch->approved_by = null;
            $branch->approved_at = null;
            $branch->rejected_at = now();
            $branch->rejection_reason = $rejectionReason;
            $branch->is_active = 0;
            $branch->save();

            User::where('branch_id', $branch->id)->update(['is_active' => 0]);

            DB::commit();
            return response()->json(['ok' => true, 'message' => 'Budget allocation rejected.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectFinanceBranchRequest error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to reject budget allocation'], 500);
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

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $branch->is_active = 0;
            $branch->deactivation_reason = trim($validated['reason']);
            $branch->save();

            Log::info('Branch deactivated', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'deactivated_by' => $user->username,
                'reason' => $branch->deactivation_reason,
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

        if (($branch->approval_status ?? 'approved') !== 'approved') {
            return response()->json([
                'ok' => false,
                'message' => 'Branch must be approved by main branch finance and the owner before it can be reactivated.',
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
