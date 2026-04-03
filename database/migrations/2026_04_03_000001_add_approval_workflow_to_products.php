<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add approval workflow fields
            if (!Schema::hasColumn('products', 'status')) {
                $table->string('status')->default('pending_owner')->comment('pending_logistics_main, pending_owner, approved, rejected');
            }

            if (!Schema::hasColumn('products', 'requires_logistics')) {
                $table->boolean('requires_logistics')->default(false)->comment('Whether product requires logistics approval');
            }

            if (!Schema::hasColumn('products', 'approved_by_logistics_main')) {
                $table->unsignedBigInteger('approved_by_logistics_main')->nullable()->comment('User ID of logistics main branch approver');
            }

            if (!Schema::hasColumn('products', 'approved_by_owner')) {
                $table->unsignedBigInteger('approved_by_owner')->nullable()->comment('User ID of owner approver');
            }

            if (!Schema::hasColumn('products', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->comment('Reason for product rejection');
            }

            if (!Schema::hasColumn('products', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->comment('Final approval timestamp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'requires_logistics',
                'approved_by_logistics_main',
                'approved_by_owner',
                'rejection_reason',
                'approved_at',
            ]);
        });
    }
};
