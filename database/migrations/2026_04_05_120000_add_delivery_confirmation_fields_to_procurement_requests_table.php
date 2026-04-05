<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->unsignedInteger('confirmed_quantity')->nullable()->after('receipt_confirmed_at');
            $table->integer('variance_quantity')->nullable()->after('confirmed_quantity');
            $table->string('variance_reason')->nullable()->after('variance_quantity');
            $table->timestamp('variance_reported_at')->nullable()->after('variance_reason');
            $table->string('delivery_proof_path')->nullable()->after('variance_reported_at');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropColumn([
                'confirmed_quantity',
                'variance_quantity',
                'variance_reason',
                'variance_reported_at',
                'delivery_proof_path',
            ]);
        });
    }
};
