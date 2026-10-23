<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const POSITION_NAMES = ['Branch Manager', 'Server/Staff'];

    public function up(): void
    {
        if (! Schema::hasTable('positions')) {
            return;
        }

        DB::table('positions')
            ->whereIn('name', self::POSITION_NAMES)
            ->whereNull('department')
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('positions')) {
            return;
        }

        DB::table('positions')
            ->whereIn('name', self::POSITION_NAMES)
            ->whereNull('department')
            ->update(['is_active' => true]);
    }
};
