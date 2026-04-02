<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LinkHotdogIngredient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:link-hotdog {--dish=35} {--product=152} {--mark-migration : Mark migration as applied}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link hotdog dish_ingredients to supplier product and optionally mark migration applied';

    public function handle()
    {
        $dishId = (int) $this->option('dish');
        $productId = (int) $this->option('product');

        if (! Schema::hasTable('dish_ingredients') || ! Schema::hasTable('products')) {
            $this->error('Required tables not found: dish_ingredients or products');
            return 1;
        }

        DB::beginTransaction();
        try {
            $updated = DB::table('dish_ingredients')
                ->where('dish_id', $dishId)
                ->where(function ($q) { $q->whereNull('product_id')->orWhere('product_id', 0); })
                ->update(['product_id' => DB::raw((string) $productId)]);

            $this->info("Updated {$updated} dish_ingredient rows to product_id={$productId}");

            if ($this->option('mark-migration')) {
                $max = DB::table('migrations')->max('batch');
                $batch = $max ? $max + 1 : 1;
                DB::table('migrations')->insert([
                    'migration' => '2026_04_02_000010_link_hotdog_ingredient_fix',
                    'batch' => $batch,
                ]);
                $this->info('Inserted migration record to migrations table');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
