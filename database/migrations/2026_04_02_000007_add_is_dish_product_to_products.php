<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddIsDishProductToProducts extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_dish_product')) {
                $table->boolean('is_dish_product')->default(false)->after('is_kitchen_dish');
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'is_dish_product')) {
                $table->dropColumn('is_dish_product');
            }
        });
    }
}
