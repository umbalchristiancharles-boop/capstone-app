<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('status');
            $table->unsignedBigInteger('receipt_uploaded_by')->nullable()->after('receipt_path');
            $table->timestamp('receipt_uploaded_at')->nullable()->after('receipt_uploaded_by');
            $table->boolean('receipt_confirmed')->default(false)->after('receipt_uploaded_at');
            $table->unsignedBigInteger('receipt_confirmed_by')->nullable()->after('receipt_confirmed');
            $table->timestamp('receipt_confirmed_at')->nullable()->after('receipt_confirmed_by');
        });
    }

    public function down()
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropColumn(['receipt_path','receipt_uploaded_by','receipt_uploaded_at','receipt_confirmed','receipt_confirmed_by','receipt_confirmed_at']);
        });
    }
};
