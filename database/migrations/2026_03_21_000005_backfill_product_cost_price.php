<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill cost_price for existing products where null: assume current price = selling price = cost * 1.10
        // so cost_price = ROUND(price / 1.10, 2)
        DB::statement("UPDATE products SET cost_price = ROUND(price / 1.10, 2) WHERE cost_price IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do not remove backfilled values on rollback to avoid data loss.
    }
};
