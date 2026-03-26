<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds the awaiting_inventory_confirmation enum value to procurement_requests.status
     *
     * Note: altering ENUM types via migration is environment-specific; this uses
     * a raw ALTER TABLE statement which works for MySQL.
     */
    public function up()
    {
        // New enum list including awaiting_inventory_confirmation
        $enum = "ENUM('pending','budget_pending','approved','completed','cancelled','cash_in_transit','delivery_pending','pending_order_to_supplier','ongoing_delivery','awaiting_inventory_confirmation')";
        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN `status` {$enum} DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     * Removes the awaiting_inventory_confirmation value.
     */
    public function down()
    {
        $enum = "ENUM('pending','budget_pending','approved','completed','cancelled','cash_in_transit','delivery_pending','pending_order_to_supplier','ongoing_delivery')";
        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN `status` {$enum} DEFAULT 'pending'");
    }
};
