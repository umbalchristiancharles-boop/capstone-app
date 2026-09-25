<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payrolls')
            ->where('status', 'approved')
            ->update(['status' => 'pending']);
    }

    public function down(): void
    {
        // Payrolls that were converted to pending cannot be safely distinguished
        // from payrolls that were originally pending.
    }
};