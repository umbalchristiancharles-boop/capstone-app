<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = strtoupper($user->role ?? '');
        if (!$user || !in_array($userRole, ['HR', 'BRANCH_MANAGER', 'OWNER', 'ADMIN', 'SUPER_ADMIN', 'MANAGER_HR', 'MANAGER'])) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $branchId = $user->branch_id;
        $status = $request->query('status', 'all');
        $payrollType = $request->query('payroll_type', 'all');
        $period = $request->query('period', 'current_month');

        $this->ensureAutomaticPayroll($branchId);

        $query = Payroll::with(['user:id,full_name,username', 'confirmedBy:id,full_name']);

        if (in_array($userRole, ['HR', 'BRANCH_MANAGER', 'MANAGER_HR', 'MANAGER'])) {
            $query->where('branch_id', $branchId);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($payrollType !== 'all') {
            $query->where('payroll_type', $payrollType);
        }

        if ($period === 'current_month') {
            $query->whereBetween('pay_period_start', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]);
        } elseif ($period === 'current_cycle') {
            [$cycleStart, $cycleEnd, $cycleType] = $this->payrollCycleForDate(Carbon::now());
            $query->whereDate('pay_period_start', $cycleStart)
                ->whereDate('pay_period_end', $cycleEnd)
                ->where('payroll_type', $cycleType);
        } elseif ($period === 'active_cycles') {
            [$cycleStart, $cycleEnd, $cycleType] = $this->payrollCycleForDate(Carbon::now());
            $previousCycleDate = $cycleStart->copy()->subDay();
            [$previousStart, $previousEnd, $previousType] = $this->payrollCycleForDate($previousCycleDate);

            $query->where(function ($cycleQuery) use (
                $cycleStart,
                $cycleEnd,
                $cycleType,
                $previousStart,
                $previousEnd,
                $previousType
            ) {
                $cycleQuery->where(function ($currentQuery) use ($cycleStart, $cycleEnd, $cycleType) {
                    $currentQuery->whereDate('pay_period_start', $cycleStart)
                        ->whereDate('pay_period_end', $cycleEnd)
                        ->where('payroll_type', $cycleType);
                })->orWhere(function ($previousQuery) use ($previousStart, $previousEnd, $previousType) {
                    $previousQuery->whereDate('pay_period_start', $previousStart)
                        ->whereDate('pay_period_end', $previousEnd)
                        ->where('payroll_type', $previousType);
                });
            });
        } elseif ($period === 'last_month') {
            $query->whereBetween('pay_period_start', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth()
            ]);
        }

        $payrolls = $query->orderBy('pay_date', 'desc')->get();

        return response()->json([
            'ok' => true,
            'data' => $payrolls
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        $userRole = strtoupper($user->role ?? '');
        if (!$user || !in_array($userRole, ['HR', 'BRANCH_MANAGER', 'OWNER', 'ADMIN', 'SUPER_ADMIN', 'MANAGER_HR', 'MANAGER'])) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $payroll = Payroll::with(['user:id,full_name,username', 'confirmedBy:id,full_name'])->find($id);

        if (!$payroll) {
            return response()->json(['ok' => false, 'message' => 'Payroll not found'], 404);
        }

        if (in_array($userRole, ['HR', 'BRANCH_MANAGER', 'MANAGER_HR', 'MANAGER']) && $payroll->branch_id !== $user->branch_id) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        return response()->json([
            'ok' => true,
            'data' => $payroll
        ]);
    }

    public function generate(Request $request)
    {
        $user = Auth::user();
        $userRole = strtoupper($user->role ?? '');
        if (!$user || !in_array($userRole, ['HR', 'BRANCH_MANAGER', 'OWNER', 'ADMIN', 'SUPER_ADMIN', 'MANAGER_HR', 'MANAGER'])) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $request->validate([
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'payroll_type' => 'required|in:mid_month,end_month',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $branchId = $request->input('branch_id', $user->branch_id);
        $payPeriodStart = Carbon::parse($request->pay_period_start);
        $payPeriodEnd = Carbon::parse($request->pay_period_end);
        $payrollType = $request->payroll_type;

        $payDate = $payrollType === 'mid_month' 
            ? $payPeriodStart->copy()->day(15) 
            : $payPeriodEnd->copy()->endOfMonth();

        $staff = $this->payrollEligibleUsers($branchId)->get();

        if ($staff->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'No active staff found in this branch'], 404);
        }

        $generatedPayrolls = [];
        DB::beginTransaction();

        try {
            foreach ($staff as $staffMember) {
                $payroll = $this->recalculatePayroll($staffMember, $payPeriodStart, $payPeriodEnd, $payrollType, $payDate);

                $generatedPayrolls[] = $payroll;
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Payroll generated successfully for ' . count($generatedPayrolls) . ' staff members',
                'data' => $generatedPayrolls
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Failed to generate payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    public function syncAttendance(Attendance $attendance): void
    {
        $staffMember = $attendance->user;
        if (!$staffMember || !$staffMember->branch_id || !$this->payrollEligibleUsers($staffMember->branch_id)->whereKey($staffMember->id)->exists()) {
            return;
        }

        [$periodStart, $periodEnd, $payrollType, $payDate] = $this->payrollCycleForDate(
            Carbon::parse($attendance->getRawOriginal('date') ?: $attendance->date)
        );
        $this->recalculatePayroll($staffMember, $periodStart, $periodEnd, $payrollType, $payDate);
    }

    private function recalculatePayroll(User $staffMember, Carbon $payPeriodStart, Carbon $payPeriodEnd, string $payrollType, Carbon $payDate): Payroll
    {
        $attendances = Attendance::where('user_id', $staffMember->id)
            ->whereBetween('date', [$payPeriodStart, $payPeriodEnd])
            ->get();
        $daysWorked = $attendances->whereNotNull('time_in')->count();
        $daysLate = $attendances->where('status', 'late')->count();
        $totalHoursWorked = $attendances->sum('hours_worked');
        $overtimeHours = $attendances->sum(function ($attendance) {
            return max(0, (((float) $attendance->hours_worked) - 480) / 60);
        });
        $dailyRate = $this->getDailyRate($staffMember->role, $staffMember->department);
        $hourlyRate = $dailyRate / 8;
        $workedHours = max(0, (float) $totalHoursWorked / 60);
        $regularHours = max(0, $workedHours - $overtimeHours);
        $baseSalary = $regularHours * $hourlyRate;
        $lateOccurrences = max(0, (int) $daysLate);
        $overtimePay = $overtimeHours * ($hourlyRate * 1.25);
        $grossSalary = $baseSalary + $overtimePay;
        $lateDeductions = min($lateOccurrences * ($hourlyRate * 0.10), $grossSalary);

        return Payroll::updateOrCreate(
            [
                'user_id' => $staffMember->id,
                'pay_period_start' => $payPeriodStart,
                'pay_period_end' => $payPeriodEnd,
            ],
            [
                'branch_id' => $staffMember->branch_id,
                'payroll_type' => $payrollType,
                'pay_date' => $payDate,
                'days_worked' => $daysWorked,
                'days_late' => $daysLate,
                'days_overtime' => $attendances->where('hours_worked', '>', 480)->count(),
                'total_hours_worked' => $workedHours,
                'total_overtime_hours' => $overtimeHours,
                'daily_rate' => $dailyRate,
                'hourly_rate' => $hourlyRate,
                'base_salary' => $baseSalary,
                'late_deductions' => $lateDeductions,
                'overtime_pay' => $overtimePay,
                'gross_salary' => $grossSalary,
                'net_salary' => max(0, $grossSalary - $lateDeductions),
            ]
        );
    }

    public function approve(Request $request, $id)
    {
        return response()->json([
            'ok' => false,
            'message' => 'Payroll approval is no longer used. Attach payment proof to mark it as paid.'
        ], 410);
    }

    public function markAsPaid(Request $request, $id)
    {
        $user = Auth::user();
        $userRole = strtoupper($user->role ?? '');
        if (!$user || !in_array($userRole, ['HR', 'BRANCH_MANAGER', 'OWNER', 'ADMIN', 'SUPER_ADMIN', 'FINANCE', 'MANAGER_HR', 'MANAGER'])) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $payroll = Payroll::find($id);
        if (!$payroll) {
            return response()->json(['ok' => false, 'message' => 'Payroll not found'], 404);
        }

        if ($payroll->status !== 'pending') {
            return response()->json(['ok' => false, 'message' => 'Payroll is not eligible to be marked as paid'], 400);
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'finance_notes' => 'nullable|string|max:2000',
        ]);

        $payroll->status = 'paid';
        $payroll->confirmed_by = $user->id;
        $payroll->confirmed_at = Carbon::now();
        $payroll->finance_notes = $request->input('finance_notes', $payroll->finance_notes);
        $payroll->payment_proof_path = $request->file('payment_proof')->store('payroll-proofs', 'public');
        $payroll->save();

        return response()->json([
            'ok' => true,
            'message' => 'Payroll marked as paid successfully',
            'data' => $payroll
        ]);
    }

    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $userRole = strtoupper($user->role ?? '');
        if (!$user || !in_array($userRole, ['HR', 'BRANCH_MANAGER', 'OWNER', 'ADMIN', 'SUPER_ADMIN', 'MANAGER_HR', 'MANAGER'])) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $payroll = Payroll::find($id);
        if (!$payroll) {
            return response()->json(['ok' => false, 'message' => 'Payroll not found'], 404);
        }

        if (in_array($userRole, ['HR', 'BRANCH_MANAGER', 'MANAGER_HR', 'MANAGER']) && $payroll->branch_id !== $user->branch_id) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $payroll->status = 'rejected';
        $payroll->notes = $request->input('notes', $payroll->notes);
        $payroll->save();

        return response()->json([
            'ok' => true,
            'message' => 'Payroll rejected',
            'data' => $payroll
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $userRole = strtoupper($user->role ?? '');
        if (!$user || !in_array($userRole, ['HR', 'BRANCH_MANAGER', 'OWNER', 'ADMIN', 'SUPER_ADMIN', 'MANAGER_HR', 'MANAGER'])) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $branchId = $user->branch_id;
        $currentMonth = Carbon::now()->startOfMonth();

        $query = Payroll::where('branch_id', $branchId)
            ->whereBetween('pay_period_start', [$currentMonth, $currentMonth->endOfMonth()]);

        $totalPayroll = $query->sum('net_salary');
        $pendingCount = $query->where('status', 'pending')->count();
        $paidCount = $query->where('status', 'paid')->count();
        $totalStaff = $query->distinct('user_id')->count();

        return response()->json([
            'ok' => true,
            'data' => [
                'total_payroll' => $totalPayroll,
                'pending_count' => $pendingCount,
                'paid_count' => $paidCount,
                'total_staff' => $totalStaff,
            ]
        ]);
    }

    private function getDailyRate(string $role, ?string $department = null): float
    {
        $rates = [
            'STAFF' => 600.00,
            'BRANCH_MANAGER' => 800.00,
            'HR' => 750.00,
            'MANAGER_FINANCE' => 750.00,
            'FINANCE' => 750.00,
            'LOGISTICS' => 700.00,
            'INVENTORY' => 650.00,
            'KITCHEN' => 650.00,
            'CASHIER' => 600.00,
        ];

        $role = strtoupper($role);
        $department = strtoupper((string) $department);

        return $rates[$role] ?? $rates[$department] ?? 600.00;
    }

    private function payrollEligibleUsers($branchId)
    {
        return User::where('branch_id', $branchId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereIn('role', [
                    'ADMIN', 'HR', 'MANAGER_HR', 'BRANCH_MANAGER',
                    'MANAGER_FINANCE', 'MANAGER_PROCUREMENT', 'MANAGER_LOGISTICS',
                    'MANAGER_INVENTORY', 'MANAGER_CASHIER', 'MANAGER_KITCHEN',
                    'STAFF_INVENTORY', 'STAFF_CASHIER', 'STAFF_KITCHEN', 'STAFF'
                ])->orWhere(function ($departmentQuery) {
                    $departmentQuery->whereIn('role', ['MANAGER', 'STAFF'])
                        ->whereIn('department', [
                            'HR', 'FINANCE', 'PROCUREMENT', 'LOGISTICS',
                            'INVENTORY', 'CASHIER', 'KITCHEN'
                        ]);
                });
            });
    }

    private function ensureAutomaticPayroll($branchId): void
    {
        if (!$branchId) {
            return;
        }

        [$periodStart, $periodEnd, $payrollType, $payDate] = $this->payrollCycleForDate(Carbon::now());
        $eligibleIds = $this->payrollEligibleUsers($branchId)->pluck('id');

        $existingIds = Payroll::where('branch_id', $branchId)
            ->whereDate('pay_period_start', $periodStart)
            ->whereDate('pay_period_end', $periodEnd)
            ->where('payroll_type', $payrollType)
            ->whereIn('user_id', $eligibleIds)
            ->pluck('user_id');

        if ($eligibleIds->diff($existingIds)->isEmpty()) {
            $eligibleUsers = $this->payrollEligibleUsers($branchId)->get();
            foreach ($eligibleUsers as $staffMember) {
                $this->recalculatePayroll($staffMember, $periodStart, $periodEnd, $payrollType, $payDate);
            }
            return;
        }

        $this->generate(new Request([
            'pay_period_start' => $periodStart->toDateString(),
            'pay_period_end' => $periodEnd->toDateString(),
            'payroll_type' => $payrollType,
            'branch_id' => $branchId,
        ]));
    }

    private function payrollCycleForDate(Carbon $date): array
    {
        $date = $date->copy();

        if ($date->day <= 15) {
            $periodStart = $date->copy()->startOfMonth();
            $periodEnd = $date->copy()->day(15)->startOfDay();
            $payrollType = 'mid_month';
            $payDate = $date->copy()->day(15)->startOfDay();
        } else {
            $periodStart = $date->copy()->day(16)->startOfDay();
            $periodEnd = $date->copy()->endOfMonth()->startOfDay();
            $payrollType = 'end_month';
            $payDate = $periodEnd->copy();
        }

        return [$periodStart, $periodEnd, $payrollType, $payDate];
    }
}