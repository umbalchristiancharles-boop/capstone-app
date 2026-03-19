<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddOngoingDeliveryToProcurementRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add 'ongoing_delivery' to the procurement_requests.status enum
        DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending','budget_pending','approved','completed','cancelled','cash_in_transit','delivery_pending','pending_order_to_supplier','ongoing_delivery') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to previous set (without ongoing_delivery)
        DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending','budget_pending','approved','completed','cancelled','cash_in_transit','delivery_pending','pending_order_to_supplier') DEFAULT 'pending'");
    }
}
