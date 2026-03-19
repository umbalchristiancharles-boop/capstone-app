<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPendingOrderStatusProcurementRequests extends Migration
{
    /**
     * Run the migrations.
     * Adds the `pending_order_to_supplier` enum value to procurement_requests.status
     */
    public function up()
    {
        // Step 1: expand enum to include the new value while keeping the old
        DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending','budget_pending','approved','completed','cancelled','cash_in_transit','delivery_pending','pending_order_to_supplier') DEFAULT 'pending'");

        // Step 2: migrate existing rows that currently use 'delivery_pending'
        DB::table('procurement_requests')->where('status', 'delivery_pending')->update(['status' => 'pending_order_to_supplier']);

        // Step 3: remove the old enum value now that rows have been migrated
        DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending','budget_pending','approved','completed','cancelled','cash_in_transit','pending_order_to_supplier') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     * Restores the previous enum including `delivery_pending`.
     */
    public function down()
    {
        // Re-add both enum values to safely revert
        DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending','budget_pending','approved','completed','cancelled','cash_in_transit','delivery_pending','pending_order_to_supplier') DEFAULT 'pending'");

        // Revert any migrated rows back to the old value
        DB::table('procurement_requests')->where('status', 'pending_order_to_supplier')->update(['status' => 'delivery_pending']);

        // Remove the new enum value
        DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending','budget_pending','approved','completed','cancelled','cash_in_transit','delivery_pending') DEFAULT 'pending'");
    }
}
