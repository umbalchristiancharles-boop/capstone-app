<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Count branches (active only or all)
        $branches = Branch::where('is_active', 1)->count();

        // Count orders
        $orders = Order::count();

        // Count staff (users with role 'staff')
        $staffCount = User::where('role', 'staff')->count();

        // Count branch managers (users with role 'branch_manager')
        $branchManagers = User::where('role', 'branch_manager')->count();

        // Recent activity - Staff and Branch Managers who recently updated
        $recentActivity = User::whereIn('role', ['staff', 'branch_manager'])
            ->where('is_active', 1)
            ->latest('updated_at')
            ->limit(10)
            ->get(['id', 'full_name', 'role', 'updated_at', 'branch_id']);

        return response()->json([
            'branches_count' => $branches,
            'orders_count' => $orders,
            'staff_count' => $staffCount + $branchManagers,
            'recent_activity' => $recentActivity->map(function ($user) {
                return [
                    'name' => $user->full_name ?? 'N/A',
                    'role' => $user->role,
                    'branch' => $user->branch_id ? (Branch::find($user->branch_id)?->name ?? 'N/A') : 'N/A',
                    'last_active' => $user->updated_at ? $user->updated_at->format('M d, Y H:i') : 'N/A',
                ];
            }),
        ]);
    }
}
