<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->text('storage_requirements')->nullable()->after('target_audience');
            $table->boolean('is_perishable')->nullable()->after('storage_requirements');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->text('storage_requirements')->nullable()->after('description');
            $table->boolean('is_perishable')->nullable()->after('storage_requirements');
        });
    }

    public function down(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropColumn(['storage_requirements', 'is_perishable']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['storage_requirements', 'is_perishable']);
        });
    }
};
