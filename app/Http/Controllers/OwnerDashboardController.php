<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return response()->json([
            'ok' => true,

            'totals' => [
                'orders'    => 12,
                'completed' => 10,
                'sales'     => '₱5,240',
                'pending'   => 2,
            ],

            'summary' => [
                'totalBranches'  => 3,
                'totalEmployees' => 24,
            ],

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
