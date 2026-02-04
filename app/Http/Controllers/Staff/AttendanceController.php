<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Clock In - Staff records arrival
     */
    public function clockIn(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $today = Carbon::now()->toDateString();

        // Check if already clocked in
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance && $attendance->time_in) {
            return response()->json([
                'ok' => false,
                'message' => 'Already clocked in today',
                'time_in' => $attendance->time_in->format('H:i')
            ], 400);
        }

        // Create or update attendance
        if (!$attendance) {
            $attendance = new Attendance([
                'user_id' => $user->id,
                'date' => $today,
            ]);
        }

        $timeIn = Carbon::now();
        $attendance->time_in = $timeIn;
        $attendance->status = $this->determineStatus($timeIn);
        $attendance->save();

        return response()->json([
            'ok' => true,
            'message' => 'Clocked in successfully',
            'time_in' => $timeIn->format('h:i A'),
            'status' => $attendance->status
        ]);
    }

    /**
     * Clock Out - Staff records departure
     */
    public function clockOut(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $today = Carbon::now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->time_in) {
            return response()->json([
                'ok' => false,
                'message' => 'Not clocked in yet'
            ], 400);
        }

        if ($attendance->time_out) {
            return response()->json([
                'ok' => false,
                'message' => 'Already clocked out today',
                'time_out' => $attendance->time_out->format('H:i')
            ], 400);
        }

        $timeOut = Carbon::now();
        $minutesWorked = $timeOut->diffInMinutes($attendance->time_in);

        $attendance->time_out = $timeOut;
        $attendance->hours_worked = $minutesWorked;
        $attendance->save();

        return response()->json([
            'ok' => true,
            'message' => 'Clocked out successfully',
            'time_out' => $timeOut->format('h:i A'),
            'hours_worked' => round($minutesWorked / 60, 2)
        ]);
    }

    /**
     * Get attendance status (current clocking status)
     */
    public function status(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false], 401);
        }

        $today = Carbon::now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'ok' => true,
                'clocked_in' => false,
                'clocked_out' => false
            ]);
        }

        return response()->json([
            'ok' => true,
            'clocked_in' => !!$attendance->time_in,
            'clocked_out' => !!$attendance->time_out,
            'time_in' => $attendance->time_in?->format('h:i A'),
            'time_out' => $attendance->time_out?->format('h:i A'),
            'status' => $attendance->status
        ]);
    }

    /**
     * Get attendance history for user
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false], 401);
        }

        $range = $request->query('range', 'thisMonth');
        $query = Attendance::where('user_id', $user->id);

        // Date filtering
        if ($range === 'today') {
            $query->where('date', Carbon::now()->toDateString());
        } elseif ($range === 'thisWeek') {
            $query->whereBetween('date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        } elseif ($range === 'thisMonth') {
            $query->whereBetween('date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]);
        }

        $records = $query->orderBy('date', 'desc')->get()->map(fn($att) => [
            'id' => $att->id,
            'date' => $att->date->format('Y-m-d'),
            'time_in' => $att->time_in?->format('h:i A'),
            'time_out' => $att->time_out?->format('h:i A'),
            'hours_worked' => is_numeric($att->hours_worked) ? round($att->hours_worked / 60, 2) : 0,
            'status' => $att->status,
        ]);

        return response()->json([
            'ok' => true,
            'data' => $records
        ]);
    }

    /**
     * Get attendance records for branch/team (for managers/HR)
     */
    public function getBranchAttendance(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['BRANCH_MANAGER', 'HR'])) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $branchId = $user->branch_id;
        $range = $request->query('range', 'today');
        $date = $range === 'today' ? Carbon::now()->toDateString() : null;

        $query = Attendance::whereHas('user', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });

        if ($date) {
            $query->where('date', $date);
        }

        $records = $query->with('user:id,full_name,username')
            ->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->get()
            ->map(fn($att) => [
                'id' => $att->id,
                'user_id' => $att->user_id,
                'user_name' => $att->user->full_name,
                'user_username' => $att->user->username,
                'date' => $att->date->format('Y-m-d'),
                'time_in' => $att->time_in?->format('h:i A'),
                'time_out' => $att->time_out?->format('h:i A'),
                'hours_worked' => is_numeric($att->hours_worked) ? round($att->hours_worked / 60, 2) : 0,
                'status' => $att->status,
            ]);

        return response()->json([
            'ok' => true,
            'data' => $records
        ]);
    }

    /**
     * Determine attendance status based on time in
     */
    private function determineStatus($timeIn)
    {
        // Assuming shift starts at 8:00 AM, late after 8:30 AM
        $shiftStart = $timeIn->copy()->setTime(8, 0, 0);
        $lateThreshold = $shiftStart->copy()->addMinutes(30);

        if ($timeIn->greaterThan($lateThreshold)) {
            return 'late';
        }

        return 'present';
    }
}

