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
        Schema::create('procurement_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('logistics_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('procurement_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('finance_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('quantity');
            $table->enum('status', ['pending', 'approved', 'cancelled'])->default('pending');
            $table->boolean('budget_approved')->default(false);
            $table->decimal('budget_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_requests');
    }
};
