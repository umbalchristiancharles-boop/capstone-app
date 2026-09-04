<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_orders', function (Blueprint $table) {
            $table->boolean('admin_confirmed')->default(false)->after('estimated_delivery_datetime');
            $table->unsignedBigInteger('admin_confirmed_by')->nullable()->after('admin_confirmed');
            $table->timestamp('admin_confirmed_at')->nullable()->after('admin_confirmed_by');
            $table->foreign('admin_confirmed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_orders', function (Blueprint $table) {
            $table->dropForeign(['admin_confirmed_by']);
            $table->dropColumn(['admin_confirmed', 'admin_confirmed_by', 'admin_confirmed_at']);
        });
    }
};