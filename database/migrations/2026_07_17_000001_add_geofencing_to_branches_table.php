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
        Schema::table('branches', function (Blueprint $table) {
            $table->decimal('square_meters', 10, 2)->nullable()->after('budget')->comment('Store area in square meters');
            $table->decimal('geofencing_radius', 10, 2)->nullable()->after('square_meters')->comment('Geofencing radius in meters (auto-calculated from square_meters)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['geofencing_radius', 'square_meters']);
        });
    }
};