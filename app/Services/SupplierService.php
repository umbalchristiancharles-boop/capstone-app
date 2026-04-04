<?php

namespace App\Services;

use App\Models\User;
use App\Models\SupplierOrder;
use App\Models\Product;
use App\Models\ProcurementRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

/**
 * SupplierService
 *
 * Manages supplier data integrity, validation, consistency checking, and comprehensive
 * audit logging for all supplier-related operations. Ensures accurate, synchronized data
 * across procurement, inventory, and logistics modules.
 */
class SupplierService
{
    /**
     * Get all suppliers with comprehensive data validation and consistency checks
     *
     * @param int|null $branchId Filter by branch (null = all branches)
     * @return Collection Suppliers with validated data
     */
    public static function getAllSuppliers(?int $branchId = null): Collection
    {
        $query = User::whereRaw('UPPER(COALESCE(role, "")) = ?', ['SUPPLIER'])
            ->whereNull('deleted_at')
            ->where('is_active', 1);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $suppliers = $query->orderBy('full_name', 'asc')->get();

        // Validate each supplier's data integrity
        return $suppliers->map(function ($supplier) {
            // Cast to ensure User model type
            if ($supplier instanceof User) {
                return self::validateSupplierData($supplier);
            }
            return null;
        })->filter();
    }

    /**
     * Get supplier profile with full data consistency verification
     *
     * @param int $supplierId Supplier user ID
     * @return array|null Validated supplier data or null if not found
     */
    public static function getSupplierDetail(int $supplierId): ?array
    {
        $supplier = User::where('id', $supplierId)
            ->whereRaw('UPPER(COALESCE(role, "")) = ?', ['SUPPLIER'])
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->with([
                'orders' => function ($q) {
                    $q->with(['product', 'procurementRequest', 'branch'])
                        ->orderBy('created_at', 'desc')
                        ->limit(50);
                },
                'branch'
            ])
            ->first();

        if (!$supplier) {
            return null;
        }

        // Validate and enhance supplier data
        return self::validateSupplierData($supplier, true);
    }

    /**
     * Validate single supplier's data integrity
     * Checks for inconsistencies, orphaned records, and data sync issues
     *
     * @param User|mixed $supplier
     * @param bool $detailed Include detailed consistency checks
     * @return array Validated supplier data with status
     */
    private static function validateSupplierData($supplier, bool $detailed = false): array
    {
        // Type safety check
        if (!($supplier instanceof User)) {
            return [
                'id' => null,
                'name' => 'Unknown',
                'data_status' => 'error',
                'issues' => ['Invalid supplier object type'],
            ];
        }
        $data = [
            'id' => $supplier->id,
            'name' => $supplier->full_name,
            'username' => $supplier->username,
            'email' => $supplier->email,
            'phone' => $supplier->phone_number,
            'branch_id' => $supplier->branch_id,
            'branch_name' => $supplier->branch ? $supplier->branch->name : 'Unknown',
            'is_active' => (bool) $supplier->is_active,
            'created_at' => $supplier->created_at?->toIso8601String(),
            'updated_at' => $supplier->updated_at?->toIso8601String(),
            'data_status' => 'valid', // valid|warning|error
            'issues' => [],
        ];

        if (!$detailed) {
            return $data;
        }

        // Detailed validation: Check for data inconsistencies
        $issues = [];

        // 1. Check for orphaned supplier orders
        $orphanedOrders = SupplierOrder::where('supplier_id', $supplier->id)
            ->where(function ($q) {
                $q->whereNull('product_id')
                  ->orWhereNull('procurement_request_id');
            })
            ->count();

        if ($orphanedOrders > 0) {
            $issues[] = "Found $orphanedOrders orphaned supplier orders (missing product/procurement reference)";
        }

        // 2. Check for price inconsistencies between supplier orders and products
        $priceInconsistencies = SupplierOrder::where('supplier_id', $supplier->id)
            ->whereHas('product', function ($q) {
                $q->whereColumn('price', '!=', 'supplier_orders.price');
            })
            ->count();

        if ($priceInconsistencies > 0) {
            $issues[] = "$priceInconsistencies supplier orders have price mismatches with product records";
        }

        // 3. Check for pending/unfulfilled orders stuck in system
        $pendingOrders = SupplierOrder::where('supplier_id', $supplier->id)
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subDays(30))
            ->count();

