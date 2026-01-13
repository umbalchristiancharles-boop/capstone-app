<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    // ==========================================
    // API METHODS (for Vue.js)
    // ==========================================

    /**
     * Get all staff (JSON) - filtered by role
     * Branch managers only see their own branch's staff
     * Owners see all staff
     */
    public function apiIndex()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Not authenticated'
            ], 401);
        }
        
        \Log::info('Staff API called by user:', [
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'branch_id' => $user->branch_id
        ]);
        
        $query = DB::table('users')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id');
        
        // If user is a BRANCH_MANAGER, only show STAFF from their branch
        if ($user->role === 'BRANCH_MANAGER') {
            $query->where('users.role', 'STAFF')
                  ->where('users.branch_id', $user->branch_id);
            \Log::info('Branch manager filter applied:', [
                'looking_for_role' => 'STAFF',
                'in_branch' => $user->branch_id
            ]);
        } else {
            // If user is OWNER, show all users (BRANCH_MANAGER and STAFF)
            $query->whereIn('users.role', ['BRANCH_MANAGER', 'STAFF']);
            \Log::info('Owner filter applied: showing BRANCH_MANAGER and STAFF');
        }
        
        $staffs = $query
            ->select(
                'users.id',
                'users.username',
                'users.full_name',
                'users.email',
                'users.phone_number',
                'users.address',
                'users.branch_id',
                'users.role',
                'users.is_active',
                'branches.name as branch_name'
            )
            ->orderBy('users.created_at', 'desc')
            ->get();

        \Log::info('Query result count:', ['count' => $staffs->count()]);

        return response()->json([
            'ok' => true,
            'staff' => $staffs
        ]);
    }

    /**
     * Get single staff (JSON)
     */
    public function apiShow($id)
    {
        $staff = DB::table('users')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->where('users.id', $id)
            ->where('users.role', 'STAFF')
            ->select(
                'users.id',
                'users.username',
                'users.full_name',
                'users.email',
                'users.phone_number',
                'users.address',
                'users.branch_id',
                'users.is_active',
                'branches.name as branch_name'
            )
            ->first();

        if (!$staff) {
            return response()->json([
                'ok' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'staff' => $staff
        ]);
    }

    /**
     * Create staff (JSON)
     */
    public function apiStore(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max: 120|unique:users,email',
            'password' => 'required|string|min:6',
            'fullName' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'branchId' => 'required|exists:branches,id',
            'role' => 'required|in:BRANCH_MANAGER,STAFF',
        ]);

        $staffId = DB::table('users')->insertGetId([
            'username' => $request->username,
            'email' => $request->email,
            'password_hash' => password_hash($request->password, PASSWORD_BCRYPT),
            'full_name' => $request->fullName,
            'role' => $request->role,
            'phone_number' => $request->phone,
            'address' => $request->address,
            'branch_id' => $request->branchId,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Staff account created successfully! ',
            'staff' => ['id' => $staffId]
        ], 201);
    }

    /**
     * Update staff (JSON)
     */
    public function apiUpdate(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'email' => 'required|email|max:120|unique:users,email,' .  $id,
            'fullName' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'branchId' => 'required|exists:branches,id',
            'role' => 'required|in:BRANCH_MANAGER,STAFF',
            'isActive' => 'required|boolean',
        ]);

        $updateData = [
            'username' => $request->username,
            'email' => $request->email,
            'full_name' => $request->fullName,
            'phone_number' => $request->phone,
            'address' => $request->address,
            'branch_id' => $request->branchId,
            'role' => $request->role,
            'is_active' => $request->isActive,
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $updateData['password_hash'] = password_hash($request->password, PASSWORD_BCRYPT);
        }

        DB:: table('users')->where('id', $id)->update($updateData);

        return response()->json([
            'ok' => true,
            'message' => 'Staff account updated successfully!'
        ]);
    }

    /**
     * Delete staff (JSON)
     */
    public function apiDestroy($id)
    {
        DB::table('users')->where('id', $id)->where('role', 'STAFF')->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Staff account deleted successfully!'
        ]);
    }

    /**
     * Get all branches (JSON)
     */
    public function apiBranches()
    {
        $branches = DB::table('branches')
            ->where('is_active', 1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'ok' => true,
            'branches' => $branches
        ]);
    }
}
