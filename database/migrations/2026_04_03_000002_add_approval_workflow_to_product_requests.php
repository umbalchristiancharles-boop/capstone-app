<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            // Multi-level workflow status
            if (!Schema::hasColumn('product_requests', 'status')) {
                $table->string('status')->default('pending_logistics')
                    ->comment('pending_logistics, pending_owner, approved, rejected')->after('approval_status');
            }

            // Logistics approval tracking
            if (!Schema::hasColumn('product_requests', 'approved_by_logistics')) {
                $table->unsignedBigInteger('approved_by_logistics')->nullable()->after('status');
            }
            if (!Schema::hasColumn('product_requests', 'logistics_approval_notes')) {
                $table->text('logistics_approval_notes')->nullable()->after('approved_by_logistics');
            }

            // Owner approval tracking
            if (!Schema::hasColumn('product_requests', 'approved_by_owner')) {
                $table->unsignedBigInteger('approved_by_owner')->nullable()->after('logistics_approval_notes');
            }
            if (!Schema::hasColumn('product_requests', 'owner_approval_notes')) {
                $table->text('owner_approval_notes')->nullable()->after('approved_by_owner');
            }

            // Rejection tracking
            if (!Schema::hasColumn('product_requests', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('owner_approval_notes');
            }

            // Add foreign keys for approvers
            if (Schema::hasColumn('product_requests', 'approved_by_logistics')) {
                try {
                    $table->foreign('approved_by_logistics')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist
                }
            }

            if (Schema::hasColumn('product_requests', 'approved_by_owner')) {
                try {
                    $table->foreign('approved_by_owner')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            // Drop foreign keys if they exist
            try {
                $table->dropForeign(['approved_by_logistics']);
            } catch (\Exception $e) {}
            try {
                $table->dropForeign(['approved_by_owner']);
            } catch (\Exception $e) {}

            // Drop columns if they exist
            if (Schema::hasColumn('product_requests', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('product_requests', 'approved_by_logistics')) {
                $table->dropColumn('approved_by_logistics');
            }
            if (Schema::hasColumn('product_requests', 'logistics_approval_notes')) {
                $table->dropColumn('logistics_approval_notes');
            }
            if (Schema::hasColumn('product_requests', 'approved_by_owner')) {
                $table->dropColumn('approved_by_owner');
            }
            if (Schema::hasColumn('product_requests', 'owner_approval_notes')) {
                $table->dropColumn('owner_approval_notes');
            }
            if (Schema::hasColumn('product_requests', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
        });
    }
};
