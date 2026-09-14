<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('payrolls')
            ->select('user_id', 'pay_period_start', 'pay_period_end', 'payroll_type')
            ->groupBy('user_id', 'pay_period_start', 'pay_period_end', 'payroll_type')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $keepId = DB::table('payrolls')
                ->where('user_id', $duplicate->user_id)
                ->where('pay_period_start', $duplicate->pay_period_start)
                ->where('pay_period_end', $duplicate->pay_period_end)
                ->where('payroll_type', $duplicate->payroll_type)
                ->min('id');

            DB::table('payrolls')
                ->where('user_id', $duplicate->user_id)
                ->where('pay_period_start', $duplicate->pay_period_start)
                ->where('pay_period_end', $duplicate->pay_period_end)
                ->where('payroll_type', $duplicate->payroll_type)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('payrolls', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'pay_period_start', 'pay_period_end', 'payroll_type'],
                'payrolls_user_period_type_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropUnique('payrolls_user_period_type_unique');
        });
    }
};