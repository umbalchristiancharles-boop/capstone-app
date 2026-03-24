<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('dish_ingredients', function (Blueprint $table) {
            if (Schema::hasColumn('dish_ingredients', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }

    public function down()
    {
        Schema::table('dish_ingredients', function (Blueprint $table) {
            if (!Schema::hasColumn('dish_ingredients', 'quantity')) {
                $table->string('quantity')->nullable()->after('name');
            }
        });
    }
};
