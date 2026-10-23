<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('branches', 'cost_attachments')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->json('cost_attachments')->nullable()->after('equipment_costs');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branches', 'cost_attachments')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('cost_attachments');
            });
        }
    }
};
