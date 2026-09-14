<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceSettings;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AutoClockOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-clock-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clock out staff who forgot to clock out at their branch schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $today = $now->toDateString();

        $this->info('Running auto clock-out for staff who forgot to clock out...');

        $attendances = Attendance::where('date', $today)
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->with('user')
            ->get();

        if ($attendances->isEmpty()) {
            $this->info('No staff need auto clock-out. All staff have already clocked out.');
            return 0;
        }

        $count = 0;
        foreach ($attendances as $attendance) {
            $scheduledTime = AttendanceSettings::getForBranch($attendance->user->branch_id ?? 1)->auto_clockout_time
                ?: config('attendance.default_auto_clockout_time', '22:00:00');
            $timeOut = Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . substr((string) $scheduledTime, 0, 5));

            $timeIn = Carbon::parse($attendance->time_in);
            $timeoutTarget = $timeIn->greaterThan($timeOut) ? $timeIn : $timeOut;
            $automaticTimeOut = $timeoutTarget->copy()->addMinutes((int) config('attendance.auto_clockout_grace_minutes', 5));
            if ($now->lt($automaticTimeOut)) {
                continue;
            }
            
            // Calculate hours worked
            $actualTimeOut = $timeIn->greaterThan($timeOut) ? $now : $timeOut;
            $minutesWorked = $actualTimeOut->diffInMinutes($timeIn, true);
            
            $attendance->time_out = $actualTimeOut;
            $attendance->hours_worked = $minutesWorked;
            $attendance->save();
            app(\App\Http\Controllers\Api\PayrollController::class)->syncAttendance($attendance);

            $count++;
            $this->line("Auto clocked out: {$attendance->user->full_name} (ID: {$attendance->user_id}) - Hours: " . round($minutesWorked / 60, 2));
        }

        $this->info("Successfully auto clocked out {$count} staff member(s) at their branch schedule.");
        return 0;
    }
}