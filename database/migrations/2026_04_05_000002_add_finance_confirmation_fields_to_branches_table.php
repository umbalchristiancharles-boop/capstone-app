<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'finance_confirmed_by')) {
                $table->unsignedBigInteger('finance_confirmed_by')->nullable()->after('requested_by');
            }
            if (!Schema::hasColumn('branches', 'finance_confirmed_at')) {
                $table->timestamp('finance_confirmed_at')->nullable()->after('finance_confirmed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'finance_confirmed_at')) {
                $table->dropColumn('finance_confirmed_at');
            }
            if (Schema::hasColumn('branches', 'finance_confirmed_by')) {
                $table->dropColumn('finance_confirmed_by');
            }
        });
    }
};
