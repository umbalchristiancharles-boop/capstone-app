<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('branches', 'budget')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->bigInteger('budget')->nullable()->default(null)->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('branches', 'budget')) {
            return;
        }

        DB::table('branches')->whereNull('budget')->update(['budget' => 100000]);

        Schema::table('branches', function (Blueprint $table) {
            $table->bigInteger('budget')->default(100000)->change();
        });
    }
};
