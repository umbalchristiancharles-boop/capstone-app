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
        Schema::create('price_markup_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('requested_by')->index(); // Finance manager who requested
            $table->decimal('current_percentage', 5, 2);
            $table->decimal('requested_percentage', 5, 2);
            $table->text('reason')->nullable();
            
            // Approval workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('main_finance_approval', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('main_finance_approved_by')->nullable();
            $table->timestamp('main_finance_approved_at')->nullable();
            $table->text('main_finance_notes')->nullable();
            
            $table->enum('owner_approval', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('owner_approved_by')->nullable();
            $table->timestamp('owner_approved_at')->nullable();
            $table->text('owner_notes')->nullable();
            
            $table->timestamp('activated_at')->nullable();
            
            $table->timestamps();
            
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('main_finance_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('owner_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_markup_requests');
    }
};
