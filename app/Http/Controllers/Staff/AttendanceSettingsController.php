<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceSettingsController extends Controller
{
    /**
     * Get attendance settings for the user's branch
     * Accessible by all authenticated users
     */
    public function getSettings(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'success' => false, 'message' => 'Not authenticated'], 401);
        }

        $branchId = $user->branch_id;

        // If user has no branch, use default branch 1
        if (!$branchId) {
            $branchId = 1;
        }

        $settings = AttendanceSettings::getForBranch($branchId);
        $autoClockoutTime = $settings->auto_clockout_time
            ? substr((string) $settings->auto_clockout_time, 0, 5)
            : config('attendance.default_auto_clockout_time', '22:00:00');

        return response()->json([
            'ok' => true,
            'success' => true,
            'data' => [
                'branch_id' => $branchId,
                'early_clockout_override' => $settings->early_clockout_override,
                'scheduled_time_out' => $autoClockoutTime,
                'auto_clockout_time' => $autoClockoutTime,
            ]
        ]);
    }

    /**
     * Toggle early clock-out override for a branch
     * Only accessible by OWNER and HR roles
     */
    public function toggleOverride(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'success' => false, 'message' => 'Not authenticated'], 401);
        }

        // Check if user is OWNER or HR
        if (!in_array(strtoupper((string) $user->role), ['OWNER', 'HR', 'ADMIN'])) {
            return response()->json([
                'ok' => false,
                'success' => false,
                'message' => 'Unauthorized. Only Admin, Owner, and HR can toggle this setting.'
            ], 403);
        }

        $branchId = $user->branch_id;

        // If user has no branch, use default branch 1
        if (!$branchId) {
            $branchId = 1;
        }

        $settings = AttendanceSettings::getForBranch($branchId);

        // Use the value sent in the request body
        $newValue = $request->boolean('early_clockout_override', !$settings->early_clockout_override);
        $settings->early_clockout_override = $newValue;
        $settings->save();

        return response()->json([
            'ok' => true,
            'success' => true,
            'message' => $newValue ? 'Early clock-out enabled' : 'Early clock-out disabled',
            'data' => [
                'branch_id' => $branchId,
                'early_clockout_override' => $newValue,
            ]
        ]);
    }

    /**
     * Update attendance settings for a branch
     * Only accessible by OWNER and HR roles
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'success' => false, 'message' => 'Not authenticated'], 401);
        }

        // Check if user is OWNER or HR
        if (!in_array(strtoupper((string) $user->role), ['OWNER', 'HR', 'ADMIN'])) {
            return response()->json([
                'ok' => false,
                'success' => false,
                'message' => 'Unauthorized. Only Admin, Owner, and HR can update settings.'
            ], 403);
        }

        $branchId = $user->branch_id;

        // If user has no branch, use default branch 1
        if (!$branchId) {
            $branchId = 1;
        }

        $settings = AttendanceSettings::getForBranch($branchId);

        // Update override if provided
        if ($request->has('early_clockout_override')) {
            $settings->early_clockout_override = $request->boolean('early_clockout_override');
        }

        if ($request->has('auto_clockout_time')) {
            $validated = $request->validate([
                'auto_clockout_time' => ['nullable', 'date_format:H:i'],
            ]);
            $settings->auto_clockout_time = $validated['auto_clockout_time']
                ? $validated['auto_clockout_time'] . ':00'
                : config('attendance.default_auto_clockout_time', '22:00:00');
        }

        $settings->save();

        return response()->json([
            'ok' => true,
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => [
                'branch_id' => $branchId,
                'early_clockout_override' => $settings->early_clockout_override,
                'auto_clockout_time' => $settings->auto_clockout_time
                    ? substr((string) $settings->auto_clockout_time, 0, 5)
                    : config('attendance.default_auto_clockout_time', '22:00:00'),
            ]
        ]);
    }
}
