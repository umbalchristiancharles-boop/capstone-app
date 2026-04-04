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
        Schema::create('supplier_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('action'); // create, update, deactivate, order, delivery, merge_duplicates, etc
            $table->text('description')->nullable();
            $table->unsignedBigInteger('triggered_by_user_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('affected_records')->nullable(); // e.g., order IDs, product IDs that were affected
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Additional context
            $table->timestamps();

            // Indexes for faster querying
            $table->index('supplier_id');
            $table->index('triggered_by_user_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['supplier_id', 'created_at']);

            // Foreign keys
            $table->foreign('supplier_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('triggered_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_audit_logs');
    }
};
