<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MarkExistingDishProducts extends Migration
{
    /**
     * Run the migrations.
     * Mark products that have a `dish_id` as `is_dish_product = 1`.
     * This helps ensure representative dish rows are hidden from staff inventory.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('products')) {
            try {
                DB::table('products')
                    ->whereNotNull('dish_id')
                    ->update(['is_dish_product' => 1]);
            } catch (\Exception $e) {
                // swallow — migration shouldn't fail the entire run for safety
                // but log if possible
                try { logger()->warning('MarkExistingDishProducts migration failed', ['error' => $e->getMessage()]); } catch (\Throwable $t) {}
            }
        }
    }

    /**
     * Reverse the migrations.
     * Set `is_dish_product` back to 0 for rows with a `dish_id`.
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('products')) {
            try {
                DB::table('products')
                    ->whereNotNull('dish_id')
                    ->update(['is_dish_product' => 0]);
            } catch (\Exception $e) {
                try { logger()->warning('MarkExistingDishProducts rollback failed', ['error' => $e->getMessage()]); } catch (\Throwable $t) {}
            }
        }
    }
}
