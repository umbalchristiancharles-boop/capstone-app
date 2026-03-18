<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add new statuses to the procurement_requests.status enum
        DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending','budget_pending','approved','completed','cancelled','cash_in_transit','delivery_pending') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to previous enum (drops records with new values if present)
        DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending','budget_pending','approved','completed','cancelled') DEFAULT 'pending'");
    }
};
