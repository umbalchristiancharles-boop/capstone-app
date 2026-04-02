<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class LinkHotdogIngredientToProductFix20260402 extends Migration
{
    /**
     * Run the migrations.
     * Link dish_ingredients for a known dish to a supplier product when missing.
     * Adjust `dish_id` and `product_id` values below if your IDs differ.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('dish_ingredients') && Schema::hasTable('products')) {
            try {
                DB::table('dish_ingredients')
                    ->where('dish_id', 35)
                    ->where(function ($q) {
                        $q->whereNull('product_id')->orWhere('product_id', 0);
                    })
                    ->update(['product_id' => 152]);
            } catch (\Exception $e) {
                try { logger()->warning('LinkHotdogIngredientToProductFix20260402 migration failed', ['error' => $e->getMessage()]); } catch (\Throwable $t) {}
            }
        }
    }

    /**
     * Reverse the migrations.
     * Unlink the product_id for those rows set by this migration.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('dish_ingredients')) {
            try {
                DB::table('dish_ingredients')
                    ->where('dish_id', 35)
                    ->where('product_id', 152)
                    ->update(['product_id' => null]);
            } catch (\Exception $e) {
                try { logger()->warning('LinkHotdogIngredientToProductFix20260402 rollback failed', ['error' => $e->getMessage()]); } catch (\Throwable $t) {}
            }
        }
    }
}
