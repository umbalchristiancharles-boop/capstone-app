<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'logistics_request_available')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('logistics_request_available')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'logistics_request_available')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('logistics_request_available');
            });
        }
    }
};
