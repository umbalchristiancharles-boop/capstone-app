<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ProcurementRequest;
use App\Models\LogisticsTransaction;
use App\Models\SupplierOrder;
use App\Models\BudgetRequest;
use App\Models\Dish;
use App\Models\Branch;
use App\Models\PriceMarkupRequest;
use App\Models\Message;
use App\Models\Announcement;
use App\Models\ProductRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PanelNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }

        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        $branchId = $user->branch_id;
        $readAt = fn (string $category) => DB::table('panel_notification_reads')
            ->where('user_id', $user->id)
            ->where('category', $category)
            ->value('read_at');

        $isGlobalRole = in_array($role, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER'], true);
        if ($isGlobalRole) {
            $branchId = null;
        }

        $counts = [
            'admin' => 0,
            'finance' => 0,
            'inventory' => 0,
            'hr' => 0,
            'logistics' => 0,
            'procurement' => 0,
            'kitchen' => 0,
            'cashier' => 0,
            'reports' => 0,
            'supplier' => 0,
        ];

        $extras = [
            'branchPendingOwner' => 0,
            'branchPendingFinance' => 0,
            'priceMarkupPending' => 0,
            'unreadMessages' => 0,
            'announcements' => 0,
            'ownerProductRequests' => 0,
        ];

        $permissions = is_array($user->permissions ?? null) ? $user->permissions : [];
        $configuredModules = collect($permissions['modules'] ?? [])
            ->map(fn ($module) => strtolower((string) $module))
            ->values()
            ->all();

        $hasModule = function (string $module) use ($role, $dept, $configuredModules, $isGlobalRole): bool {
            if ($isGlobalRole) return true;
            if ($role === 'CUSTOM') return in_array($module, $configuredModules, true);

            $departmentModules = [
                'ADMIN' => ['admin', 'cashier'],
                'FINANCE' => ['finance'],
                'INVENTORY' => ['inventory'],
                'LOGISTICS' => ['logistics'],
                'PROCUREMENT' => ['procurement'],
                'KITCHEN' => ['kitchen'],
                'CASHIER' => ['cashier'],
                'HR' => ['hr'],
            ];

            $roleModules = [
                'ADMIN' => ['admin', 'cashier'],
                'CASHIER' => ['cashier'],
                'SUPPLIER' => ['supplier'],
                'HR' => ['hr'],
            ];

            return in_array($module, $departmentModules[$dept] ?? [], true)
                || in_array($module, $roleModules[$role] ?? [], true)
                || ($role === 'SUPPLIER' && $module === 'supplier');
        };

        // Admin + Cashier pending orders
        $orderPendingQuery = Order::whereIn('status', ['pending', 'in_kitchen']);
        if ($branchId) {
            $orderPendingQuery->where('branch_id', $branchId);
        }
        if ($hasModule('admin')) {
            $counts['admin'] = (int) $orderPendingQuery->count();
        }

        $cashierPendingQuery = Order::where('status', 'pending');
        if ($branchId) {
            $cashierPendingQuery->where('branch_id', $branchId);
        }
        if ($hasModule('cashier')) {
            $counts['cashier'] = (int) $cashierPendingQuery->count();
        }

        // Finance approvals
        $financePendingQuery = BudgetRequest::where('status', 'Pending');
        if ($branchId) {
            $financePendingQuery->where('branch_id', $branchId);
        }
        if ($hasModule('finance')) {
            $counts['finance'] = (int) $financePendingQuery->count();
        }

        // Procurement requests
        $procurementPendingQuery = ProcurementRequest::whereIn('status', [
            'pending',
            'budget_pending',
            'cash_in_transit',
            'pending_order_to_supplier',
            'delivery_pending',
            'ongoing_delivery',
            'awaiting_inventory_confirmation',
        ]);
        if ($branchId) {
            $procurementPendingQuery->where('branch_id', $branchId);
        }
        if ($hasModule('procurement')) {
            $counts['procurement'] = (int) $procurementPendingQuery->count();
        }

        // Inventory confirmations
        $inventoryPendingQuery = ProcurementRequest::where('status', 'awaiting_inventory_confirmation');
        if ($branchId) {
            $inventoryPendingQuery->where('branch_id', $branchId);
        }
        if ($hasModule('inventory')) {
            $counts['inventory'] = (int) $inventoryPendingQuery->count();
        }

        // Logistics transactions that still need actions
        $logisticsPendingQuery = LogisticsTransaction::whereIn('status', ['pending', 'in_transit', 'at_destination']);
        if ($branchId) {
            $logisticsPendingQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->orWhere('source_branch_id', $branchId)
                    ->orWhere('destination_branch_id', $branchId);
            });
        }
        if ($hasModule('logistics')) {
            if ($logisticsReadAt = $readAt('logistics')) {
                $logisticsPendingQuery->where('updated_at', '>', $logisticsReadAt);
            }
            $counts['logistics'] = (int) $logisticsPendingQuery->count();
        }

        // Supplier orders
        $supplierPendingQuery = SupplierOrder::where('status', 'pending');
        if ($role === 'SUPPLIER') {
            $supplierPendingQuery->where('supplier_id', $user->id);
        } elseif ($branchId) {
            $supplierPendingQuery->where('branch_id', $branchId);
        }
        if ($hasModule('supplier')) {
            if ($supplierReadAt = $readAt('supplier')) {
                $supplierPendingQuery->where('updated_at', '>', $supplierReadAt);
            }
            $counts['supplier'] = (int) $supplierPendingQuery->count();
        }

        // Kitchen approvals
        $kitchenPendingQuery = Dish::where('approval_status', 'pending_approval');
        if ($branchId) {
            $kitchenPendingQuery->where('branch_id', $branchId);
        }
        if ($hasModule('kitchen')) {
            $counts['kitchen'] = (int) $kitchenPendingQuery->count();
        }

        if ($hasModule('hr')) {
            $hrBranchId = $branchId;
            if ($role === 'MANAGER' && $dept === 'HR' && $branchId) {
                $hrBranch = Branch::find($branchId);
                if ($hrBranch && ($hrBranch->is_main_branch ?? false)) {
                    $hrBranchId = null;
                }
            }

            $hrPendingQuery = Attendance::whereNotNull('face_image')
                ->where('face_image', '!=', '')
                ->where('date', now()->toDateString())
                ->where('confirmed', false)
                ->whereHas('user', function ($query) use ($hrBranchId) {
                    if ($hrBranchId) {
                        $query->where('branch_id', $hrBranchId);
                    }
                });
            $counts['hr'] = (int) $hrPendingQuery->count();
        }

        // Owner + main branch finance approvals
        if ($role === 'OWNER' || $role === 'SUPER_ADMIN' || $role === 'SUPERADMIN') {
            $branchApprovalQuery = Branch::where('approval_status', 'pending_owner');
            if ($branchApprovalReadAt = $readAt('branchPendingOwner')) {
                $branchApprovalQuery->where('updated_at', '>', $branchApprovalReadAt);
            }
            $extras['branchPendingOwner'] = (int) $branchApprovalQuery->count();

            $ownerProductRequestsQuery = ProductRequest::where('status', 'pending_owner');
            if ($branchId) {
                $ownerProductRequestsQuery->where('branch_id', $branchId);
            }
            $extras['ownerProductRequests'] = (int) $ownerProductRequestsQuery->count();
        }

        $isMainBranchFinance = false;
        if ($role === 'MANAGER' && $dept === 'FINANCE' && $user->branch_id) {
            $branch = Branch::find($user->branch_id);
            $isMainBranchFinance = (bool) ($branch && ($branch->is_main_branch ?? false));
        }
        if ($isMainBranchFinance) {
            $extras['branchPendingFinance'] = (int) Branch::where('approval_status', 'pending_finance')->count();
        }

        $priceMarkupPendingQuery = PriceMarkupRequest::where('status', 'pending');
        if ($branchId) {
            $priceMarkupPendingQuery->where('branch_id', $branchId);
        }
        if ($hasModule('finance')) {
            $extras['priceMarkupPending'] = (int) $priceMarkupPendingQuery->count();
        }

        $extras['unreadMessages'] = (int) Message::where('to_user_id', $user->id)
            ->whereNull('read_at')
            ->count();
        $announcementQuery = Announcement::visibleTo($user);
        if ($announcementReadAt = $readAt('announcements')) {
            $announcementQuery->where('updated_at', '>', $announcementReadAt);
        }
        $extras['announcements'] = (int) $announcementQuery->count();

        $approvalCount = $counts['finance']
            + $counts['kitchen']
            + $extras['branchPendingOwner']
            + $extras['branchPendingFinance']
            + $extras['priceMarkupPending']
            + $extras['ownerProductRequests'];
        $updateCount = $counts['admin']
            + $counts['inventory']
            + $counts['logistics']
            + $counts['procurement']
            + $counts['supplier']
            + $counts['cashier'];

        return response()->json([
            'ok' => true,
            'counts' => $counts,
            'extras' => $extras,
            'summary' => [
                'approvals' => $approvalCount,
                'updates' => $updateCount,
                'messages' => $extras['unreadMessages'],
                'announcements' => $extras['announcements'],
            ],
        ]);
    }

    public function markRead(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(['announcements', 'logistics', 'supplier', 'branchPendingOwner'])],
        ]);

        $now = now();
        DB::table('panel_notification_reads')->upsert(
            [[
                'user_id' => $request->user()->id,
                'category' => $validated['category'],
                'read_at' => $now,
                'updated_at' => $now,
                'created_at' => $now,
            ]],
            ['user_id', 'category'],
            ['read_at', 'updated_at']
        );

        return response()->json(['ok' => true]);
    }
}
