<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Branch;

class OwnerDashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user = Auth::user();

        // Count all active employees (STAFF or BRANCH_MANAGER)
        $totalEmployees = User::whereIn('role', ['STAFF', 'BRANCH_MANAGER'])
            ->where('is_active', 1)
            ->count();

        // Count all branches
        $totalBranches = Branch::count();

        // For branch manager or staff, count employees in their branch only
        $branchEmployees = null;
        if (in_array($user->role, ['BRANCH_MANAGER', 'STAFF']) && $user->branch_id) {
            // Only count STAFF (not branch manager) if the user is STAFF
            $roles = $user->role === 'STAFF' ? ['STAFF'] : ['STAFF', 'BRANCH_MANAGER'];
            $branchEmployees = User::whereIn('role', $roles)
                ->where('is_active', 1)
                ->where('branch_id', $user->branch_id)
                ->count();
        }

        // Example: you can add this to the response for frontend use
        return response()->json([
            'ok' => true,
            'totals' => [
                'orders'    => 12,
                'completed' => 10,
                'sales'     => '₱5,240',
                'pending'   => 2,
            ],
            'summary' => [
                'totalBranches'  => $totalBranches,
                'totalEmployees' => $totalEmployees,
                'branchEmployees' => $branchEmployees, // null for admin, number for branch manager/staff
            ],
            // ...existing code for recentOrders, productionQueue, etc...
            'recentOrders' => [
                [
                    'id'          => 1,
                    'code'        => '#ORD001',
                    'customer'    => 'John Doe',
                    'status'      => 'completed',
                    'statusLabel' => 'Completed',
                    'total'       => '₱850',
                ],
                [
                    'id'          => 2,
                    'code'        => '#ORD002',
                    'customer'    => 'Jane Smith',
                    'status'      => 'in_kitchen',
                    'statusLabel' => 'In Kitchen',
                    'total'       => '₱620',
                ],
                [
                    'id'          => 3,
                    'code'        => '#ORD003',
                    'customer'    => 'Bob Wilson',
                    'status'      => 'pending',
                    'statusLabel' => 'Pending',
                    'total'       => '₱450',
                ],
                [
                    'id'          => 4,
                    'code'        => '#ORD004',
                    'customer'    => 'Alice Brown',
                    'status'      => 'completed',
                    'statusLabel' => 'Completed',
                    'total'       => '₱1,200',
                ],
                [
                    'id'          => 5,
                    'code'        => '#ORD005',
                    'customer'    => 'Charlie Davis',
                    'status'      => 'completed',
                    'statusLabel' => 'Completed',
                    'total'       => '₱920',
                ],
            ],
            'productionQueue' => [
                [
                    'id'         => 1,
                    'title'      => 'Ube Cake - Order #ORD002',
                    'meta'       => 'Started 10 mins ago',
                    'badgeLabel' => 'In Progress',
                    'badgeClass' => 'badge--warning',
                ],
                [
                    'id'         => 2,
                    'title'      => 'Mochi Bread - Order #ORD003',
                    'meta'       => 'Queue position: 2',
                    'badgeLabel' => 'Queued',
                    'badgeClass' => 'badge--info',
                ],
            ],
            'topProducts' => [
                ['id' => 1, 'name' => 'Ube Cake',   'orders' => 8],
                ['id' => 2, 'name' => 'Mochi Bread','orders' => 6],
                ['id' => 3, 'name' => 'Croissant',  'orders' => 5],
            ],
            'lowStockItems' => [
                ['id' => 1, 'name' => 'Ube Powder', 'qty' => 2, 'unit' => 'kg'],
                ['id' => 2, 'name' => 'Butter',     'qty' => 3, 'unit' => 'kg'],
            ],
            'staffActivity' => [
                ['id' => 1, 'message' => 'Order #ORD001 completed', 'meta' => '2 mins ago'],
                ['id' => 2, 'message' => 'Maria clocked in',        'meta' => '15 mins ago'],
                ['id' => 3, 'message' => 'Inventory updated',       'meta' => '1 hour ago'],
            ],
        ]);
    }
}
