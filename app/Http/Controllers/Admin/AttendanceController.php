<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Support\Permission;

class AttendanceController extends Controller
{
    /**
     * Get attendance records for admin/owner across branches.
     * Optional query params: branch_id, range (today|thisWeek|thisMonth)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        // Allow OWNER, ADMIN, HR, or CUSTOM accounts with HR permissions/functions
        if (! Permission::allowed($user, ['OWNER', 'ADMIN', 'HR'], ['hr'], ['hr.attendance'])) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $branchId = $request->query('branch_id');
        $range = $request->query('range', 'today');

        $query = Attendance::with('user.branch')->whereHas('user', function ($q) use ($branchId) {
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        });

        $now = Carbon::now();
        $dateRange = match ($range) {
            'yesterday' => [$now->copy()->subDay(), $now->copy()->subDay()],
            'thisWeek' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'lastWeek' => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'thisMonth' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'lastMonth' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            default => [$now->copy(), $now->copy()],
        };

        $query->whereBetween('date', array_map(
            static fn (Carbon $date) => $date->toDateString(),
            $dateRange
        ));

        $records = $query->orderBy('date', 'desc')->orderBy('time_in', 'desc')->get()->map(function ($att) {
            return [
                'id' => $att->id,
                'user_id' => $att->user_id,
                'user_name' => $att->user?->full_name ?? ($att->user?->username ?? 'Unknown'),
                'branch_id' => $att->user?->branch?->id ?? null,
                'branch_name' => $att->user?->branch?->name ?? null,
                'date' => $att->date?->format('Y-m-d') ?? null,
                'time_in' => $att->time_in?->format('h:i A') ?? null,
                'time_out' => $att->time_out?->format('h:i A') ?? null,
                'hours_worked' => is_numeric($att->hours_worked) ? round($att->hours_worked / 60, 2) : 0,
                'status' => $att->status,
            ];
        });

        return response()->json(['ok' => true, 'data' => $records]);
    }
}
