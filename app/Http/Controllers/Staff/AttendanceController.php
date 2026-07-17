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
     * Works for: STAFF, BRANCH_MANAGER, OWNER, ADMIN, HR
     * Restricted for: Super Admin Logistics role
     */
    public function clockIn(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'success' => false, 'message' => 'Not authenticated'], 401);
        }

        // Reject clock-in requests from Super Admin with Logistics module
        if ($this->isLogisticsAdmin($user)) {
            return response()->json([
                'ok' => false,
                'success' => false,
                'message' => 'Super Admin Logistics role cannot perform clock in/out operations',
                'restricted_role' => 'logistics_admin'
            ], 403);
        }

        $today = Carbon::now()->toDateString();

        // Check if already clocked in today
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance && $attendance->time_in) {
            return response()->json([
                'ok' => false,
                'success' => false,
                'message' => 'Already clocked in today',
                'time_in' => $attendance->time_in->format('H:i')
            ], 400);
        }

        // Geofencing validation
        $userLatitude = $request->input('latitude');
        $userLongitude = $request->input('longitude');

        if ($userLatitude && $userLongitude) {
            $geofencingCheck = $this->validateGeofencing($user, $userLatitude, $userLongitude);
            
            if (!$geofencingCheck['valid']) {
                return response()->json([
                    'ok' => false,
                    'success' => false,
                    'message' => $geofencingCheck['message'],
                    'geofencing_error' => true,
                    'distance' => $geofencingCheck['distance'] ?? null,
                    'allowed_radius' => $geofencingCheck['allowed_radius'] ?? null
                ], 403);
            }
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
            'success' => true,
            'message' => 'Clocked in successfully',
            'time_in' => $timeIn->format('h:i A'),
            'status' => $attendance->status,
            'is_clocked_in' => true
        ]);
    }

    /**
     * Clock Out - Staff records departure
     * Works for: STAFF, BRANCH_MANAGER, OWNER, ADMIN, HR
     * Restricted for: Super Admin Logistics role
     */
    public function clockOut(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'success' => false, 'message' => 'Not authenticated'], 401);
        }

        // Reject clock-out requests from Super Admin with Logistics module
        if ($this->isLogisticsAdmin($user)) {
            return response()->json([
                'ok' => false,
                'success' => false,
                'message' => 'Super Admin Logistics role cannot perform clock in/out operations',
                'restricted_role' => 'logistics_admin'
            ], 403);
        }

        $today = Carbon::now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->time_in) {
            return response()->json([
                'ok' => false,
                'success' => false,
                'message' => 'Not clocked in yet'
            ], 400);
        }

        if ($attendance->time_out) {
            return response()->json([
                'ok' => false,
                'success' => false,
                'message' => 'Already clocked out today',
                'time_out' => $attendance->time_out->format('H:i')
            ], 400);
        }

        // Geofencing validation for clock out
        $userLatitude = $request->input('latitude');
        $userLongitude = $request->input('longitude');

        if ($userLatitude && $userLongitude) {
            $geofencingCheck = $this->validateGeofencing($user, $userLatitude, $userLongitude);
            
            if (!$geofencingCheck['valid']) {
                return response()->json([
                    'ok' => false,
                    'success' => false,
                    'message' => $geofencingCheck['message'],
                    'geofencing_error' => true,
                    'distance' => $geofencingCheck['distance'] ?? null,
                    'allowed_radius' => $geofencingCheck['allowed_radius'] ?? null
                ], 403);
            }
        }

        // Get user's branch ID
        $branchId = $user->branch_id;

        // If user has no branch, allow clock out (for system admins without branch assignment)
        if (!$branchId) {
            $branchId = 1; // Default to branch 1
        }

        // Check if early clock-out is allowed
        $currentTime = Carbon::now();
        $scheduledTimeOut = Carbon::now()->format('Y-m-d') . ' ' . config('attendance.default_time_out');
        $scheduledTimeOut = Carbon::parse($scheduledTimeOut);

        // Get branch override setting
        $settings = \App\Models\AttendanceSettings::getForBranch($branchId);
        $overrideEnabled = $settings->early_clockout_override;

        // If current time is before scheduled time AND override is not enabled, deny clock out
        if ($currentTime->lessThan($scheduledTimeOut) && !$overrideEnabled) {
            return response()->json([
                'ok' => false,
                'success' => false,
                'message' => 'Clock out not allowed before scheduled time.',
                'scheduled_time_out' => $scheduledTimeOut->format('h:i A'),
                'current_time' => $currentTime->format('h:i A'),
                'override_enabled' => $overrideEnabled
            ], 403);
        }

        $timeOut = Carbon::now();
        $minutesWorked = $timeOut->diffInMinutes($attendance->time_in);

        $attendance->time_out = $timeOut;
        $attendance->hours_worked = $minutesWorked;
        $attendance->save();

        return response()->json([
            'ok' => true,
            'success' => true,
            'message' => 'Clocked out successfully',
            'time_out' => $timeOut->format('h:i A'),
            'hours_worked' => round($minutesWorked / 60, 2),
            'is_clocked_in' => false
        ]);
    }

    /**
     * Get attendance status (current clocking status)
     * Works for all roles: STAFF, BRANCH_MANAGER, OWNER, ADMIN, HR
     */
    public function status(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'ok' => true,
                    'success' => true,
                    'clocked_in' => false,
                    'clocked_out' => false,
                    'status' => ['is_clocked_in' => false, 'is_clocked_out' => false, 'clock_in_time' => null, 'clock_out_time' => null, 'hours_worked' => 0]
                ], 200);
            }

            $today = Carbon::now()->toDateString();
            $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

            $clockedIn = false;
            $clockedOut = false;
            $clockInTime = null;
            $clockOutTime = null;
            $hoursWorked = 0;

            if ($attendance) {
                $timeIn = $attendance->getAttribute('time_in');
                $timeOut = $attendance->getAttribute('time_out');
                $clockedIn = !empty($timeIn);
                $clockedOut = !empty($timeOut);

                if ($clockedIn && $timeIn) {
                    try { $clockInTime = Carbon::parse($timeIn)->format('h:i A'); } catch (\Exception $e) { $clockInTime = null; }
                }
                if ($clockedOut && $timeOut) {
                    try { $clockOutTime = Carbon::parse($timeOut)->format('h:i A'); } catch (\Exception $e) { $clockOutTime = null; }
                }
                $hw = $attendance->getAttribute('hours_worked');
                if (is_numeric($hw)) { $hoursWorked = round($hw / 60, 2); }
            }

            return response()->json([
                'ok' => true,
                'success' => true,
                'clocked_in' => $clockedIn,
                'clocked_out' => $clockedOut,
                'time_in' => $clockInTime,
                'time_out' => $clockOutTime,
                'status' => ['is_clocked_in' => $clockedIn, 'is_clocked_out' => $clockedOut, 'clock_in_time' => $clockInTime, 'clock_out_time' => $clockOutTime, 'hours_worked' => $hoursWorked]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => true,
                'success' => true,
                'clocked_in' => false,
                'clocked_out' => false,
                'status' => ['is_clocked_in' => false, 'is_clocked_out' => false, 'clock_in_time' => null, 'clock_out_time' => null, 'hours_worked' => 0]
            ], 200);
        }
    }

    /**
     * Get attendance history for user
     * Works for all roles: STAFF, BRANCH_MANAGER, OWNER, ADMIN, HR
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'success' => false], 401);
        }

        $range = $request->query('range', 'thisMonth');
        $limit = $request->query('limit', null);
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

        if ($limit) {
            $query->limit((int) $limit);
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
            'success' => true,
            'data' => $records,
            'history' => $records
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

    /**
     * Validate geofencing - check if user is within allowed radius of their branch
     */
    private function validateGeofencing($user, $userLatitude, $userLongitude)
    {
        // Get user's branch
        $branch = \App\Models\Branch::find($user->branch_id);
        
        // If no branch assigned or branch doesn't exist, allow clock in/out
        if (!$branch) {
            return ['valid' => true];
        }

        // If branch has no coordinates, allow clock in/out (no geofencing configured)
        if (!$branch->latitude || !$branch->longitude) {
            return ['valid' => true];
        }

        // Get geofencing radius (in meters)
        $radius = $branch->geofencing_radius;
        
        // If no radius configured, use default of 100 meters
        if (!$radius || $radius <= 0) {
            $radius = 100;
        }

        // Calculate distance between user and branch using Haversine formula
        $distance = $this->calculateDistance(
            $userLatitude,
            $userLongitude,
            $branch->latitude,
            $branch->longitude
        );

        // Check if user is within allowed radius
        if ($distance > $radius) {
            return [
                'valid' => false,
                'message' => "You are not within the branch vicinity. You are {$distance} meters away. Allowed radius is {$radius} meters.",
                'distance' => round($distance, 2),
                'allowed_radius' => $radius
            ];
        }

        return ['valid' => true];
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in meters
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    /**
     * Check if user is a Super Admin with Logistics module
     * Super Admin Logistics role is restricted from clock in/out operations
     */
    private function isLogisticsAdmin($user)
    {
        if (!$user) {
            return false;
        }

        // Check if user has SUPER_ADMIN or ADMIN role
        $isSuperAdmin = in_array(strtoupper($user->role ?? ''), ['SUPER_ADMIN', 'ADMIN']);

        // Check if user has 'logistics' in modules permissions
        $permissions = $user->permissions ?? [];
        if (is_string($permissions)) {
            try {
                $permissions = json_decode($permissions, true) ?: [];
            } catch (\Throwable $e) {
                return false;
            }
        }

        $modules = $permissions['modules'] ?? [];
        $hasLogisticsModule = in_array('logistics', array_map('strtolower', $modules));

        // User is logistics admin if they are SUPER_ADMIN/ADMIN AND have logistics module
        return $isSuperAdmin && $hasLogisticsModule;
    }
}
