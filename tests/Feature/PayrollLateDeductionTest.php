<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\PayrollController;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PayrollLateDeductionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('full_name')->nullable();
            $table->string('role')->default('STAFF');
            $table->string('department')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();
            $table->integer('hours_worked')->default(0);
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->string('payroll_type');
            $table->date('pay_date');
            $table->integer('days_worked')->default(0);
            $table->integer('days_late')->default(0);
            $table->integer('days_overtime')->default(0);
            $table->decimal('total_hours_worked', 8, 2)->default(0);
            $table->decimal('total_overtime_hours', 8, 2)->default(0);
            $table->decimal('daily_rate', 10, 2);
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('base_salary', 10, 2);
            $table->decimal('late_deductions', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function test_late_occurrences_deduct_ten_percent_of_hourly_rate_each(): void
    {
        $branch = Branch::create([
            'name' => 'Main Branch',
            'is_active' => true,
        ]);

        $staff = User::create([
            'username' => 'stafflate01',
            'email' => 'stafflate01@example.com',
            'password' => 'Password123!',
            'full_name' => 'Late Staff',
            'role' => 'STAFF',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        Attendance::create([
            'user_id' => $staff->id,
            'date' => '2026-09-01',
            'time_in' => '2026-09-01 08:30:00',
            'time_out' => '2026-09-01 17:30:00',
            'hours_worked' => 540,
            'status' => 'late',
        ]);

        Attendance::create([
            'user_id' => $staff->id,
            'date' => '2026-09-02',
            'time_in' => '2026-09-02 08:50:00',
            'time_out' => '2026-09-02 17:30:00',
            'hours_worked' => 540,
            'status' => 'late',
        ]);

        $controller = new PayrollController();
        $method = new \ReflectionMethod($controller, 'recalculatePayroll');
        $method->setAccessible(true);

        $payroll = $method->invoke(
            $controller,
            $staff,
            Carbon::parse('2026-09-01'),
            Carbon::parse('2026-09-15'),
            'mid_month',
            Carbon::parse('2026-09-15')
        );

        $this->assertSame(15.00, (float) $payroll->late_deductions);
        $this->assertSame(1185.00, (float) $payroll->net_salary);
    }
}
