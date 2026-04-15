<?php

namespace App\Console\Commands;

use App\Services\BranchPasswordService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateBranchPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branches:update-passwords {--force : Force password update even if already updated today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update default passwords for all active branches daily';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting daily branch password update...');

        try {
            $updatedCount = BranchPasswordService::updateAllBranchPasswords();
            
            $this->info("✓ Successfully updated passwords for {$updatedCount} branches");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Error updating branch passwords: ' . $e->getMessage());
            Log::error('UpdateBranchPasswords command failed: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
