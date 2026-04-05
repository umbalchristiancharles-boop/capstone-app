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
        if (Schema::hasTable('logistics_transactions')) {
            return;
        }

        Schema::create('logistics_transactions', function (Blueprint $table) {
            $table->id();

            // Reference to source transaction
            $table->foreignId('procurement_request_id')->nullable()->constrained('procurement_requests')->onDelete('cascade');
            $table->foreignId('supplier_order_id')->nullable()->constrained('supplier_orders')->onDelete('cascade');

            // Reference to inventory/product
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');

            // Branch information
            $table->foreignId('source_branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('destination_branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');

            // Transaction details
            $table->string('type'); // 'procurement', 'transfer', 'delivery', 'incoming', 'outgoing', 'adjustment'
            $table->string('status'); // pending, in_transit, at_destination, verified, confirmed, completed, cancelled
            $table->integer('quantity')->default(0);
            $table->decimal('quantity_verified', 10, 2)->nullable();
            $table->string('unit')->default('unit');

            // Tracking details
            $table->text('reference_number')->nullable(); // e.g. "PR-123", "DO-456"
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            // User tracking
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('setNull');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->onDelete('setNull');
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->onDelete('setNull');

            // Timestamps for each status change
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('at_destination_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Expected vs Actual
            $table->integer('expected_quantity')->default(0);
            $table->integer('actual_quantity')->default(0);
            $table->text('variance_reason')->nullable();

            // Location tracking
            $table->string('source_location')->nullable(); // e.g., warehouse, kitchen
            $table->string('destination_location')->nullable();
            $table->text('delivery_address')->nullable();

            // Documents/Proof
            $table->text('receipt_path')->nullable();
            $table->text('proof_of_delivery_path')->nullable();
            $table->text('documentation_files')->nullable(); // JSON array of file paths

            // Financial reconciliation
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->string('cost_reference')->nullable(); // e.g., "BudgetRequest#123"

            // Audit trail
            $table->boolean('is_duplicate')->default(false);
            $table->unsignedBigInteger('duplicate_of_transaction_id')->nullable();
            $table->text('audit_notes')->nullable();

            // Indexes for quick lookups
            $table->index('procurement_request_id');
            $table->index('supplier_order_id');
            $table->index('product_id');
            $table->index('branch_id');
            $table->index('source_branch_id');
            $table->index('destination_branch_id');
            $table->index('type');
            $table->index('status');
            $table->index('created_by_user_id');
            $table->index('created_at');
            $table->unique(['procurement_request_id', 'type', 'status'], 'unique_proc_transaction');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistics_transactions');
    }
};
