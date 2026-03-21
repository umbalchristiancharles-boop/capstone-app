<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'Budget Given' to the status enum so controllers can persist it.
        // Using raw statement because altering ENUM with Blueprint is DB-specific.
        DB::statement("ALTER TABLE `budget_requests` MODIFY `status` ENUM('Pending','Approved','Rejected','Budget Given') NOT NULL DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum to original set (this will fail if any rows use 'Budget Given').
        DB::statement("ALTER TABLE `budget_requests` MODIFY `status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending'");
    }
};
