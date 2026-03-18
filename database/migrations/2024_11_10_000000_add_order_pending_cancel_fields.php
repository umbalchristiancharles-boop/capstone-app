<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderPendingCancelFields extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'cancelled', 'completed'])->default('pending')->change();
            $table->boolean('is_cancelled')->default(false)->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('is_cancelled');
            $table->timestamp('approved_at')->nullable()->after('cancelled_at');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['is_cancelled', 'cancelled_at', 'approved_at', 'approved_by']);
            $table->enum('status', ['completed'])->default('completed')->change();
        });
    }
};

