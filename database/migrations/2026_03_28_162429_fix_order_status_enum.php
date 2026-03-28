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
            // Add 'in_kitchen' back to the status enum to support kitchen orders
            $table->enum('status', ['pending', 'in_kitchen', 'approved', 'cancelled', 'completed'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert to previous enum without 'in_kitchen'
            $table->enum('status', ['pending', 'approved', 'cancelled', 'completed'])->change();
        });
    }
};
