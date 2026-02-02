<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // ADD THIS IMPORT
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    // ==========================================
    // API METHODS (for Vue.js)
    // ==========================================

    /**
     * Get all staff grouped by branch (JSON)
     * Branch managers only see their own branch
     * Owners/Admins see all branches with their managers and staff
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            Log::warning('Unauthenticated apiIndex call', [
                'route' => 'apiIndex',
                'origin' => $request->header('origin'),
                'cookie_header' => $request->header('cookie'),
                'x_xsrf_token' => $request->header('x-xsrf-token'),
                'x_xsrf_token_alt' => $request->header('x-xsrf-token'),
                'cookies' => $request->cookies->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        Log::info('Staff API called by user:', [
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'branch_id' => $user->branch_id
        ]);

        try {
            $branchesQuery = DB::table('branches')
                ->where('is_active', 1);

            // If branch manager, only show their branch
            if ($user->role === 'BRANCH_MANAGER') {
                $branchesQuery->where('branches.id', $user->branch_id);
            }

            $branches = $branchesQuery
                ->orderBy('name')
                ->get();

            $result = [];

            foreach ($branches as $branch) {
                // Get branch manager for this branch
                $branchManager = DB::table('users')
                    ->where('branch_id', $branch->id)
                    ->where('role', 'BRANCH_MANAGER')
                    ->where('is_active', 1)
                    ->whereNull('deleted_at') // Exclude soft deleted
                    ->first();

                // Get staff for this branch (STAFF only)
                $staff = DB::table('users')
                    ->where('branch_id', $branch->id)
                    ->where('role', 'STAFF')
                    ->where('is_active', 1)
                    ->whereNull('deleted_at') // Exclude soft deleted
                    ->get();

                // Get HR for this branch separately
                $hrUsers = DB::table('users')
                    ->where('branch_id', $branch->id)
                    ->where('role', 'HR')
                    ->where('is_active', 1)
                    ->whereNull('deleted_at')
                    ->get();

                // Format branch manager data
                $managerData = null;
                if ($branchManager) {
                    $managerData = [
                        'id' => $branchManager->id,
                        'username' => $branchManager->username,
                        'full_name' => $branchManager->full_name,
                        'email' => $branchManager->email,
                        'phone_number' => $branchManager->phone_number,
                        'address' => $branchManager->address,
                        'role' => 'BRANCH_MANAGER',
                        'is_active' => $branchManager->is_active,
                    ];
                }

                // Format staff data (preserve actual role: STAFF)
                $staffData = $staff->map(function($s) {
                    return [
                        'id' => $s->id,
                        'username' => $s->username,
                        'full_name' => $s->full_name,
                        'email' => $s->email,
                        'phone_number' => $s->phone_number,
                        'address' => $s->address,
                        'role' => $s->role,
                        'is_active' => $s->is_active,
                    ];
                })->toArray();

                // Format HR data
                $hrData = $hrUsers->map(function($h) {
                    return [
                        'id' => $h->id,
                        'username' => $h->username,
                        'full_name' => $h->full_name,
                        'email' => $h->email,
                        'phone_number' => $h->phone_number,
                        'address' => $h->address,
                        'role' => $h->role,
                        'is_active' => $h->is_active,
                    ];
                })->toArray();

                // Only include branches that have manager or staff
                if ($branchManager || count($staffData) > 0 || count($hrData) > 0) {
                    $result[] = [
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->name,
                        'branch_code' => $branch->code,
                        'branch_address' => $branch->address,
                        'branch_manager' => $managerData,
                        'staff' => $staffData,
                        'hr' => $hrData
                    ];
                }
            }

            Log::info('Branches with staff count:', ['count' => count($result)]);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Staff fetch error:  ' . $e->getMessage());
            Log::error('Stack trace:  ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch staff data:  ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single staff (JSON)
     */
    public function apiShow($id)
    {
        $staff = DB::table('users')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->where('users.id', $id)
            ->whereIn('users.role', ['BRANCH_MANAGER', 'STAFF', 'HR'])
            ->whereNull('users.deleted_at') // Exclude soft deleted
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
            ->first();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $staff
        ]);
    }

    /**
     * Create staff (JSON)
     */
    public function apiStore(Request $request)
    {
        Log::debug('apiStore payload', [
            'branchId' => $request->input('branchId'),
            'all' => $request->all()
        ]);
        try {
            $request->validate([
                'username' => 'required|string|max:50|unique:users,username',
                'email' => 'required|email|max:120|unique:users,email',
                'fullName' => 'required|string|max:150',
                'phone' => 'nullable|string|max:30',
                'address' => 'nullable|string|max:255',
                // Accept either branchId (camelCase) or branch_id (snake_case)
                'branchId' => 'required_without:branch_id|exists:branches,id',
                'branch_id' => 'required_without:branchId|exists:branches,id',
                'role' => 'required|in:BRANCH_MANAGER,STAFF,HR',
            ]);

            // Check if branch already has a manager (if creating BRANCH_MANAGER)
            if ($request->input('role') === 'BRANCH_MANAGER') {
                $existingManager = DB::table('users')
                    ->where('branch_id', $request->input('branchId'))
                    ->where('role', 'BRANCH_MANAGER')
                    ->where('is_active', 1)
                    ->whereNull('deleted_at') // Exclude soft deleted
                    ->exists();

                if ($existingManager) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This branch already has a manager'
                    ], 400);
                }
            }


            // Accept both camelCase and snake_case for robustness
            $fullName = $request->input('fullName') ?? $request->input('full_name');
            $email = $request->input('email') ?? $request->input('email');
            $defaultPassword = 'ChikinTayo_2526';
            $insertData = [
                'username' => $request->input('username'),
                'email' => $email,
                'password_hash' => Hash::make($defaultPassword),
                'full_name' => $fullName,
                'role' => $request->input('role'),
                'phone_number' => $request->input('phone'),
                'address' => $request->input('address'),
                'branch_id' => $request->input('branchId') ?? $request->input('branch_id'),
                'is_active' => 1,
                'must_change_password' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            Log::debug('Inserting staff with data:', $insertData);
            $staffId = DB::table('users')->insertGetId($insertData);

            Log::info('Staff created:', ['id' => $staffId, 'role' => $request->input('role')]);

            return response()->json([
                'success' => true,
                'message' => ($request->input('role') === 'BRANCH_MANAGER' ? 'Branch Manager' : ($request->input('role') === 'HR' ? 'HR' : 'Staff')) . ' account created successfully!',
                'data' => ['id' => $staffId]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Staff creation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff account'
            ], 500);
        }
    }

    /**
     * Update staff (JSON)
     */
    // Latest Laravel error observed when calling PUT /api/admin/staff/{id} from the SPA:
    // [2026-01-17 07:32:45] local.ERROR: Staff update error: The branch id field is required.
    // Please ensure the frontend sends `branchId` (exactly this key) and/or adapt validation to
    // accept the key the client sends (e.g., `branch_id`).
    // Full log entries show the same validation error repeated at 07:41:17 as well.
    public function apiUpdate(Request $request, $id)
    {
        // Copilot prompt:
        // I get HTTP 500 when calling PUT /api/admin/staff/{id} from Vue.
        // Here is the Laravel error from laravel.log:
        // [2026-01-17 07:32:45] local.ERROR: Staff update error: The branch id field is required.
        // Please rewrite this update() method so that:
        // - Route uses PUT/PATCH correctly.
        // - It validates username, email, and phone, ignoring the current record for unique rules.
        // - It accepts either 'branchId' (SPA key) or 'branch_id' (snake_case) and validates existence.
        // - Password is only changed if a non-empty password is provided; otherwise keep existing password.
        // - It returns JSON with success or error messages and appropriate HTTP status codes.
        // Make sure validation messages map to the frontend keys (branchId or branch_id).

        try {
            $request->validate([
                'username' => 'required|string|max:50|unique:users,username,' . $id,
                'email' => 'required|email|max:120|unique:users,email,' .  $id,
                'fullName' => 'required|string|max:150',
                'phone' => 'nullable|string|max:30',
                'address' => 'nullable|string|max:255',
                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[!@#$%^&*])[A-Za-z\\d!@#$%^&*]{8,}$/',
                ],
                // accept either branchId (from SPA) or branch_id (from other clients)
                'branchId' => 'sometimes|required|exists:branches,id',
                'branch_id' => 'sometimes|required|exists:branches,id',
                'role' => 'required|in:BRANCH_MANAGER,STAFF,HR',
                'isActive' => 'required|boolean',
            ]);

            // Ensure request is authenticated
            $user = Auth::user();
            if (! $user) {
                Log::warning('Unauthenticated apiUpdate call', [
                    'route' => 'apiUpdate',
                    'id' => $id,
                    'origin' => $request->header('origin'),
                    'cookie_header' => $request->header('cookie'),
                    'x_xsrf_token' => $request->header('x-xsrf-token'),
                    'cookies' => $request->cookies->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            // Normalize branch id (accept either branchId or branch_id)
            $branchId = $request->input('branchId') ?? $request->input('branch_id');

            // Check if branch already has a manager (if changing to BRANCH_MANAGER)
            if ($request->input('role') === 'BRANCH_MANAGER') {
                $existingManager = DB::table('users')
                    ->where('branch_id', $branchId)
                    ->where('role', 'BRANCH_MANAGER')
                    ->where('is_active', 1)
                    ->where('id', '!=', $id)
                    ->whereNull('deleted_at') // Exclude soft deleted
                    ->exists();

                if ($existingManager) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This branch already has a manager'
                    ], 400);
                }
            }

            // Use Eloquent to update the user record
            $staff = User::findOrFail($id);

            $staff->username = $request->input('username');
            $staff->email = $request->input('email');
            $staff->full_name = $request->input('fullName');
            $staff->phone_number = $request->input('phone');
            $staff->address = $request->input('address');
            $staff->branch_id = $branchId;
            $staff->role = $request->input('role');
            $staff->is_active = (bool) $request->input('isActive');

            if ($request->filled('password')) {
                $staff->password_hash = Hash::make($request->input('password'));
            }

            $staff->updated_at = now();
            $staff->save();

            Log::info('Staff updated:', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Account updated successfully!',
                'data' => [
                    'id' => $staff->id,
                    'username' => $staff->username,
                    'email' => $staff->email,
                    'full_name' => $staff->full_name,
                    'branch_id' => $staff->branch_id,
                    'role' => $staff->role,
                    'is_active' => $staff->is_active,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Staff update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update account'
            ], 500);
        }
    }

    /**
     * Delete staff (SOFT DELETE - moves to deleted_at)
     */
    public function apiDestroy($id)
    {
        try {
            // Use Eloquent model for soft delete
            $user = User::findOrFail($id);

            // Prevent deleting owner accounts
            if ($user->role === 'OWNER') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete owner account'
                ], 403);
            }

            // Check if user is BRANCH_MANAGER, STAFF or HR
            if (! in_array($user->role, ['BRANCH_MANAGER', 'STAFF', 'HR'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user role'
                ], 400);
            }



            // Perform soft delete
            $user->delete();

            Log::info('Staff soft deleted:', ['id' => $id, 'role' => $user->role]);
            return response()->json([
                'success' => true,
                'message' => ($user->role === 'BRANCH_MANAGER' ? 'Branch Manager' : ($user->role === 'HR' ? 'HR' : 'Staff')) . ' account moved to deleted history successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Staff deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account:  ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all branches (JSON)
     */
    public function apiBranches()
    {
        try {
            $branches = DB::table('branches')
                ->where('is_active', 1)
                ->select('id', 'name', 'code', 'address')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $branches
            ]);

        } catch (\Exception $e) {
            Log::error('Branches fetch error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch branches'
            ], 500);
        }
    }
}
