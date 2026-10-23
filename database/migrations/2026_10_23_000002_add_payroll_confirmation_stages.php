<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('hr_confirmed_by')->nullable()->after('confirmed_by')->constrained('users')->nullOnDelete();
            $table->timestamp('hr_confirmed_at')->nullable()->after('hr_confirmed_by');
            $table->timestamp('employee_confirmed_at')->nullable()->after('hr_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['hr_confirmed_by']);
            $table->dropColumn(['hr_confirmed_by', 'hr_confirmed_at', 'employee_confirmed_at']);
        });
    }
};
