<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('branches', 'deactivation_reason')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->text('deactivation_reason')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branches', 'deactivation_reason')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('deactivation_reason');
            });
        }
    }
};
