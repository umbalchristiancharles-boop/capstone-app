<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PayrollConfirmationWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('users');
        Schema::dropIfExists('branches');

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
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
            $table->softDeletes();
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
            $table->foreignId('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('hr_confirmed_by')->nullable();
            $table->timestamp('hr_confirmed_at')->nullable();
            $table->timestamp('employee_confirmed_at')->nullable();
            $table->text('finance_notes')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->timestamps();
        });
    }

    public function test_hr_finance_and_employee_confirm_payroll_in_sequence(): void
    {
        $branch = Branch::create(['name' => 'Main Branch']);
        $employee = $this->createUser('employee', 'STAFF', $branch->id);
        $hr = $this->createUser('hr', 'HR', $branch->id);
        $finance = $this->createUser('finance', 'MANAGER_FINANCE', $branch->id);
        $payroll = Payroll::create([
            'user_id' => $employee->id,
            'branch_id' => $branch->id,
            'pay_period_start' => '2026-10-01',
            'pay_period_end' => '2026-10-15',
            'payroll_type' => 'mid_month',
            'pay_date' => '2026-10-15',
            'daily_rate' => 600,
            'hourly_rate' => 75,
            'base_salary' => 1200,
            'late_deductions' => 0,
            'overtime_pay' => 0,
            'gross_salary' => 1200,
            'net_salary' => 1200,
            'status' => 'pending',
        ]);

        $this->actingAs($hr)->postJson("/api/payroll/{$payroll->id}/hr-confirm")
            ->assertOk()
            ->assertJsonPath('data.hr_confirmed_by', $hr->id)
            ->assertJsonPath('data.hr_confirmer.full_name', 'Hr');

        $this->actingAs($finance)->postJson("/api/payroll/{$payroll->id}/mark-paid")
            ->assertOk()
            ->assertJsonPath('data.status', 'paid');

        $this->actingAs($employee)->getJson('/api/payroll/my')
            ->assertOk()
            ->assertJsonPath('data.0.id', $payroll->id)
            ->assertJsonPath('data.0.status', 'paid');

        $response = $this->actingAs($employee)->postJson("/api/payroll/{$payroll->id}/employee-confirm")
            ->assertOk();
        $this->assertNotNull($response->json('data.employee_confirmed_at'));
    }

    public function test_finance_cannot_pay_before_hr_confirms_salary(): void
    {
        $branch = Branch::create(['name' => 'Main Branch']);
        $employee = $this->createUser('employee', 'STAFF', $branch->id);
        $finance = $this->createUser('finance', 'MANAGER_FINANCE', $branch->id);
        $payroll = Payroll::create([
            'user_id' => $employee->id,
            'branch_id' => $branch->id,
            'pay_period_start' => '2026-10-01',
            'pay_period_end' => '2026-10-15',
            'payroll_type' => 'mid_month',
            'pay_date' => '2026-10-15',
            'daily_rate' => 600,
            'hourly_rate' => 75,
            'base_salary' => 1200,
            'late_deductions' => 0,
            'overtime_pay' => 0,
            'gross_salary' => 1200,
            'net_salary' => 1200,
            'status' => 'pending',
        ]);

        $this->actingAs($finance)->postJson("/api/payroll/{$payroll->id}/mark-paid")
            ->assertBadRequest()
            ->assertJsonPath('ok', false);
    }

    private function createUser(string $username, string $role, int $branchId): User
    {
        return User::create([
            'username' => $username,
            'email' => "{$username}@example.com",
            'password' => 'Password123!',
            'full_name' => ucfirst($username),
            'role' => $role,
            'branch_id' => $branchId,
            'is_active' => true,
        ]);
    }
}
