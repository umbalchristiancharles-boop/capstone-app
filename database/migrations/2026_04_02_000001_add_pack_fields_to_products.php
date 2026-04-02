<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('pack_quantity', 10, 2)->nullable()->after('per_pack_or_individual');
            $table->string('pack_unit', 50)->nullable()->after('pack_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'pack_unit')) {
                $table->dropColumn('pack_unit');
            }
            if (Schema::hasColumn('products', 'pack_quantity')) {
                $table->dropColumn('pack_quantity');
            }
        });
    }
};
