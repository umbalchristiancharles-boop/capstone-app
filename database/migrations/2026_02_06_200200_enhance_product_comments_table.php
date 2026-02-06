<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_comments', function (Blueprint $table) {
            // Add rating column if doesn't exist
            if (!Schema::hasColumn('product_comments', 'rating')) {
                $table->integer('rating')->nullable()->after('text')->comment('Rating from 1 to 5');
            }

            // Add user_id column to link to authenticated user
            if (!Schema::hasColumn('product_comments', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('product_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_comments', function (Blueprint $table) {
            if (Schema::hasColumn('product_comments', 'rating')) {
                $table->dropColumn('rating');
            }

            if (Schema::hasColumn('product_comments', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
