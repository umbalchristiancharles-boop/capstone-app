<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('email', 100);
            $table->string('full_name', 100)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 50)->nullable();
            $table->string('province', 50)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->integer('total_comments')->default(0);
            $table->integer('total_ratings')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            $table->timestamps();

            $table->index(['email']);
            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_accounts');
    }
};
