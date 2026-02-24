<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_comments', 'parent_comment_id')) {
            Schema::table('product_comments', function (Blueprint $table) {
                $table->foreignId('parent_comment_id')->nullable()->after('user_id')->constrained('product_comments')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_comments', 'parent_comment_id')) {
            Schema::table('product_comments', function (Blueprint $table) {
                $table->dropForeign(['parent_comment_id']);
                $table->dropColumn('parent_comment_id');
            });
        }
    }
};
