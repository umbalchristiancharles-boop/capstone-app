<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('procurement_requests')) {
            Schema::table('procurement_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('procurement_requests', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->constrained('branches')->after('id');
                }
                if (!Schema::hasColumn('procurement_requests', 'price')) {
                    $table->decimal('price', 10, 2)->nullable()->after('quantity');
                }
                if (!Schema::hasColumn('procurement_requests', 'total_amount')) {
                    $table->decimal('total_amount', 12, 2)->nullable()->after('price');
                }
            });

            // Expand status enum to support budget_pending/completed
            try {
                DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending','budget_pending','approved','completed','cancelled') DEFAULT 'pending'");
            } catch (\Throwable $e) {
                // Fallback: ignore if database driver doesn't support ALTER ENUM
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('procurement_requests')) {
            Schema::table('procurement_requests', function (Blueprint $table) {
                if (Schema::hasColumn('procurement_requests', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
                if (Schema::hasColumn('procurement_requests', 'price')) {
                    $table->dropColumn('price');
                }
                if (Schema::hasColumn('procurement_requests', 'total_amount')) {
                    $table->dropColumn('total_amount');
                }
            });

            try {
                DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending','approved','cancelled') DEFAULT 'pending'");
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }
};
