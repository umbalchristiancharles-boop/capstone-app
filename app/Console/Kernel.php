<?php

namespace App\Console;

use App\Console\Commands\UpdateBranchPasswords;
use App\Console\Commands\FetchGmailReplies;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run the branch password update command daily at 00:00 (midnight)
        $schedule->command('branches:update-passwords')
                 ->dailyAt('00:00')
                 ->withoutOverlapping()
                 ->onFailure(function () {
                     \Illuminate\Support\Facades\Log::error('Failed to update branch passwords');
                 })
                 ->onSuccess(function () {
                     \Illuminate\Support\Facades\Log::info('Successfully completed daily branch password update');
                 });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
