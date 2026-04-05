<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'approval_status')) {
                $table->string('approval_status', 20)->default('approved')->after('is_main_branch');
            }
            if (!Schema::hasColumn('branches', 'requested_by')) {
                $table->unsignedBigInteger('requested_by')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('branches', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('requested_by');
            }
            if (!Schema::hasColumn('branches', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('branches', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
        });

        if (Schema::hasColumn('branches', 'approval_status')) {
            DB::table('branches')
                ->whereNull('approval_status')
                ->update(['approval_status' => 'approved']);
        }
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('branches', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('branches', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('branches', 'requested_by')) {
                $table->dropColumn('requested_by');
            }
            if (Schema::hasColumn('branches', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
        });
    }
};
