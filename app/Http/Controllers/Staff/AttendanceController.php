<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Clock In - Staff records arrival
     */
    public function clockIn(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'STAFF') {
            return response()->json(['success' => false, 'message' => 'Unauthorized - Staff only'], 403);
        }

        // TODO: Check if already clocked in today
        // TODO: Save to attendance table

        return response()->json([
            'success' => true,
            'message' => 'Clocked in successfully',
            'data' => [
                'staff_id' => $user->id,
                'staff_name' => $user->full_name,
                'clock_in_time' => now()->format('H:i:s'),
                'date' => now()->format('Y-m-d'),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Clock Out - Staff records departure
     */
    public function clockOut(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'STAFF') {
            return response()->json(['success' => false, 'message' => 'Unauthorized - Staff only'], 403);
        }

        // TODO: Check if clocked in today
        // TODO: Update attendance record with clock out time

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully',
            'data' => [
                'staff_id' => $user->id,
                'staff_name' => $user->full_name,
                'clock_out_time' => now()->format('H:i:s'),
                'date' => now()->format('Y-m-d'),
                'hours_worked' => '8.5', // TODO: Calculate from clock_in time
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get attendance status for today
     */
    public function status(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'STAFF') {
            return response()->json(['success' => false, 'message' => 'Unauthorized - Staff only'], 403);
        }

        // TODO: Query actual attendance table
        // Mock data for now
        return response()->json([
            'success' => true,
            'status' => [
                'is_clocked_in' => false,
                'clock_in_time' => null,
                'clock_out_time' => null,
                'hours_worked' => 0,
                'shift_start' => '08:00',
                'shift_end' => '16:00',
                'date' => now()->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get attendance history for staff
     */
    public function history(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'STAFF') {
            return response()->json(['success' => false, 'message' => 'Unauthorized - Staff only'], 403);
        }

        $limit = $request->query('limit', 10);

        // TODO: Query actual attendance table
        // Mock data for now
        $history = [
            [
                'date' => now()->subDays(1)->format('Y-m-d'),
                'clock_in' => '08:05',
                'clock_out' => '16:10',
                'hours_worked' => '8.08',
                'status' => 'completed',
            ],
            [
                'date' => now()->subDays(2)->format('Y-m-d'),
                'clock_in' => '08:00',
                'clock_out' => '16:00',
                'hours_worked' => '8.00',
                'status' => 'completed',
            ],
        ];

        return response()->json([
            'success' => true,
            'history' => array_slice($history, 0, $limit),
            'total' => count($history),
        ]);
    }
}
