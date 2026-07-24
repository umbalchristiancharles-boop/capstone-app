<?php

namespace App\Console\Commands;

use App\Models\Attendance;
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
    protected $description = 'Automatically clock out staff who forgot to clock out after 10 PM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->toDateString();
        $currentTime = Carbon::now();
        
        // Only run if current time is 22:00 (10 PM) or later
        if ($currentTime->hour < 22) {
            $this->info('Auto clock-out will only run at or after 10 PM (22:00).');
            return 0;
        }

        $this->info('Running auto clock-out for staff who forgot to clock out...');

        // Find all attendance records for today where time_in is set but time_out is null
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
            // Set time_out to 10:00 PM (22:00:00)
            $timeOut = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' 22:00:00');
            
            // Calculate hours worked
            $minutesWorked = $timeOut->diffInMinutes($attendance->time_in);
            
            $attendance->time_out = $timeOut;
            $attendance->hours_worked = $minutesWorked;
            $attendance->save();

            $count++;
            $this->line("Auto clocked out: {$attendance->user->full_name} (ID: {$attendance->user_id}) - Hours: " . round($minutesWorked / 60, 2));
        }

        $this->info("Successfully auto clocked out {$count} staff member(s) at 10:00 PM.");
        return 0;
    }
}