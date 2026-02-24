<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add username column to users table for customer authentication
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 50)->unique()->after('email')->nullable();
            });
        }

        // Add email_verified_at if it doesn't exist
        if (!Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }

        // Ensure email is unique
        try {
            DB::statement('ALTER TABLE users ADD UNIQUE KEY unique_email (email)');
        } catch (\Exception $e) {
            // Index might already exist
        }

        // Ensure username is unique
        try {
            DB::statement('ALTER TABLE users ADD UNIQUE KEY unique_username (username)');
        } catch (\Exception $e) {
            // Index might already exist
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique('unique_username');
                $table->dropColumn('username');
            }
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }
};
