<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('branches', 'budget')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->decimal('budget', 15, 2)->nullable()->default(null)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branches', 'budget')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->bigInteger('budget')->nullable()->default(null)->change();
            });
        }
    }
};
