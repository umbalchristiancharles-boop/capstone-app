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
            // Store permit bills as JSON array
            $table->json('permit_bills')->nullable()->after('geofencing_radius')->comment('Array of permit types and their costs');
            
            // Store construction costs as JSON
            $table->json('construction_costs')->nullable()->after('permit_bills')->comment('Detailed construction cost breakdown');
            
            // Store equipment costs as JSON array
            $table->json('equipment_costs')->nullable()->after('construction_costs')->comment('Array of equipment with types, quantities, and costs');
            
            // Total initial investment (sum of all costs)
            $table->decimal('total_investment', 15, 2)->nullable()->after('equipment_costs')->comment('Total initial investment including permits, construction, and equipment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['permit_bills', 'construction_costs', 'equipment_costs', 'total_investment']);
        });
    }
};