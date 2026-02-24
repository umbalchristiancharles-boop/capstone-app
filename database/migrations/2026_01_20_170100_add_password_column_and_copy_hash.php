<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'password')) {
                $table->string('password', 255)->nullable();
            }
        });
        // Copy password_hash to password for all users only if password_hash exists
        if (Schema::hasColumn('users', 'password_hash')) {
            DB::statement('UPDATE users SET password = password_hash WHERE password_hash IS NOT NULL AND password IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
