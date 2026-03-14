<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'discount_type')) {
                $table->string('discount_type', 20)->default('none')->after('change_amount');
            }
            if (!Schema::hasColumn('orders', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_percent');
            }
            if (!Schema::hasColumn('orders', 'vat_percent')) {
                $table->decimal('vat_percent', 5, 2)->default(12.00)->after('discount_amount');
            }
            if (!Schema::hasColumn('orders', 'vat_amount')) {
                $table->decimal('vat_amount', 10, 2)->default(0)->after('vat_percent');
            }
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('vat_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = ['discount_type','discount_percent','discount_amount','vat_percent','vat_amount','subtotal'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('orders', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
