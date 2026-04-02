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
        if (Schema::hasColumn('products', 'is_published')) {
            // Change column default to false (0) using raw statement to avoid requiring doctrine/dbal
            DB::statement("ALTER TABLE `products` MODIFY `is_published` TINYINT(1) NOT NULL DEFAULT 0;");

            // For safety, mark supplier-submitted products as unpublished so they don't appear publicly
            DB::table('products')
                ->where(function ($q) {
                    $q->whereNotNull('supplier_id')
                      ->orWhereNotNull('supplier_name')
                      ->orWhere('supplier_name', '<>', '');
                })
                ->update(['is_published' => 0]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'is_published')) {
            // Revert default back to true (1)
            DB::statement("ALTER TABLE `products` MODIFY `is_published` TINYINT(1) NOT NULL DEFAULT 1;");
        }
    }
};
