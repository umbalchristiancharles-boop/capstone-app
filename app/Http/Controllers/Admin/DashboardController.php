<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Dish;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get date range based on filter
     */
    private function getDateRange(string $range): array
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'yesterday':
                return [
                    'start' => $now->copy()->subDay()->startOfDay(),
                    'end' => $now->copy()->subDay()->endOfDay(),
                ];
            case 'thisWeek':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                ];
            case 'lastWeek':
                return [
                    'start' => $now->copy()->subWeek()->startOfWeek(),
                    'end' => $now->copy()->subWeek()->endOfWeek(),
                ];
            case 'thisMonth':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
            case 'lastMonth':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth(),
                ];
            default:
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
        }
    }

    /**
     * Get status label for display
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Pending',
            'in_kitchen' => 'In Kitchen',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }

    /**
     * Main dashboard index with all metrics
     */
    public function index(Request $request)
    {
        $range = $request->get('range', 'today');
        $dates = $this->getDateRange($range);
        $user = $request->user();
        $isSuperAdmin = in_array(strtoupper($user->role ?? ''), ['SUPER_ADMIN', 'SUPERADMIN']);
        $branchId = $isSuperAdmin && $request->filled('branch_id')
            ? (int) $request->query('branch_id')
            : (!$isSuperAdmin ? $user->branch_id : null);

        // Count branches (active only)
        $branchesCount = Branch::where('is_active', 1)
            ->when($branchId, fn ($query) => $query->where('id', $branchId))
            ->count();

        // Get all branches for reference
        $branches = Branch::where('is_active', 1)->get(['id', 'name', 'code']);

        // Count employees (active STAFF, BRANCH_MANAGER, HR)
        $employeeRoles = ['STAFF', 'BRANCH_MANAGER', 'HR'];
        $staffCount = User::whereIn('role', $employeeRoles)
            ->where('is_active', 1)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->count();

        // Orders with date range filter
        $ordersQuery = Order::where(function ($query) use ($dates) {
                $query->whereBetween('ordered_at', [$dates['start'], $dates['end']])
                    ->orWhere(function ($fallback) use ($dates) {
                        $fallback->whereNull('ordered_at')
                            ->whereBetween('created_at', [$dates['start'], $dates['end']]);
                    });
            })
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
        $ordersCount = $ordersQuery->count();

        // Completed orders
        $completedCount = (clone $ordersQuery)->where('status', 'completed')->count();

        // Pending orders
        $pendingStatuses = ['pending', 'in_kitchen', 'preparing', 'ready'];
        $pendingCount = (clone $ordersQuery)->whereIn('status', $pendingStatuses)->count();

        // Total sales
        $totalSales = (clone $ordersQuery)
            ->where('status', 'completed')
            ->sum('grand_total');
        $salesFormatted = '₱' . number_format($totalSales, 2);

        // Recent orders
        $recentOrders = Order::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderByRaw('COALESCE(ordered_at, created_at) desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'code' => $order->order_code ?? 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'customer' => $order->customer_name ?? 'Guest',
                    'status' => $order->status,
                    'statusLabel' => $this->getStatusLabel($order->status),
                    'total' => '₱' . number_format($order->grand_total, 2),
                    'ordered_at' => $order->ordered_at ? $order->ordered_at->format('M d, Y H:i') : 'N/A',
                ];
            });

        // Production Queue
        $productionQueue = Order::whereIn('status', ['pending', 'in_kitchen', 'preparing', 'ready'])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->with('items')
            ->orderByRaw('COALESCE(ordered_at, created_at) asc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                $badgeClass = match($order->status) {
                    'completed' => 'badge--success',
                    'in_kitchen', 'preparing' => 'badge--warning',
                    'ready' => 'badge--info',
                    default => 'badge--info'
                };
                return [
                    'id' => $order->id,
                    'title' => $order->order_code ?? 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'meta' => ($order->customer_name ?? 'Guest') . ' - ₱' . number_format($order->grand_total, 2),
                    'products' => $order->items->map(fn ($item) => [
                        'name' => $item->product_name ?? 'Product',
                        'quantity' => (int) $item->quantity,
                    ])->values(),
                    'badgeLabel' => $this->getStatusLabel($order->status),
                    'badgeClass' => $badgeClass,
                ];
            });

        $salesByProduct = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->where(function ($query) use ($dates) {
                $query->whereBetween('orders.ordered_at', [$dates['start'], $dates['end']])
                    ->orWhere(function ($fallback) use ($dates) {
                        $fallback->whereNull('orders.ordered_at')
                            ->whereBetween('orders.created_at', [$dates['start'], $dates['end']]);
                    });
            })
            ->when($branchId, fn ($query) => $query->where('orders.branch_id', $branchId))
            ->select(
                DB::raw('TRIM(UPPER(order_items.product_name)) as product_key'),
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.subtotal) as revenue')
            )
            ->groupBy(DB::raw('TRIM(UPPER(order_items.product_name))'))
            ->get()
            ->keyBy('product_key');

        // Product performance and complete inventory status for the dashboard.
        $productRows = Product::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->get([
                'id', 'name', 'category', 'brand', 'sku', 'price', 'stock', 'real_stock',
                'min_stock', 'is_active', 'is_published', 'is_kitchen_dish', 'is_dish_product',
                'branch_id', 'expires_at', 'image_path',
            ]);

        // Supplier records can represent one sellable product. Group by SKU when
        // available, otherwise by normalized name, matching Product's stock rules.
        $productCatalog = $productRows
            ->groupBy(function ($product) {
                return !empty($product->sku)
                    ? 'sku:' . trim(strtoupper($product->sku))
                    : 'name:' . trim(strtoupper($product->name));
            })
            ->map(function ($group) use ($salesByProduct, $branches) {
                $product = $group
                    ->sortByDesc(fn ($item) => ((int) $item->is_published * 2) + (int) $item->is_active)
                    ->first();
                $nameKey = trim(strtoupper((string) $product->name));
                $sales = $salesByProduct->get($nameKey);
                $activeRows = $group->where('is_active', true);
                $stock = (int) $activeRows->sum(fn ($item) => (int) $item->stock);
                $minStock = (int) $group->max('min_stock');
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'brand' => $product->brand,
                    'sku' => $product->sku,
                    'price' => (float) ($group->where('is_published', true)->where('price', '>', 0)->sortByDesc('price')->first()?->price ?? $product->price),
                    'stock' => $stock,
                    'real_stock' => $stock,
                    'min_stock' => $minStock,
                    'is_active' => $activeRows->isNotEmpty(),
                    'is_published' => $activeRows->where('is_published', true)->isNotEmpty(),
                    'is_kitchen_dish' => (bool) $product->is_kitchen_dish,
                    'is_dish_product' => (bool) $product->is_dish_product,
                    'branch' => $branches->firstWhere('id', $product->branch_id)?->name ?? 'N/A',
                    'expires_at' => $product->expires_at?->format('M d, Y'),
                    'units_sold' => (int) ($sales->units_sold ?? 0),
                    'revenue' => (float) ($sales->revenue ?? 0),
                    'image_url' => $product->image_url,
                    'supplier_records' => $group->count(),
                    'suppliers' => $group->pluck('supplier_name')->filter()->unique()->values(),
                ];
            });

        $topProducts = $productCatalog->sortByDesc('units_sold')->take(5)->values();

        $dishDetails = Dish::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->with('ingredients.product')
            ->orderBy('name')
            ->get()
            ->map(function ($dish) use ($productCatalog) {
                $dishProduct = $productCatalog->firstWhere('name', $dish->name);
                return [
                    'id' => $dish->id,
                    'name' => $dish->name,
                    'status' => $dish->status,
                    'approval_status' => $dish->approval_status,
                    'price' => $dishProduct['price'] ?? null,
                    'stock' => $dishProduct['real_stock'] ?? 0,
                    'units_sold' => $dishProduct['units_sold'] ?? 0,
                    'revenue' => $dishProduct['revenue'] ?? 0,
                    'ingredients' => $dish->ingredients->map(fn ($ingredient) => [
                        'name' => $ingredient->name,
                        'quantity' => $ingredient->per_serving,
                        'unit' => $ingredient->unit,
                        'available_stock' => $ingredient->product?->real_stock ?? $ingredient->product?->stock ?? null,
                    ])->values(),
                ];
            })->values();

        // Low Stock Items
        $lowStockItems = $productCatalog
            ->filter(fn ($product) => $product['stock'] > 0 && $product['stock'] < 10)
            ->sortBy('stock')
            ->take(10)
            ->map(function ($product) {
                return [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'stock' => $product['stock'],
                    'branch' => $product['branch'],
                ];
            });

        $productSummary = [
            'total' => $productCatalog->count(),
            'active' => $productCatalog->where('is_active', true)->count(),
            'published' => $productCatalog->where('is_published', true)->count(),
            'disabled' => $productCatalog->where('is_active', false)->count(),
            'low_stock' => $productCatalog->filter(fn ($product) => $product['stock'] <= $product['min_stock'])->count(),
            'units_sold' => $productCatalog->sum('units_sold'),
            'revenue' => $productCatalog->sum('revenue'),
            'dishes' => $dishDetails->count(),
        ];

        // Staff Activity
        $recentActivity = User::whereIn('role', $employeeRoles)
            ->where('is_active', 1)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->latest('updated_at')
            ->limit(10)
            ->get(['id', 'full_name', 'role', 'updated_at', 'branch_id'])
            ->map(function ($user) use ($branches) {
                $branchName = $branches->firstWhere('id', $user->branch_id)?->name ?? 'N/A';
                return [
                    'name' => $user->full_name ?? 'N/A',
                    'role' => $user->role,
                    'branch' => $branchName,
                    'last_active' => $user->updated_at ? $user->updated_at->format('M d, Y H:i') : 'N/A',
                ];
            });

        // All branches
        $allBranches = Branch::where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'address']);

        return response()->json([
            'branches_count' => $branchesCount,
            'orders_count' => $ordersCount,
            'staff_count' => $staffCount,
            'orders' => $ordersCount,
            'completed' => $completedCount,
            'pending' => $pendingCount,
            'sales' => $salesFormatted,
            'sales_raw' => $totalSales,
            'recent_orders' => $recentOrders,
            'production_queue' => $productionQueue,
            'top_products' => $topProducts,
            'product_catalog' => $productCatalog->values(),
            'dish_details' => $dishDetails,
            'product_summary' => $productSummary,
            'low_stock_items' => $lowStockItems,
            'recent_activity' => $recentActivity,
            'branches' => $allBranches,
            'range' => $range,
            'date_range' => [
                'start' => $dates['start']->format('Y-m-d H:i:s'),
                'end' => $dates['end']->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
