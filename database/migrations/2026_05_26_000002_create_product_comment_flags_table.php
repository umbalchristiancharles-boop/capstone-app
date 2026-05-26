<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_comment_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_comment_id')->constrained('product_comments')->cascadeOnDelete();
            $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['product_comment_id']);
            $table->index(['admin_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_comment_flags');
    }
};
