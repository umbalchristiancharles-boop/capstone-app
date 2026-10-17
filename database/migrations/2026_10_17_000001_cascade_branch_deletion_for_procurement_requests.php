<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('procurement_requests') || !Schema::hasColumn('procurement_requests', 'branch_id')) {
            return;
        }

        if ($this->hasBranchForeignKey()) {
            Schema::table('procurement_requests', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
        }

        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('procurement_requests') || !Schema::hasColumn('procurement_requests', 'branch_id')) {
            return;
        }

        if ($this->hasBranchForeignKey()) {
            Schema::table('procurement_requests', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
        }

        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches');
        });
    }

    private function hasBranchForeignKey(): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'procurement_requests')
            ->where('COLUMN_NAME', 'branch_id')
            ->where('REFERENCED_TABLE_NAME', 'branches')
            ->exists();
    }
};
