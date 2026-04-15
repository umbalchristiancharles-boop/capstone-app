<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add geolocation to users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'latitude')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('latitude', 10, 8)->nullable()->after('address');
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            });
        }

        // Add geolocation to branches table
        if (Schema::hasTable('branches') && !Schema::hasColumn('branches', 'latitude')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->decimal('latitude', 10, 8)->nullable()->after('address');
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            });
        }

        // Add geolocation to customer_accounts table
        if (Schema::hasTable('customer_accounts') && !Schema::hasColumn('customer_accounts', 'latitude')) {
            Schema::table('customer_accounts', function (Blueprint $table) {
                $table->decimal('latitude', 10, 8)->nullable()->after('postal_code');
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'latitude')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['latitude', 'longitude']);
            });
        }

        if (Schema::hasTable('branches') && Schema::hasColumn('branches', 'latitude')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn(['latitude', 'longitude']);
            });
        }

        if (Schema::hasTable('customer_accounts') && Schema::hasColumn('customer_accounts', 'latitude')) {
            Schema::table('customer_accounts', function (Blueprint $table) {
                $table->dropColumn(['latitude', 'longitude']);
            });
        }
    }
};
