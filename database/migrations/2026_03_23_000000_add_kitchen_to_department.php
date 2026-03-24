<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw statement to safely alter the ENUM on MySQL
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `department` ENUM('HR','FINANCE','INVENTORY','LOGISTICS','CASHIER','KITCHEN','PROCUREMENT') NULL AFTER `role`");
    }

    public function down(): void
    {
        // Revert to previous enum (without KITCHEN and PROCUREMENT if needed)
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `department` ENUM('HR','FINANCE','INVENTORY','LOGISTICS','CASHIER') NULL AFTER `role`");
    }
};
