<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->string('payroll_type'); // 'mid_month' or 'end_month'
            $table->date('pay_date');
            
            // Attendance-based calculations
            $table->integer('days_worked')->default(0);
            $table->integer('days_late')->default(0);
            $table->integer('days_overtime')->default(0);
            $table->decimal('total_hours_worked', 8, 2)->default(0);
            $table->decimal('total_overtime_hours', 8, 2)->default(0);
            
            // Salary calculations
            $table->decimal('daily_rate', 10, 2);
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('base_salary', 10, 2);
            $table->decimal('late_deductions', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('net_salary', 10, 2);
            
            // Status tracking
            $table->string('status')->default('pending'); // pending, approved, paid, rejected
            $table->text('notes')->nullable();
            
            // Finance confirmation
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('finance_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->index(['user_id', 'pay_period_start', 'pay_period_end']);
            $table->index(['branch_id', 'status']);
            $table->index(['pay_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};