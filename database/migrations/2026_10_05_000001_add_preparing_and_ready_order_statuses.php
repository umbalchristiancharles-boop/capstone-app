<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_kitchen', 'preparing', 'ready', 'approved', 'cancelled', 'completed'])->change();
        });
    }

    public function down(): void
    {
        DB::table('orders')
            ->whereIn('status', ['preparing', 'ready'])
            ->update(['status' => 'in_kitchen']);

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_kitchen', 'approved', 'cancelled', 'completed'])->change();
        });
    }
};
