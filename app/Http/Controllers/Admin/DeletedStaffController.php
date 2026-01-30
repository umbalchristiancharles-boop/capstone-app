<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeletedStaffController extends Controller
{
    // Show deleted history page
    public function index()
    {
        return view('admin.deleted-staff');
    }

    // API:  Get deleted staff
    public function apiIndex()
    {
        try {
            $currentUser = Auth::user();

            // Only OWNER and BRANCH_MANAGER can access
            if (! in_array($currentUser->role, ['OWNER', 'BRANCH_MANAGER'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $query = User::onlyTrashed()
                ->whereIn('role', ['BRANCH_MANAGER', 'STAFF', 'HR'])
                ->with('branch');

            // Branch managers can only see deleted users from their branch
            if ($currentUser->role === 'BRANCH_MANAGER') {
                $query->where('branch_id', $currentUser->branch_id);
            }

            $deletedUsers = $query->orderBy('deleted_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $deletedUsers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch deleted accounts: ' . $e->getMessage()
            ], 500);
        }
    }

    // API: Restore deleted account
    public function apiRestore($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);

            // Check access
            $currentUser = Auth::user();
            if ($currentUser->role === 'BRANCH_MANAGER' && $user->branch_id !== $currentUser->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $user->restore();

            return response()->json([
                'success' => true,
                'message' => 'Account restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore account: ' . $e->getMessage()
            ], 500);
        }
    }

    // API: Permanently delete account
    public function apiForceDelete($id)
    {
        try {
            // Only OWNER can permanently delete
            $currentUser = Auth::user();
            if ($currentUser->role !== 'OWNER') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admin can permanently delete accounts'
                ], 403);
            }

            $user = User::onlyTrashed()->findOrFail($id);
            $user->forceDelete(); // Permanent delete

            return response()->json([
                'success' => true,
                'message' => 'Account permanently deleted'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage()
            ], 500);
        }
    }
}
