<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class UnpublishUnexpectedPublishedProducts extends Migration
{
    /**
     * Run the migrations.
     * Unpublish specific products that were unintentionally published.
     */
    public function up()
    {
        // IDs found in the provided SQL dump that should not be published yet
        $ids = [124, 125, 126];

        DB::table('products')->whereIn('id', $ids)->update(['is_published' => 0]);
    }

    /**
     * Reverse the migrations.
     * Re-publish the products in case a rollback is desired.
     */
    public function down()
    {
        $ids = [124, 125, 126];
        DB::table('products')->whereIn('id', $ids)->update(['is_published' => 1]);
    }
}
