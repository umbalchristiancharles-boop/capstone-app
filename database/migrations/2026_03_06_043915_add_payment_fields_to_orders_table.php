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
            $table->decimal('amount_paid', 10, 2)->default(0)->after('grand_total');
            $table->decimal('change_amount', 10, 2)->default(0)->after('amount_paid');
            $table->unsignedBigInteger('cashier_id')->nullable()->after('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'change_amount', 'cashier_id']);
        });
    }
};
