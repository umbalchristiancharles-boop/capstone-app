<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\StaffDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Support\Permission;

class StaffController extends Controller
{
    // Online threshold in minutes (user is considered online if activity within this time)
    private const ONLINE_THRESHOLD_MINUTES = 5;

    // ==========================================
    // API METHODS (for Vue.js)
    // ==========================================

    /**
     * Check if a user has an active session within the threshold
     */
    private function isUserOnline(int $userId): bool
    {
        try {
            $threshold = now()->subMinutes(self::ONLINE_THRESHOLD_MINUTES);

            $sessionExists = DB::table('sessions')
                ->where('user_id', $userId)
                ->where('last_activity', '>=', $threshold->timestamp)
                ->exists();

            return $sessionExists;
        } catch (\Exception $e) {
            Log::warning('Error checking user online status: ' . $e->getMessage());
            return false;
        }
    }

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
                'cookies' => $request->header('cookie'),
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

            // If branch manager, HR, or ADMIN, only show their branch
            if (in_array($user->role, ['BRANCH_MANAGER', 'MANAGER', 'HR', 'ADMIN'])) {
                $branchesQuery->where('branches.id', $user->branch_id);
            }

            $branches = $branchesQuery
                ->orderBy('name')
                ->get();

            $result = [];

            foreach ($branches as $branch) {
                // Get all managers for this branch (may be multiple)
                $managers = DB::table('users')
                    ->where('branch_id', $branch->id)
                    ->whereIn('role', ['BRANCH_MANAGER', 'MANAGER'])
                    ->where('is_active', 1)
                    ->whereNull('deleted_at') // Exclude soft deleted
                    ->get();

                // Keep a single representative as branch_manager (frontend still expects a single manager)
                $branchManager = $managers->first();

                // Get staff for this branch (include STAFF and CUSTOM accounts)
                $staff = DB::table('users')
                    ->where('branch_id', $branch->id)
                    ->whereIn('role', ['STAFF', 'CUSTOM'])
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

                // Format branch manager data (single representative)
                $managerData = null;
                if ($branchManager) {
                    $mgrOnline = $this->isUserOnline($branchManager->id);
                    $managerData = [
                        'id' => $branchManager->id,
                        'username' => $branchManager->username,
                        'full_name' => $branchManager->full_name,
                        'email' => $branchManager->email,
                        'phone_number' => $branchManager->phone_number,
                        'department' => $branchManager->department ?? '',
                        'address' => $branchManager->address,
                        'role' => $branchManager->role,
                        'is_active' => $branchManager->is_active,
                        'is_online' => $mgrOnline,
                        'status' => $branchManager->is_active ? ($mgrOnline ? 'On Duty' : 'Offline') : 'Inactive',
                    ];
                }

                // Format managers data (all managers in branch)
                $managersData = $managers->map(function($m) {
                    $isOnline = $this->isUserOnline($m->id);
                    return [
                        'id' => $m->id,
                        'username' => $m->username,
                        'full_name' => $m->full_name,
                        'email' => $m->email,
                        'phone_number' => $m->phone_number,
                        'department' => $m->department ?? '',
                        'address' => $m->address,
                        'role' => $m->role,
                        'is_active' => $m->is_active,
                        'is_online' => $isOnline,
                        'status' => $m->is_active ? ($isOnline ? 'On Duty' : 'Offline') : 'Inactive',
                    ];
                })->toArray();

                // Format staff data (preserve actual role: STAFF)
                $staffData = $staff->map(function($s) {
                    $isOnline = $this->isUserOnline($s->id);
                    return [
                        'id' => $s->id,
                        'username' => $s->username,
                        'full_name' => $s->full_name,
                        'email' => $s->email,
                        'phone_number' => $s->phone_number,
                        'department' => $s->department ?? '',
                        'address' => $s->address,
                        'role' => $s->role,
                        'is_active' => $s->is_active,
                        'is_online' => $isOnline,
                        'status' => $s->is_active ? ($isOnline ? 'On Duty' : 'Offline') : 'Inactive',
                    ];
                })->toArray();

                // Merge managers into staff list so frontend receives all managers + staff in the 'staff' array
                // Avoid duplicating the representative branch_manager already shown separately
                $otherManagers = [];
                if ($managersData && $managerData) {
                    foreach ($managersData as $md) {
                        if ($md['id'] !== $managerData['id']) $otherManagers[] = $md;
                    }
                } else {
                    $otherManagers = $managersData;
                }

                // Prepend other managers so managers appear before staff in the listing
                $staffData = array_merge($otherManagers, $staffData);

                // Format HR data
                $hrData = $hrUsers->map(function($h) {
                    $isOnline = $this->isUserOnline($h->id);
                    return [
                        'id' => $h->id,
                        'username' => $h->username,
                        'full_name' => $h->full_name,
                        'email' => $h->email,
                        'phone_number' => $h->phone_number,
                        'department' => $h->department ?? '',
                        'address' => $h->address,
                        'role' => $h->role,
                        'is_active' => $h->is_active,
                        'is_online' => $isOnline,
                        'status' => $h->is_active ? ($isOnline ? 'On Duty' : 'Offline') : 'Inactive',
                    ];
                })->toArray();

// Always include for branch users (ADMIN/HR/MGR), or populated for globals
                $shouldInclude = $branchManager || count($staffData) > 0 || count($hrData) > 0;
                if (in_array($user->role, ['BRANCH_MANAGER', 'MANAGER', 'HR', 'ADMIN']) || $shouldInclude) {
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

        // Additionally include OWNER accounts (they are not tied to branches).
            // Only show OWNERs to ADMIN, OWNER, or SUPER_ADMIN users.
            try {
                if (in_array($user->role, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])) {
                    $owners = DB::table('users')
                        ->where('role', 'OWNER')
                        ->where('is_active', 1)
                        ->whereNull('deleted_at')
                        ->get();

                    if ($owners && count($owners) > 0) {
                        $ownerData = $owners->map(function($o) {
                                $isOnline = $this->isUserOnline($o->id);
                                return [
                                    'id' => $o->id,
                                    'username' => $o->username,
                                    'full_name' => $o->full_name,
                                    'email' => $o->email,
                                    'phone_number' => $o->phone_number,
                                    'department' => $o->department ?? '',
                                    'address' => $o->address,
                                    'role' => $o->role,
                                    'is_active' => $o->is_active,
                                    'is_online' => $isOnline,
                                    'status' => $o->is_active ? ($isOnline ? 'On Duty' : 'Offline') : 'Inactive',
                                ];
                            })->toArray();

                        // Append as a pseudo-branch named 'Owners' so frontend can display them.
                        $result[] = [
                            'branch_id' => null,
                            'branch_name' => 'Owners',
                            'branch_code' => null,
                            'branch_address' => null,
                            'branch_manager' => null,
                            'staff' => $ownerData,
                            'hr' => [],
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not append owners to staff list: ' . $e->getMessage());
            }

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
        $user = Auth::user();

        $staff = DB::table('users')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->where('users.id', $id)
            ->whereIn('users.role', ['BRANCH_MANAGER', 'MANAGER', 'STAFF', 'HR', 'CUSTOM'])
            ->whereNull('users.deleted_at') // Exclude soft deleted
            ->select(
                'users.id',
                'users.username',
                'users.full_name',
                'users.email',
                'users.phone_number',
                'users.address',
                'users.department',
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

        if ($user && in_array($user->role, ['HR', 'ADMIN']) && $user->branch_id && $staff->branch_id !== $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        // Fetch documents
        $documents = StaffDocument::where('user_id', '=', $id)->first();
        $documentsList = [];

        if ($documents) {
            $documentsList = [
                'resume' => $documents->resume_path ? ['path' => $documents->resume_path, 'url' => '/api/admin/staff/' . $id . '/document/resume'] : null,
                'government_id' => $documents->government_id_path ? ['path' => $documents->government_id_path, 'url' => '/api/admin/staff/' . $id . '/document/government_id'] : null,
                'psa_birth_certificate' => $documents->psa_birth_certificate_path ? ['path' => $documents->psa_birth_certificate_path, 'url' => '/api/admin/staff/' . $id . '/document/psa_birth_certificate'] : null,
                'nbi_clearance' => $documents->nbi_clearance_path ? ['path' => $documents->nbi_clearance_path, 'url' => '/api/admin/staff/' . $id . '/document/nbi_clearance'] : null,
                'police_clearance' => $documents->police_clearance_path ? ['path' => $documents->police_clearance_path, 'url' => '/api/admin/staff/' . $id . '/document/police_clearance'] : null,
                'medical_certificate' => $documents->medical_certificate_path ? ['path' => $documents->medical_certificate_path, 'url' => '/api/admin/staff/' . $id . '/document/medical_certificate'] : null,
                'drug_test_result' => $documents->drug_test_result_path ? ['path' => $documents->drug_test_result_path, 'url' => '/api/admin/staff/' . $id . '/document/drug_test_result'] : null,
                'sss_id' => $documents->sss_id_path ? ['path' => $documents->sss_id_path, 'url' => '/api/admin/staff/' . $id . '/document/sss_id'] : null,
                'philhealth_id' => $documents->philhealth_id_path ? ['path' => $documents->philhealth_id_path, 'url' => '/api/admin/staff/' . $id . '/document/philhealth_id'] : null,
                'pagibig_mdf' => $documents->pagibig_mdf_path ? ['path' => $documents->pagibig_mdf_path, 'url' => '/api/admin/staff/' . $id . '/document/pagibig_mdf'] : null,
                'tin_id' => $documents->tin_id_path ? ['path' => $documents->tin_id_path, 'url' => '/api/admin/staff/' . $id . '/document/tin_id'] : null,
                'diploma_transcript' => $documents->diploma_transcript_path ? ['path' => $documents->diploma_transcript_path, 'url' => '/api/admin/staff/' . $id . '/document/diploma_transcript'] : null,
            ];
        }

        $staff->documents = $documentsList;

        return response()->json([
            'success' => true,
            'data' => $staff
        ]);
    }

    /**
     * Reset a staff member's password to the default and require change on next login.
     */
    public function resetPassword(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        // Only allow OWNER/ADMIN/SUPER_ADMIN to reset any account. Branch managers / HR may reset within their branch.
        $target = User::find($id);
        if (! $target) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        if (in_array($user->role, ['BRANCH_MANAGER', 'MANAGER', 'HR'])) {
            // ensure same branch
            if (! $user->branch_id || $user->branch_id != $target->branch_id) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }
        } elseif (! in_array($user->role, ['OWNER', 'ADMIN', 'SUPER_ADMIN', 'SUPERADMIN'])) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        try {
            $defaultPassword = config('chikintayo.default_password');
            // Assign plain password so the User model mutator hashes it exactly once
            $target->password = $defaultPassword;
            $target->must_change_password = 1;
            $target->save();

            return response()->json([
                'success' => true,
                'message' => 'Password reset to default successfully',
                'defaultPassword' => $defaultPassword,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reset password for user ' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to reset password'], 500);
        }
    }

    /**
     * Create staff (JSON)
     */
    public function apiStore(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        Log::debug('apiStore payload', [
            'branchId' => $request->input('branchId'),
            'all' => $request->all()
        ]);
        try {
            $allowedRoles = [];
            if (in_array($user->role, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])) {
                // Allow owners/superadmins to create owner, branch managers, HR, staff and custom accounts
                $allowedRoles = ['OWNER', 'BRANCH_MANAGER', 'MANAGER', 'HR', 'STAFF', 'CUSTOM'];
            } elseif ($user->role === 'ADMIN') {
                // ADMIN is per-branch, can create branch-level staff and custom accounts
                $allowedRoles = ['BRANCH_MANAGER', 'MANAGER', 'HR', 'STAFF', 'CUSTOM'];
            } elseif (in_array($user->role, ['BRANCH_MANAGER', 'MANAGER'])) {
                // Branch managers and department managers (HR, Finance, etc.) can create HR and staff
                $allowedRoles = ['HR', 'STAFF'];
                // If the current user is a Manager of HR, allow them to create other Managers
                // (e.g., HR manager creating department managers like Procurement, Finance)
                if (strtoupper($user->role) === 'MANAGER' && strtoupper($user->department ?? '') === 'HR') {
                    // HR managers should also be allowed to create CUSTOM accounts
                    $allowedRoles = ['MANAGER', 'HR', 'STAFF', 'CUSTOM'];
                }
            } elseif (strtoupper($user->role) === 'HR') {
                // HR users can create staff, department managers, and custom accounts within their branch
                // (Allow creating MANAGER so HR can add managers for departments)
                $allowedRoles = ['MANAGER', 'HR', 'STAFF', 'CUSTOM'];
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            $roleRule = 'required|in:' . implode(',', $allowedRoles);
            $fileRule = 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120';


            // Normalize role/department in case the frontend sent a combined value
            // Example: some clients may send `role` => "BRANCH_MANAGER hr" (role + department)
            // Accept that and split into role + department so validation works.
            $rawRole = $request->input('role');
            if ($rawRole && is_string($rawRole) && preg_match('/\s+/', trim($rawRole))) {
                $parts = preg_split('/\s+/', trim($rawRole), 2);
                // merge the normalized role back into the request for validation
                $request->merge(['role' => strtoupper($parts[0])]);
                // If department not explicitly provided, use the second token
                if (!$request->input('department') && isset($parts[1])) {
                    $request->merge(['department' => $parts[1]]);
                }
            }

            // Log allowed roles and incoming requested role to debug validation issues
            Log::debug('apiStore allowedRoles', ['allowedRoles' => $allowedRoles, 'session_user_role' => $user->role ?? null]);

            // Build validation rules dynamically so OWNER role does not require a branch
            $requestedRole = $request->input('role');

            Log::debug('apiStore requestedRole before normalization', ['requestedRole_raw' => $requestedRole]);

            // Accept both camelCase and snake_case field names
            $fullName = $request->input('fullName') ?? $request->input('full_name') ?? '';
            $phone = $request->input('phone') ?? $request->input('phone_number') ?? '';
            $address = $request->input('address') ?? '';
            $branchId = $request->input('branchId') ?? $request->input('branch_id');
            $password = $request->input('password');

$rules = [
                // Username may be omitted from the SPA (auto-generated server-side)
                'username' => 'nullable|string|max:50|unique:users,username',
                'email' => 'nullable|email|max:120|unique:users,email',
                'full_name' => 'nullable|string|max:150',
                'fullName' => 'nullable|string|max:150',
                'phone' => 'nullable|string|max:30',
                'phone_number' => 'nullable|string|max:30',
                'address' => 'nullable|string|max:255',
                'role' => $roleRule,
                // Documents are now optional (nullable) for staff creation
                'resume' => $fileRule,
                'government_id' => $fileRule,
                'psa_birth_certificate' => $fileRule,
                'nbi_clearance' => $fileRule,
                'police_clearance' => $fileRule,
                'medical_certificate' => $fileRule,
                'drug_test_result' => $fileRule,
                'sss_id' => $fileRule,
                'philhealth_id' => $fileRule,
                'pagibig_mdf' => $fileRule,
                'tin_id' => $fileRule,
                'diploma_transcript' => $fileRule,
                'password' => 'nullable|string|min:8',
            ];

            // branch is optional for now - Owner can create staff without specifying a branch
            // The branch can be assigned later
            $rules['branchId'] = 'nullable|exists:branches,id';
            $rules['branch_id'] = 'nullable|exists:branches,id';

            // Custom validation messages
            $messages = [
                'branchId.required' => 'The branch field is required.',
                'branch_id.required' => 'The branch field is required.',
            ];

            // Require department when creating Managers or Staff
            if (in_array(strtoupper($requestedRole), ['MANAGER', 'STAFF'])) {
                $rules['department'] = 'required|in:HR,FINANCE,INVENTORY,LOGISTICS,CASHIER,KITCHEN,PROCUREMENT';
                $messages['department.required'] = 'The department field is required.';
            }

            $request->validate($rules, $messages);

            $role = strtoupper($requestedRole);

            // Use frontend-provided password or default from config
            $defaultPassword = !empty($password) ? $password : config('chikintayo.default_password');
            $branchId = $branchId ?? null;

            // If the creator is an HR user, enforce that created staff belong to the HR's branch
            // (HR should not be able to create staff in other branches)
            if (strtoupper($user->role) === 'HR' && $user->branch_id) {
                $branchId = $user->branch_id;
            }

            // If username was not provided by the SPA, generate a unique one server-side.
            $username = $request->input('username');
            if (! $username || trim($username) === '') {
                // Build a base from the first name if available, fallback to 'user'
                $firstName = '';
                if (! empty($fullName)) {
                    $parts = preg_split('/\s+/', trim($fullName));
                    $firstName = strtolower($parts[0] ?? '');
                }
                $base = preg_replace('/[^a-z0-9]/', '', $firstName) ?: 'user';
                $base = substr($base, 0, 8);

                // Try candidates until unique
                $candidate = strtoupper($base) . rand(100, 999);
                $tries = 0;
                while (DB::table('users')->where('username', $candidate)->exists() && $tries < 10) {
                    $candidate = strtoupper($base) . rand(100, 999);
                    $tries++;
                }
                // As a fallback append timestamp
                if (DB::table('users')->where('username', $candidate)->exists()) {
                    $candidate = strtoupper($base) . substr(time(), -6);
                }

                $username = $candidate;
            }

            // Ensure request has username for later insert
            $request->merge(['username' => $username]);

            // Allow multiple managers per branch — no uniqueness check here.

            if (in_array($user->role, ['HR', 'ADMIN']) && $user->branch_id && $branchId && (int) $branchId !== (int) $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            if (in_array($user->role, ['BRANCH_MANAGER', 'MANAGER']) && $user->branch_id && $branchId && (int) $branchId !== (int) $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            // CUSTOM users with hr/admin.users can create only within their branch (if branch-bound)
            if (strtoupper($user->role ?? '') === 'CUSTOM' && $user->branch_id && $branchId && (int) $branchId !== (int) $user->branch_id) {
                if (!Permission::allowed($user, [], ['admin.users', 'hr'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Forbidden'
                    ], 403);
                }
            }

            $transactionStarted = false;
            $docDir = null;

            DB::beginTransaction();
            $transactionStarted = true;

            // Fix: department must be NULL for OWNER, or if not set/invalid
            $originalDept = $request->input('department');
            $departmentValue = $originalDept;
            if (is_string($departmentValue) && $departmentValue !== '') {
                $departmentValue = strtoupper($departmentValue);
            }
            $validDepartments = ['HR', 'FINANCE', 'INVENTORY', 'LOGISTICS', 'CASHIER', 'KITCHEN', 'PROCUREMENT'];
            if ($role === 'OWNER' || $departmentValue === null || $departmentValue === '' || !in_array($departmentValue, $validDepartments)) {
                // If the client provided a department but it was dropped, log for visibility
                if (!empty($originalDept) && strtoupper($originalDept) !== ($departmentValue ?? '')) {
                    Log::warning('Department normalized to null during staff creation', [
                        'requested_department' => $originalDept,
                        'normalized' => null,
                        'role' => $role,
                        'request_user_id' => $user->id ?? null,
                    ]);
                }
                $departmentValue = null;
            }
            // If creating CUSTOM account via Admin endpoint, normalize modules/functions from FormData
            $permissionsPayload = null;
            if ($role === 'CUSTOM') {
                Log::debug('admin apiStore raw modules/functions', [
                    'modules_raw' => $request->input('modules'),
                    'functions_raw' => $request->input('functions'),
                    'all' => $request->all() ? 'present' : 'empty'
                ]);

                $allowedModules = [
                    'admin', 'finance', 'logistics', 'inventory', 'procurement', 'kitchen', 'cashier', 'hr', 'reports'
                ];
                $allowedFunctions = [
                    'admin.users', 'admin.branches', 'admin.settings',
                    'finance.dashboard', 'finance.budget', 'finance.reports', 'finance.expenses',
                    'logistics.dispatch', 'logistics.receiving', 'logistics.transfers',
                    'inventory.products', 'inventory.counts', 'inventory.adjustments',
                    'procurement.purchase_orders', 'procurement.suppliers', 'procurement.approvals',
                    'kitchen.orders', 'kitchen.production', 'kitchen.waste',
                    'cashier.pos', 'cashier.refunds', 'cashier.shifts',
                    'hr.attendance', 'hr.scheduling', 'hr.payroll',
                    'reports.sales', 'reports.inventory', 'reports.finance',
                ];

                $rawModulesInput = $request->input('modules') ?? $request->input('modules[]') ?? [];
                $rawFunctionsInput = $request->input('functions') ?? $request->input('functions[]') ?? [];

                if (is_string($rawModulesInput) && strpos($rawModulesInput, ',') !== false) {
                    $rawModulesInput = array_map('trim', explode(',', $rawModulesInput));
                }
                if (is_string($rawFunctionsInput) && strpos($rawFunctionsInput, ',') !== false) {
                    $rawFunctionsInput = array_map('trim', explode(',', $rawFunctionsInput));
                }

                $rawModulesInput = is_array($rawModulesInput) ? $rawModulesInput : [$rawModulesInput];
                $rawFunctionsInput = is_array($rawFunctionsInput) ? $rawFunctionsInput : [$rawFunctionsInput];

                $rawModules = array_filter($rawModulesInput, fn($m) => is_string($m) && in_array(strtolower($m), $allowedModules, true));
                $rawFunctions = array_filter($rawFunctionsInput, fn($f) => is_string($f) && in_array(strtolower($f), array_map('strtolower', $allowedFunctions), true));

                $modules = array_values(array_unique(array_map('strtolower', $rawModules)));
                $functions = array_values(array_unique(array_map('strtolower', $rawFunctions)));

                if (!empty($modules) || !empty($functions)) {
                    $permissionsPayload = [
                        'modules' => $modules,
                        'functions' => $functions,
                    ];
                }

                Log::debug('admin apiStore normalized permissions', ['permissions' => $permissionsPayload]);
            }

            $insertData = [
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => Hash::make($defaultPassword),
                'full_name' => $fullName,
                'role' => $role,
                'department' => $departmentValue,
                'phone_number' => $phone,
                'address' => $address,
                'branch_id' => $branchId,
                'is_active' => 1,
                'must_change_password' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if ($permissionsPayload) {
                // Ensure permissions are stored as JSON string for direct DB insert
                $insertData['permissions'] = is_string($permissionsPayload) ? $permissionsPayload : json_encode($permissionsPayload);
            }
            Log::debug('Inserting staff with data:', $insertData);
            $staffId = DB::table('users')->insertGetId($insertData);

            // Only create staff documents if files are provided
            $hasDocuments = $request->hasFile('resume') || $request->hasFile('government_id');

            if ($hasDocuments) {
                $docDir = 'staff-documents/' . $staffId;
                $storeFile = function (string $field, string $name) use ($request, $docDir) {
                    $file = $request->file($field);
                    if ($file) {
                        $ext = $file->getClientOriginalExtension();
                        return $file->storeAs($docDir, $name . '.' . $ext, 'public');
                    }
                    return null;
                };

                $documentData = [
                    'user_id' => $staffId,
                    'resume_path' => $storeFile('resume', 'resume'),
                    'government_id_path' => $storeFile('government_id', 'government_id'),
                    'psa_birth_certificate_path' => $storeFile('psa_birth_certificate', 'psa_birth_certificate'),
                    'nbi_clearance_path' => $storeFile('nbi_clearance', 'nbi_clearance'),
                    'police_clearance_path' => $storeFile('police_clearance', 'police_clearance'),
                    'medical_certificate_path' => $storeFile('medical_certificate', 'medical_certificate'),
                    'drug_test_result_path' => $storeFile('drug_test_result', 'drug_test_result'),
                    'sss_id_path' => $storeFile('sss_id', 'sss_id'),
                    'philhealth_id_path' => $storeFile('philhealth_id', 'philhealth_id'),
                    'pagibig_mdf_path' => $storeFile('pagibig_mdf', 'pagibig_mdf'),
                    'tin_id_path' => $storeFile('tin_id', 'tin_id'),
                    'diploma_transcript_path' => $storeFile('diploma_transcript', 'diploma_transcript'),
                ];

                StaffDocument::create($documentData);
            }

            DB::commit();

            Log::info('Staff created:', ['id' => $staffId, 'role' => $role]);

            $roleLabel = 'Staff';
            if (in_array($role, ['BRANCH_MANAGER', 'MANAGER'])) $roleLabel = 'Manager';
            elseif ($role === 'HR') $roleLabel = 'HR';
            elseif ($role === 'OWNER') $roleLabel = 'Owner';

            return response()->json([
                'success' => true,
                'message' => $roleLabel . ' account created successfully!',
                'data' => ['id' => $staffId]
            ], 201);

        } catch (\Exception $e) {
            if (isset($transactionStarted) && $transactionStarted) {
                DB::rollBack();
            }
            Log::error('Staff creation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            if (isset($docDir) && $docDir) {
                Storage::disk('public')->deleteDirectory($docDir);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff account: ' . $e->getMessage()
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
                'email' => 'nullable|email|max:120|unique:users,email,' .  $id,
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
                // make them nullable here and enforce requirement after normalization
                'branchId' => 'nullable|exists:branches,id',
                'branch_id' => 'nullable|exists:branches,id',
                // allow OWNER, ADMIN, and SUPER_ADMIN as well so these accounts can be edited here
                'role' => 'required|in:BRANCH_MANAGER,MANAGER,STAFF,HR,OWNER,ADMIN,SUPER_ADMIN,SUPERADMIN,CUSTOM',
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
                    'cookies' => $request->header('cookie'),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }


            // Use Eloquent to update the user record
            $staff = User::findOrFail($id);

            // Normalize branch id (accept either branchId or branch_id). If omitted, fall back
            // to the existing staff's branch. This allows editing other fields without re-selecting
            // the branch when it already exists on the record.
            $branchId = $request->input('branchId') ?? $request->input('branch_id') ?? $staff->branch_id;

            // If role is not OWNER, branch id is required (but allow existing staff branch)
            $roleInput = $request->input('role') ?? $staff->role;
            if (strtoupper($roleInput) !== 'OWNER' && (empty($branchId) && $branchId !== '0')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The branch id field is required.'
                ], 422);
            }

            if (in_array($user->role, ['HR', 'ADMIN', 'MANAGER']) && $user->branch_id && (int) $branchId !== (int) $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            // Allow multiple managers per branch — no uniqueness check on update.

            if (in_array($user->role, ['HR', 'ADMIN', 'MANAGER']) && $user->branch_id && $staff->branch_id !== $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            $staff->username = $request->input('username');
            $updateData = [
                'email' => $request->input('email'),
                'full_name' => $request->input('fullName'),
                'phone_number' => $request->input('phone'),
                'address' => $request->input('address'),
                'branch_id' => $branchId,
                'role' => $request->input('role'),
                'department' => is_string($request->input('department')) ? strtoupper($request->input('department')) : ($request->input('department') ?? ''),
                'is_active' => (bool) $request->input('isActive'),
                'updated_at' => now(),
            ];

            if ($request->filled('password')) {
                $updateData['password'] = $request->input('password'); // Mutator will hash
            }

            $staff->update($updateData);

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
            $actor = Auth::user();
            if (! $actor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

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
            if (! in_array($user->role, ['BRANCH_MANAGER', 'MANAGER', 'STAFF', 'HR', 'CUSTOM'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user role'
                ], 400);
            }



            if (in_array($actor->role, ['HR', 'ADMIN', 'MANAGER']) && $actor->branch_id && $user->branch_id !== $actor->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            // Perform soft delete
            $user->delete();

            Log::info('Staff soft deleted:', ['id' => $id, 'role' => $user->role]);
            return response()->json([
                'success' => true,
                'message' => (in_array($user->role, ['BRANCH_MANAGER', 'MANAGER']) ? 'Manager' : ($user->role === 'HR' ? 'HR' : 'Staff')) . ' account moved to deleted history successfully!'
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
            $actor = Auth::user();

            $branchesQuery = DB::table('branches')
                ->where('is_active', 1)
                ->select('id', 'name', 'code', 'address')
                ->orderBy('name');

            // Main Branch users should see ALL branches; other admins see only their own
            if ($actor && $actor->branch_id) {
                $actorBranch = DB::table('branches')->where('id', $actor->branch_id)->first();
                $isMainBranch = $actorBranch && strtoupper($actorBranch->name ?? '') === 'MAIN BRANCH';
                
                if (!$isMainBranch && in_array($actor->role, ['HR', 'ADMIN'])) {
                    $branchesQuery->where('id', $actor->branch_id);
                }
            }

            $branches = $branchesQuery->get();

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

    protected function getDocumentFieldMap(): array
    {
        return [
            'resume' => 'resume_path',
            'government_id' => 'government_id_path',
            'psa_birth_certificate' => 'psa_birth_certificate_path',
            'nbi_clearance' => 'nbi_clearance_path',
            'police_clearance' => 'police_clearance_path',
            'medical_certificate' => 'medical_certificate_path',
            'drug_test_result' => 'drug_test_result_path',
            'sss_id' => 'sss_id_path',
            'philhealth_id' => 'philhealth_id_path',
            'pagibig_mdf' => 'pagibig_mdf_path',
            'tin_id' => 'tin_id_path',
            'diploma_transcript' => 'diploma_transcript_path',
        ];
    }

    /**
     * Download document
     */
    public function downloadDocument($id, $documentType)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            $staff = User::findOrFail($id);

            if ($user->role === 'HR' && $user->branch_id && $staff->branch_id !== $user->branch_id) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }

            $fieldMap = $this->getDocumentFieldMap();
            if (!array_key_exists($documentType, $fieldMap)) {
                return response()->json(['success' => false, 'message' => 'Invalid document type'], 400);
            }

            $doc = StaffDocument::where('user_id', '=', $id)->firstOrFail();
            $fieldName = $fieldMap[$documentType];

            if (!$doc->$fieldName) {
                return response()->json(['success' => false, 'message' => 'Document not found'], 404);
            }

            $filePath = $doc->$fieldName;
            $fullPath = storage_path('app/public/' . $filePath);

            if (!file_exists($fullPath)) {
                return response()->json(['success' => false, 'message' => 'Document file missing'], 404);
            }

            return response()->download($fullPath, basename($filePath));
        } catch (\Exception $e) {
            Log::error('Document download error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to download document'], 500);
        }
    }

    /**
     * Delete document
     */
    public function deleteDocument($id, $documentType)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            $staff = User::findOrFail($id);

            if ($user->role === 'HR' && $user->branch_id && $staff->branch_id !== $user->branch_id) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }

            $fieldMap = $this->getDocumentFieldMap();
            if (!array_key_exists($documentType, $fieldMap)) {
                return response()->json(['success' => false, 'message' => 'Invalid document type'], 400);
            }

            $doc = StaffDocument::where('user_id', '=', $id)->first();
            if (!$doc) {
                return response()->json(['success' => false, 'message' => 'Document record not found'], 404);
            }

            $fieldName = $fieldMap[$documentType];

            if ($doc->$fieldName) {
                Storage::disk('public')->delete($doc->$fieldName);
                $doc->$fieldName = null;
                $doc->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Document delete error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete document'], 500);
        }
    }

    /**
     * Upload/Replace document
     */
    public function uploadDocument(Request $request, $id, $documentType)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            $staff = User::findOrFail($id);

            if ($user->role === 'HR' && $user->branch_id && $staff->branch_id !== $user->branch_id) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }

            $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120'
            ]);

            $fieldMap = $this->getDocumentFieldMap();
            if (!array_key_exists($documentType, $fieldMap)) {
                return response()->json(['success' => false, 'message' => 'Invalid document type'], 400);
            }

            $doc = StaffDocument::firstOrCreate(['user_id' => $id]);
            $fieldName = $fieldMap[$documentType];

            // Delete old file if exists
            if ($doc->$fieldName) {
                Storage::disk('public')->delete($doc->$fieldName);
            }

            // Store new file
            $docDir = 'staff-documents/' . $id;
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $path = $file->storeAs($docDir, $documentType . '.' . $ext, 'public');

            $doc->$fieldName = $path;
            $doc->save();

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'path' => $path
            ]);
        } catch (\Exception $e) {
            Log::error('Document upload error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to upload document'], 500);
        }
    }
}
