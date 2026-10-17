<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('branches')
            || !Schema::hasColumn('branches', 'is_main_branch')
            || !Schema::hasColumn('branches', 'budget')
            || !Schema::hasColumn('branches', 'initial_budget')
        ) {
            return;
        }

        DB::table('branches')
            ->where('is_main_branch', false)
            ->where('budget', '>', 0)
            ->where(function ($query) {
                $query->whereNull('initial_budget')
                    ->orWhere('initial_budget', 0);
            })
            ->update(['initial_budget' => DB::raw('budget')]);
    }

    public function down(): void
    {
        // Previous initial_budget values cannot be reconstructed safely.
    }
};