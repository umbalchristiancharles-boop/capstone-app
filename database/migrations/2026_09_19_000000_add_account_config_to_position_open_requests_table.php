<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('position_open_requests', 'account_type')) {
            Schema::table('position_open_requests', function (Blueprint $table) {
                $table->string('account_type', 30)->default('standard')->after('notes');
                $table->json('account_config')->nullable()->after('account_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('position_open_requests', 'account_type')) {
            Schema::table('position_open_requests', function (Blueprint $table) {
                $table->dropColumn(['account_type', 'account_config']);
            });
        }
    }
};