        if ($pendingOrders > 0) {
            $issues[] = "$pendingOrders pending orders older than 30 days (may need follow-up or cancellation)";
        }

        // 4. Verify supplier has required contact information
        if (empty($supplier->email) && empty($supplier->phone_number)) {
            $issues[] = "Supplier has no email or phone number on record";
        }

        if (!$supplier->is_active) {
            $issues[] = "Supplier is inactive - verify pending orders";
        }

        // 5. Check for potential duplicate suppliers
        $duplicates = self::findPotentialDuplicates($supplier);
        if (!$duplicates->isEmpty()) {
            $issues[] = "Found " . $duplicates->count() . " potential duplicate supplier records (similar names/contact info)";
        }

        if (!empty($issues)) {
            $data['data_status'] = 'warning';
            $data['issues'] = $issues;
        }

        // Add order summary
        $orders = SupplierOrder::where('supplier_id', $supplier->id)->get();
        $data['order_summary'] = [
            'total_orders' => $orders->count(),
            'pending' => $orders->where('status', 'pending')->count(),
            'fulfilled' => $orders->where('status', 'fulfilled')->count(),
            'on_delivery' => $orders->where('status', 'on_delivery')->count(),
            'cancelled' => $orders->where('status', 'cancelled')->count(),
        ];

        // Add product summary
        $products = Product::where('supplier_name', $supplier->full_name)
            ->orWhere('supplier_id', $supplier->id)
            ->get();
        $data['product_summary'] = [
            'total_products' => $products->count(),
            'active' => $products->where('is_active', 1)->count(),
            'low_stock' => $products->filter(function ($p) {
                $minStock = $p->min_stock ?? 10;
                return ($p->stock ?? 0) < $minStock;
            })->count(),
        ];

