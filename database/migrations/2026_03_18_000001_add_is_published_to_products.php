<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPublishedToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('products', 'is_published')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_published')->default(true)->after('branch_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('products', 'is_published')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_published');
            });
        }
    }
}
