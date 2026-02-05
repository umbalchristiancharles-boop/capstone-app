<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffDocument;
use App\Models\User; // ADD THIS IMPORT
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

            // If branch manager or HR, only show their branch
            if (in_array($user->role, ['BRANCH_MANAGER', 'HR'])) {
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
        $user = Auth::user();

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

        if ($user && $user->role === 'HR' && $user->branch_id && $staff->branch_id !== $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        // Fetch documents
        $documents = StaffDocument::where('user_id', $id)->first();
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
            $isAdmin = in_array($user->role, ['OWNER', 'ADMIN']);
            $roleRule = $isAdmin ? 'nullable|in:BRANCH_MANAGER' : 'required|in:BRANCH_MANAGER,STAFF,HR';
            $fileRule = 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120';

            $request->validate([
                'username' => 'required|string|max:50|unique:users,username',
                'email' => 'required|email|max:120|unique:users,email',
                'fullName' => 'required|string|max:150',
                'phone' => 'nullable|string|max:30',
                'address' => 'nullable|string|max:255',
                // Accept either branchId (camelCase) or branch_id (snake_case)
                'branchId' => 'required_without:branch_id|exists:branches,id',
                'branch_id' => 'required_without:branchId|exists:branches,id',
                'role' => $roleRule,
                // Required documents (images or PDF, max 5MB each)
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
            ]);

            $role = $isAdmin ? 'BRANCH_MANAGER' : $request->input('role');

            // Accept both camelCase and snake_case for robustness
            $fullName = $request->input('fullName') ?? $request->input('full_name');
            $email = $request->input('email') ?? $request->input('email');
            $defaultPassword = 'ChikinTayo_2526';
            $branchId = $request->input('branchId') ?? $request->input('branch_id');

            // Check if branch already has a manager (if creating BRANCH_MANAGER)
            if ($role === 'BRANCH_MANAGER') {
                $existingManager = DB::table('users')
                    ->where('branch_id', $branchId)
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

            if ($user->role === 'HR' && $user->branch_id && (int) $branchId !== (int) $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            $transactionStarted = false;
            $docDir = null;

            DB::beginTransaction();
            $transactionStarted = true;

            $insertData = [
                'username' => $request->input('username'),
                'email' => $email,
                'password_hash' => Hash::make($defaultPassword),
                'full_name' => $fullName,
                'role' => $role,
                'phone_number' => $request->input('phone'),
                'address' => $request->input('address'),
                'branch_id' => $branchId,
                'is_active' => 1,
                'must_change_password' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            Log::debug('Inserting staff with data:', $insertData);
            $staffId = DB::table('users')->insertGetId($insertData);

            $docDir = 'staff-documents/' . $staffId;
            $storeFile = function (string $field, string $name) use ($request, $docDir) {
                $file = $request->file($field);
                $ext = $file->getClientOriginalExtension();
                return $file->storeAs($docDir, $name . '.' . $ext, 'public');
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

            DB::commit();

            Log::info('Staff created:', ['id' => $staffId, 'role' => $role]);

            return response()->json([
                'success' => true,
                'message' => ($role === 'BRANCH_MANAGER' ? 'Branch Manager' : ($role === 'HR' ? 'HR' : 'Staff')) . ' account created successfully!',
                'data' => ['id' => $staffId]
            ], 201);

        } catch (\Exception $e) {
            if (isset($transactionStarted) && $transactionStarted) {
                DB::rollBack();
            }
            Log::error('Staff creation error: ' . $e->getMessage());

            if (isset($docDir) && $docDir) {
                Storage::disk('public')->deleteDirectory($docDir);
            }

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

            if ($user->role === 'HR' && $user->branch_id && (int) $branchId !== (int) $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

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

            if ($user->role === 'HR' && $user->branch_id && $staff->branch_id !== $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

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
            if (! in_array($user->role, ['BRANCH_MANAGER', 'STAFF', 'HR'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user role'
                ], 400);
            }



            if ($actor->role === 'HR' && $actor->branch_id && $user->branch_id !== $actor->branch_id) {
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
            $actor = Auth::user();

            $branchesQuery = DB::table('branches')
                ->where('is_active', 1)
                ->select('id', 'name', 'code', 'address')
                ->orderBy('name');

            if ($actor && $actor->role === 'HR' && $actor->branch_id) {
                $branchesQuery->where('id', $actor->branch_id);
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

            $doc = StaffDocument::where('user_id', $id)->firstOrFail();
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

            $doc = StaffDocument::where('user_id', $id)->first();
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
