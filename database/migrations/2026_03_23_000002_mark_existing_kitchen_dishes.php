<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Mark existing products as kitchen dishes based on supplier_name KITCHEN
        // Heuristic: only mark products that are published (likely menu items)
        DB::table('products')
            ->whereRaw("COALESCE(UPPER(TRIM(supplier_name)), '') = 'KITCHEN'")
            ->where('is_published', 1)
            ->update(['is_kitchen_dish' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('products')
            ->whereRaw("COALESCE(UPPER(TRIM(supplier_name)), '') = 'KITCHEN'")
            ->where('is_published', 1)
            ->update(['is_kitchen_dish' => false]);
    }
};
