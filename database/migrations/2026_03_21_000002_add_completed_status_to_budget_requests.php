<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'Completed' to the status enum for budget_requests
        DB::statement("ALTER TABLE `budget_requests` MODIFY `status` ENUM('Pending','Approved','Rejected','Budget Given','Completed') NOT NULL DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: remove 'Completed' (will fail if rows use it)
        DB::statement("ALTER TABLE `budget_requests` MODIFY `status` ENUM('Pending','Approved','Rejected','Budget Given') NOT NULL DEFAULT 'Pending'");
    }
};
