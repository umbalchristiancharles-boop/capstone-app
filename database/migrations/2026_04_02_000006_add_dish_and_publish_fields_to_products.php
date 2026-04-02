<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddDishAndPublishFieldsToProducts extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'dish_id')) {
                $table->unsignedBigInteger('dish_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('products', 'published_by')) {
                $table->unsignedBigInteger('published_by')->nullable()->after('branch_id');
            }
            if (!Schema::hasColumn('products', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('published_by');
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'published_at')) {
                $table->dropColumn('published_at');
            }
            if (Schema::hasColumn('products', 'published_by')) {
                $table->dropColumn('published_by');
            }
            if (Schema::hasColumn('products', 'dish_id')) {
                $table->dropColumn('dish_id');
            }
        });
    }
}
