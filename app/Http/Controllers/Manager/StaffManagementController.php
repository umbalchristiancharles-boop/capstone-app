<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffManagementController extends Controller
{
    /**
     * Get staff list for branch manager's branch
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $staff = User::where('branch_id', $user->branch_id)
            ->where('role', 'STAFF')
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'username' => $s->username,
                    'full_name' => $s->full_name,
                    'email' => $s->email,
                    'phone_number' => $s->phone_number,
                    'avatar_url' => $s->avatar_url,
                    'is_active' => $s->is_active,
                    'created_at' => $s->created_at->format('M d, Y'),
                ];
            });

        return response()->json([
            'success' => true,
            'staff' => $staff,
            'total' => $staff->count(),
        ]);
    }

    /**
     * Create new staff member (Branch Manager can only create STAFF role)
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        $staff = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number,
            'password_hash' => Hash::make($request->password),
            'role' => 'STAFF', // Branch Manager can only create STAFF
            'branch_id' => $user->branch_id, // Assign to manager's branch
            'is_active' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Staff member created successfully',
            'staff' => [
                'id' => $staff->id,
                'username' => $staff->username,
                'full_name' => $staff->full_name,
                'email' => $staff->email,
            ],
        ], 201);
    }

    /**
     * Update staff member
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $staff = User::find($id);

        if (!$staff || $staff->branch_id !== $user->branch_id || $staff->role !== 'STAFF') {
            return response()->json(['success' => false, 'message' => 'Staff not found or unauthorized'], 404);
        }

        $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone_number' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        $staff->update($request->only(['full_name', 'email', 'phone_number', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Staff updated successfully',
            'staff' => [
                'id' => $staff->id,
                'username' => $staff->username,
                'full_name' => $staff->full_name,
                'email' => $staff->email,
                'phone_number' => $staff->phone_number,
                'is_active' => $staff->is_active,
            ],
        ]);
    }

    /**
     * Get staff schedules (mock data for now)
     */
    public function schedules(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Mock schedule data - implement actual schedule table later
        $schedules = [
            [
                'staff_id' => 1,
                'staff_name' => 'Juan Dela Cruz',
                'date' => now()->format('Y-m-d'),
                'shift_start' => '08:00',
                'shift_end' => '16:00',
                'status' => 'scheduled',
            ],
            [
                'staff_id' => 2,
                'staff_name' => 'Maria Santos',
                'date' => now()->format('Y-m-d'),
                'shift_start' => '16:00',
                'shift_end' => '00:00',
                'status' => 'scheduled',
            ],
        ];

        return response()->json([
            'success' => true,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Get staff attendance (mock data for now)
     */
    public function attendance(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'BRANCH_MANAGER' || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $date = $request->query('date', now()->format('Y-m-d'));

        // Mock attendance data
        $attendance = [
            [
                'staff_id' => 1,
                'staff_name' => 'Juan Dela Cruz',
                'clock_in' => '08:05',
                'clock_out' => null,
                'status' => 'on_duty',
                'hours_worked' => '6.5',
            ],
            [
                'staff_id' => 2,
                'staff_name' => 'Maria Santos',
                'clock_in' => '16:00',
                'clock_out' => null,
                'status' => 'on_duty',
                'hours_worked' => '2.0',
            ],
        ];

        return response()->json([
            'success' => true,
            'date' => $date,
            'attendance' => $attendance,
            'summary' => [
                'total_staff' => 2,
                'present' => 2,
                'absent' => 0,
            ],
        ]);
    }
}
