<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');

            // Delivery linkage (one lot per supplier-order delivery line)
            $table->unsignedBigInteger('supplier_order_id')->nullable();

            // Optional linkage (useful if you later want to trace back to procurement)
            $table->unsignedBigInteger('procurement_request_id')->nullable();

            $table->integer('quantity')->default(0);
            $table->dateTime('expires_at')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'product_id']);
            $table->index('supplier_order_id');
            $table->index('procurement_request_id');
            $table->index('expires_at');

            // Foreign keys intentionally omitted for now to avoid failures
            // if table/column names differ across environments.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_lots');
    }
};


