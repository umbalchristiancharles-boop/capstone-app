<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class BackfillRealStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:backfill-real-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill real_stock column by summing stock across grouped products (by sku or name)';

    public function handle()
    {
        $this->info('Starting backfill of real_stock...');

        // First, handle groups with SKU
        $this->info('Processing SKU groups...');
        $skuGroups = Product::select('branch_id', 'sku')
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->groupBy('branch_id', 'sku')
            ->get();

        foreach ($skuGroups as $grp) {
            Product::recomputeRealStockForGroup($grp->branch_id, $grp->sku, null);
        }

        $this->info('Processing name-based groups (no SKU)...');
        // now handle name-based groups for products without SKU
        $nameGroups = Product::select('branch_id', DB::raw('TRIM(UPPER(name)) as nname'))
            ->where(function($q){ $q->whereNull('sku')->orWhere('sku', ''); })
            ->groupBy('branch_id', DB::raw('TRIM(UPPER(name))'))
            ->get();

        foreach ($nameGroups as $g) {
            Product::recomputeRealStockForGroup($g->branch_id, null, $g->nname);
        }

        $this->info('Backfill complete.');
        return 0;
    }
}
