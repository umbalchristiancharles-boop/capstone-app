<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExpiredProductReport;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CreateTestDisposalData extends Command
{
    protected $signature = 'test:create-disposal-data';
    protected $description = 'Create test expired product disposal data for current user branch';

    public function handle()
    {
        $user = Auth::user();
        
        if (!$user) {
            $this->error('No user logged in. Please login first via the web interface.');
            return 1;
        }

        $this->info("User: {$user->full_name} (Branch: {$user->branch_id})");

        $product = Product::where('branch_id', $user->branch_id)->first();
        
        if (!$product) {
            $this->error('No products found in your branch. Please create a product first.');
            return 1;
        }

        $this->info("Found product: {$product->name}");

        // Create 3 test disposal records
        for ($i = 0; $i < 3; $i++) {
            $report = ExpiredProductReport::create([
                'product_id' => $product->id,
                'branch_id' => $user->branch_id,
                'reported_by' => $user->id,
                'quantity' => rand(5, 20),
                'notes' => "Test expired product report #" . ($i + 1) . " - " . date('Y-m-d H:i:s'),
                'status' => $i === 0 ? 'pending' : ($i === 1 ? 'reviewed' : 'resolved')
            ]);

            $this->info("Created test report ID: {$report->id} (Status: {$report->status})");
        }

        $this->info('Done! Refresh the inventory page to see the disposal list.');
        return 0;
    }
}