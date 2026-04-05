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
        ];

        // Admin + Cashier pending orders
        $orderPendingQuery = Order::whereIn('status', ['pending', 'in_kitchen']);
        if ($branchId) {
            $orderPendingQuery->where('branch_id', $branchId);
        }
        $counts['admin'] = (int) $orderPendingQuery->count();

        $cashierPendingQuery = Order::where('status', 'pending');
        if ($branchId) {
            $cashierPendingQuery->where('branch_id', $branchId);
        }
        $counts['cashier'] = (int) $cashierPendingQuery->count();

        // Finance approvals
        $financePendingQuery = BudgetRequest::where('status', 'Pending');
        if ($branchId) {
            $financePendingQuery->where('branch_id', $branchId);
        }
        $counts['finance'] = (int) $financePendingQuery->count();

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
        $counts['procurement'] = (int) $procurementPendingQuery->count();

        // Inventory confirmations
        $inventoryPendingQuery = ProcurementRequest::where('status', 'awaiting_inventory_confirmation');
        if ($branchId) {
            $inventoryPendingQuery->where('branch_id', $branchId);
        }
        $counts['inventory'] = (int) $inventoryPendingQuery->count();

        // Logistics transactions that still need actions
        $logisticsPendingQuery = LogisticsTransaction::whereIn('status', ['pending', 'in_transit', 'at_destination']);
        if ($branchId) {
            $logisticsPendingQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->orWhere('source_branch_id', $branchId)
                    ->orWhere('destination_branch_id', $branchId);
            });
        }
        $counts['logistics'] = (int) $logisticsPendingQuery->count();

        // Supplier orders
        $supplierPendingQuery = SupplierOrder::where('status', 'pending');
        if ($role === 'SUPPLIER') {
            $supplierPendingQuery->where('supplier_id', $user->id);
        } elseif ($branchId) {
            $supplierPendingQuery->where('branch_id', $branchId);
        }
        $counts['supplier'] = (int) $supplierPendingQuery->count();

        // Kitchen approvals
        $kitchenPendingQuery = Dish::where('approval_status', 'pending_approval');
        if ($branchId) {
            $kitchenPendingQuery->where('branch_id', $branchId);
        }
        $counts['kitchen'] = (int) $kitchenPendingQuery->count();

        // Owner + main branch finance approvals
        if ($role === 'OWNER' || $role === 'SUPER_ADMIN' || $role === 'SUPERADMIN') {
            $extras['branchPendingOwner'] = (int) Branch::where('approval_status', 'pending_owner')->count();
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
        $extras['priceMarkupPending'] = (int) $priceMarkupPendingQuery->count();

        return response()->json([
            'ok' => true,
            'counts' => $counts,
            'extras' => $extras,
        ]);
    }
}
