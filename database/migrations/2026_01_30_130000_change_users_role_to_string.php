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
        // Convert `role` column to VARCHAR so new roles (e.g. HR) can be stored.
        // Using raw statement because changing from ENUM may require doctrine/dbal.
        DB::statement("ALTER TABLE `users` MODIFY `role` VARCHAR(50) NOT NULL DEFAULT 'STAFF'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Attempt to revert to enum; adjust list if your schema differs.
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('OWNER','BRANCH_MANAGER','STAFF') NOT NULL DEFAULT 'STAFF'");
    }
};
