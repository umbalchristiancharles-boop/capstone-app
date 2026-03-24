<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('dish_ingredients', function (Blueprint $table) {
            if (!Schema::hasColumn('dish_ingredients', 'per_serving')) {
                $table->decimal('per_serving', 12, 4)->default(0)->after('unit');
            }
        });
    }

    public function down()
    {
        Schema::table('dish_ingredients', function (Blueprint $table) {
            if (Schema::hasColumn('dish_ingredients', 'per_serving')) {
                $table->dropColumn('per_serving');
            }
        });
    }
};