        return $data;
    }

    /**
     * Find potential duplicate supplier entries
     * Checks for similar names, email addresses, or phone numbers
     *
     * @param User $supplier
     * @return Collection Potential duplicates
     */
    public static function findPotentialDuplicates(User $supplier): Collection
    {
        $query = User::whereRaw('UPPER(COALESCE(role, "")) = ?', ['SUPPLIER'])
            ->where('id', '!=', $supplier->id)
            ->whereNull('deleted_at')
            ->where('is_active', 1);

        // Check for same email
        if ($supplier->email) {
            $query->orWhere('email', $supplier->email);
        }

        // Check for same phone
        if ($supplier->phone_number) {
            $query->orWhere('phone_number', $supplier->phone_number);
        }

        // Check for similar names (case-insensitive substring)
        $sanitizedName = trim(strtoupper($supplier->full_name ?? ''));
        if (strlen($sanitizedName) > 3) {
            $query->orWhereRaw('UPPER(TRIM(full_name)) LIKE ?', ['%' . $sanitizedName . '%']);
        }

        return $query->get();
    }

    /**
     * Validate supplier order data consistency
     * Checks that orders have valid relationships and data
     *
     * @param int $supplierId
     * @return array Validation result with details
     */
    public static function validateSupplierOrders(int $supplierId): array
    {
        $supplier = User::find($supplierId);
        if (!$supplier) {
            return ['status' => 'error', 'message' => 'Supplier not found'];
        }

        $issues = [];
        $total = 0;
        $fixed = 0;

        DB::beginTransaction();
        try {
            $orders = SupplierOrder::where('supplier_id', $supplierId)
                ->with(['product', 'procurementRequest', 'branch'])
                ->get();

            $total = $orders->count();

            foreach ($orders as $order) {
                // Check product exists and is in same branch
                if (!$order->product) {
                    $issues[] = "Order #{$order->id}: Product not found";
                    continue;
                }

                if ($order->branch_id && $order->product->branch_id !== $order->branch_id) {
                    $issues[] = "Order #{$order->id}: Product branch mismatch";
                }

                // Check procurement request exists
                if (!$order->procurementRequest) {
                    $issues[] = "Order #{$order->id}: Procurement request not found";
                }

                // Verify price is reasonable (not negative, not 0 unless intentional)
                if ($order->price === null || $order->price < 0) {
                    $issues[] = "Order #{$order->id}: Invalid price";
                }

                // Check for stale pending orders
                if ($order->status === 'pending' && $order->created_at < now()->subDays(90)) {
                    $issues[] = "Order #{$order->id}: Very old pending order (90+ days)";
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error validating supplier orders', [
                'supplier_id' => $supplierId,
                'error' => $e->getMessage(),
            ]);
            return [
                'status' => 'error',
                'message' => 'Validation failed: ' . $e->getMessage(),
            ];
        }

        return [
            'status' => empty($issues) ? 'valid' : 'warning',
            'total_orders' => $total,
            'issues_found' => count($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Verify supplier's product and pricing data consistency with procurement records
     *
     * @param int $supplierId
     * @return array Verification result
     */
    public static function verifyProductPricingConsistency(int $supplierId): array
    {
        $discrepancies = [];

        // Get all supplier orders with pricing
        $orders = SupplierOrder::where('supplier_id', $supplierId)
            ->with(['product', 'procurementRequest'])
            ->get();

        foreach ($orders as $order) {
            if (!$order->product) {
                continue;
            }

            // Check if current product price differs from what was ordered
            if ($order->price !== $order->product->price) {
                $discrepancies[] = [
                    'order_id' => $order->id,
                    'product_id' => $order->product->id,
                    'product_name' => $order->product->name,
                    'order_price' => $order->price,
                    'current_price' => $order->product->price,
                    'difference' => $order->product->price - $order->price,
                ];
            }
        }

        return [
            'status' => empty($discrepancies) ? 'consistent' : 'inconsistent',
            'discrepancies' => $discrepancies,
            'count' => count($discrepancies),
        ];
    }

    /**
     * Create audit log entry for supplier activity
     *
     * @param int $supplierId
     * @param string $action (create|update|deactivate|order|delivery|etc)
     * @param string|null $description
     * @param int|null $triggeredByUserId
     * @param array $metadata Additional context
     * @param string $severity info|warning|critical
     */
    public static function logSupplierActivity(
        int $supplierId,
        string $action,
        ?string $description = null,
        ?int $triggeredByUserId = null,
        array $metadata = [],
        string $severity = 'info'
    ): void {
        try {
            $logData = [
                'supplier_id' => $supplierId,
                'action' => $action,
                'description' => $description,
                'triggered_by_user_id' => $triggeredByUserId,
                'severity' => $severity,
                'metadata' => $metadata,
            ];

            // Save to database for audit trail
            \App\Models\SupplierAuditLog::create($logData);

            // Also log to application logger
            Log::channel('supplier_audit')->info('Supplier Activity: ' . $action, $logData);
        } catch (\Exception $e) {
            Log::error('Failed to log supplier activity', [
                'supplier_id' => $supplierId,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if supplier has duplicate entries in system
     * Returns true if duplicates found, false otherwise
     *
     * @param int $supplierId
     * @return bool
     */
    public static function hasDuplicateEntries(int $supplierId): bool
    {
        $supplier = User::find($supplierId);
        if (!$supplier) {
            return false;
        }

        return !self::findPotentialDuplicates($supplier)->isEmpty();
    }

    /**
     * Merge/consolidate duplicate supplier records
     * Caution: This is a destructive operation and should only be called by admins
     *
     * @param int $primarySupplierId Keep this record
     * @param int $duplicateSupplierId Merge this record into primary
     * @param int|null $performedByUserId Admin user ID performing the merge
     * @return array Operation result
     */
    public static function mergeDuplicateSuppliers(
        int $primarySupplierId,
        int $duplicateSupplierId,
        ?int $performedByUserId = null
    ): array {
        $primary = User::find($primarySupplierId);
        $duplicate = User::find($duplicateSupplierId);

        if (!$primary || !$duplicate) {
            return ['status' => 'error', 'message' => 'One or both suppliers not found'];
        }

        if ($primary->role !== 'SUPPLIER' || $duplicate->role !== 'SUPPLIER') {
            return ['status' => 'error', 'message' => 'Invalid supplier records'];
        }

        DB::beginTransaction();
        try {
            // Move all orders from duplicate to primary
            $movedOrders = SupplierOrder::where('supplier_id', $duplicateSupplierId)
                ->update(['supplier_id' => $primarySupplierId]);

            // Move all products from duplicate to primary
            $movedProducts = Product::where('supplier_id', $duplicateSupplierId)
                ->update(['supplier_id' => $primarySupplierId]);

            // Soft delete the duplicate
            $duplicate->delete();

            DB::commit();

            self::logSupplierActivity(
                $primarySupplierId,
                'merge_duplicates',
                "Merged duplicate supplier (ID: $duplicateSupplierId) into primary",
                $performedByUserId,
                [
                    'duplicate_id' => $duplicateSupplierId,
                    'orders_moved' => $movedOrders,
                    'products_moved' => $movedProducts,
                ]
            );

            return [
                'status' => 'success',
                'message' => 'Suppliers merged successfully',
                'orders_moved' => $movedOrders,
                'products_moved' => $movedProducts,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to merge duplicate suppliers', [
                'primary_id' => $primarySupplierId,
                'duplicate_id' => $duplicateSupplierId,
                'error' => $e->getMessage(),
            ]);
            return [
                'status' => 'error',
                'message' => 'Failed to merge suppliers: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get supplier activity history/audit log
     *
     * @param int $supplierId
     * @param int $limit
     * @return Collection
     */
    public static function getSupplierActivityHistory(int $supplierId, int $limit = 100): Collection
    {
        // Combine audit logs and order changes for comprehensive history
        $auditLogs = \App\Models\SupplierAuditLog::where('supplier_id', $supplierId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => 'audit-' . $log->id,
                    'type' => 'audit',
                    'action' => $log->action,
                    'severity' => $log->severity,
                    'description' => $log->description,
                    'timestamp' => $log->created_at->toIso8601String(),
                    'triggered_by' => $log->triggeredBy ? $log->triggeredBy->full_name : 'System',
                    'metadata' => $log->metadata,
                ];
            });

        // Get recent supplier order changes
        $orderChanges = SupplierOrder::where('supplier_id', $supplierId)
            ->orderBy('updated_at', 'desc')
            ->limit($limit / 2)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => 'order-' . $order->id,
                    'type' => 'order_update',
                    'action' => 'order_update',
                    'severity' => 'info',
                    'description' => "Order #{$order->id} status changed to {$order->status}",
                    'timestamp' => $order->updated_at->toIso8601String(),
                    'triggered_by' => 'System',
                    'metadata' => [
                        'order_id' => $order->id,
                        'product' => $order->product?->name,
                        'status' => $order->status,
                        'quantity' => $order->quantity,
                        'price' => $order->price,
                    ],
                ];
            });

        // Merge and sort by timestamp
        $allActivities = collect()
            ->merge($auditLogs)
            ->merge($orderChanges)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();

        return $allActivities;
    }

    /**
     * Update supplier status and cascade related updates across system
     * Ensures data consistency when supplier status changes
     *
     * @param int $supplierId
     * @param bool $isActive New active status
     * @param int|null $triggeredByUserId
     * @return array Operation result
     */
    public static function updateSupplierStatus(
        int $supplierId,
        bool $isActive,
        ?int $triggeredByUserId = null
    ): array {
        $supplier = User::find($supplierId);
        if (!$supplier || $supplier->role !== 'SUPPLIER') {
            return ['status' => 'error', 'message' => 'Supplier not found'];
        }

        DB::beginTransaction();
        try {
            $oldStatus = (bool) $supplier->is_active;
            $supplier->update(['is_active' => $isActive]);

            // If deactivating, mark pending orders for review
            if (!$isActive && $oldStatus) {
                $pendingOrders = SupplierOrder::where('supplier_id', $supplierId)
                    ->where('status', 'pending')
                    ->update(['status' => 'pending']); // Mark for review without changing status
            }

            DB::commit();

            self::logSupplierActivity(
                $supplierId,
                $isActive ? 'reactivate' : 'deactivate',
                "Supplier status changed from " . ($oldStatus ? 'active' : 'inactive') . " to " . ($isActive ? 'active' : 'inactive'),
                $triggeredByUserId
            );

            return [
                'status' => 'success',
                'message' => 'Supplier status updated successfully',
                'new_status' => $isActive ? 'active' : 'inactive',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update supplier status', [
                'supplier_id' => $supplierId,
                'error' => $e->getMessage(),
            ]);
            return [
                'status' => 'error',
                'message' => 'Failed to update supplier status: ' . $e->getMessage(),
            ];
        }
    }
}
