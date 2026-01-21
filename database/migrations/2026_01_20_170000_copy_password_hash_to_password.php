<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Copy password_hash to password for all users if password is null
        DB::statement('UPDATE users SET password = password_hash WHERE password IS NULL OR password = ""');
    }

    public function down(): void
    {
        // No rollback needed
    }
};
