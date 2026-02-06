<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('product_comments') && Schema::hasColumn('product_comments', 'rating')) {
            Schema::table('product_comments', function (Blueprint $table) {
                $table->unsignedTinyInteger('rating')->nullable()->default(5)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_comments', function (Blueprint $table) {
            $table->tinyInteger('rating')->nullable(false)->change();
        });
    }
};
