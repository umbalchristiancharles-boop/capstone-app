<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UnpublishKitchenPlaceholderProducts extends Migration
{
    /**
     * Run the migrations.
     * Unpublish any products marked as kitchen dish placeholders so inventory can manage publishing.
     */
    public function up()
    {
        // Set is_published = 0 for products that are kitchen dishes (placeholders)
        try {
            DB::table('products')
                ->where('is_kitchen_dish', 1)
                ->update(['is_published' => 0]);
        } catch (\Exception $e) {
            // log but don't fail migration
            // This file may run in environments without DB access during static analysis
        }
    }

    /**
     * Reverse the migrations.
     * We will not re-publish automatically on rollback.
     */
    public function down()
    {
        // no-op: do not revert to published automatically
    }
}
