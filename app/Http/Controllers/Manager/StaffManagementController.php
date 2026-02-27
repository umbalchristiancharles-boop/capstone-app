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
            'department' => 'nullable|string|max:100',
        ]);

        $defaultPassword = config('chikintayo.default_password');

        $dept = $request->input('department', null);
        if (is_string($dept) && $dept !== '') $dept = strtoupper($dept);

        $staff = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number,
            'department' => $dept,
            'password' => $defaultPassword, // Mutator will hash this automatically
            'role' => 'STAFF', // Branch Manager can only create STAFF
            'branch_id' => $user->branch_id, // Assign to manager's branch
            'is_active' => 1,
            'must_change_password' => 1,
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
            'department' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $updateData = $request->only(['full_name', 'email', 'phone_number', 'department', 'is_active']);
        if (isset($updateData['department']) && is_string($updateData['department'])) {
            $updateData['department'] = strtoupper($updateData['department']);
        }

        $staff->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Staff updated successfully',
            'staff' => [
                'id' => $staff->id,
                'username' => $staff->username,
                'full_name' => $staff->full_name,
                'email' => $staff->email,
                'phone_number' => $staff->phone_number,
                'department' => $staff->department,
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
     * Get staff attendance for manager's branch (real data)
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

        // Get all staff in the branch (including the manager)
        $branchUsers = \App\Models\User::where('branch_id', $user->branch_id)
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->get();

        // Get attendance records for the date
        $attendanceRecords = \App\Models\Attendance::where('date', $date)
            ->whereIn('user_id', $branchUsers->pluck('id'))
            ->get()
            ->keyBy('user_id');

        // Build attendance data for each user
        $attendance = [];
        $presentCount = 0;
        $absentCount = 0;

        foreach ($branchUsers as $staff) {
            $att = $attendanceRecords->get($staff->id);

            if ($att && $att->time_in) {
                $presentCount++;
                $attendance[] = [
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->full_name ?? $staff->username,
                    'clock_in' => $att->time_in ? $att->time_in->format('H:i') : null,
                    'clock_out' => $att->time_out ? $att->time_out->format('H:i') : null,
                    'status' => $att->status ?? ($att->time_out ? 'completed' : 'on_duty'),
                    'hours_worked' => is_numeric($att->hours_worked) ? round($att->hours_worked / 60, 2) : 0,
                ];
            } else {
                $absentCount++;
                $attendance[] = [
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->full_name ?? $staff->username,
                    'clock_in' => null,
                    'clock_out' => null,
                    'status' => 'absent',
                    'hours_worked' => 0,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'date' => $date,
            'attendance' => $attendance,
            'summary' => [
                'total_staff' => $branchUsers->count(),
                'present' => $presentCount,
                'absent' => $absentCount,
            ],
        ]);
    }
}
