<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->unique();
            $table->string('username', 50)->unique()->nullable();
            $table->string('name', 100)->nullable();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('role', ['OWNER', 'BRANCH_MANAGER', 'STAFF', 'HR', 'customer'])->default('customer');
            $table->rememberToken();
            $table->timestamps();

            $table->index('email');
            $table->index('username');
            $table->index('role');
        });
    }

    public function down(): void
    {
        // Schema::dropIfExists('users');
    }
};
