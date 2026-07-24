<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $staff = User::where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereIn('role', ['STAFF', 'BRANCH_MANAGER', 'HR'])
            ->get();

        if ($staff->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'No active staff found in this branch'], 404);
        }

        $generatedPayrolls = [];
        DB::beginTransaction();

        try {
            foreach ($staff as $staffMember) {
                $attendances = Attendance::where('user_id', $staffMember->id)
                    ->whereBetween('date', [$payPeriodStart, $payPeriodEnd])
                    ->get();

                $daysWorked = $attendances->whereNotNull('time_in')->count();
                $daysLate = $attendances->where('status', 'late')->count();
                $totalHoursWorked = $attendances->sum('hours_worked');

                $overtimeHours = 0;
                foreach ($attendances as $attendance) {
                    if ($attendance->hours_worked > 8) {
                        $overtimeHours += ($attendance->hours_worked - 8);
                    }
                }

                $dailyRate = $this->getDailyRate($staffMember->role);
                $hourlyRate = $dailyRate / 8;

                $baseSalary = $daysWorked * $dailyRate;
                $lateDeductions = $daysLate * ($dailyRate * 0.1);
                $overtimePay = $overtimeHours * ($hourlyRate * 1.25);
                $grossSalary = $baseSalary + $overtimePay;
                $netSalary = $grossSalary - $lateDeductions;

                $payroll = Payroll::updateOrCreate(
                    [
                        'user_id' => $staffMember->id,
                        'pay_period_start' => $payPeriodStart,
                        'pay_period_end' => $payPeriodEnd,
                    ],
                    [
                        'branch_id' => $branchId,
                        'payroll_type' => $payrollType,
                        'pay_date' => $payDate,
                        'days_worked' => $daysWorked,
                        'days_late' => $daysLate,
                        'days_overtime' => $attendances->where('hours_worked', '>', 8)->count(),
                        'total_hours_worked' => $totalHoursWorked,
                        'total_overtime_hours' => $overtimeHours,
                        'daily_rate' => $dailyRate,
                        'hourly_rate' => $hourlyRate,
                        'base_salary' => $baseSalary,
                        'late_deductions' => $lateDeductions,
                        'overtime_pay' => $overtimePay,
                        'gross_salary' => $grossSalary,
                        'net_salary' => $netSalary,
                        'status' => 'pending',
                    ]
                );

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

    public function approve(Request $request, $id)
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

        if ($payroll->status !== 'pending') {
            return response()->json(['ok' => false, 'message' => 'Payroll is not in pending status'], 400);
        }

        $payroll->status = 'approved';
        $payroll->notes = $request->input('notes', $payroll->notes);
        $payroll->save();

        return response()->json([
            'ok' => true,
            'message' => 'Payroll approved successfully',
            'data' => $payroll
        ]);
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

        if ($payroll->status !== 'approved') {
            return response()->json(['ok' => false, 'message' => 'Payroll must be approved before marking as paid'], 400);
        }

        $payroll->status = 'paid';
        $payroll->confirmed_by = $user->id;
        $payroll->confirmed_at = Carbon::now();
        $payroll->finance_notes = $request->input('finance_notes', $payroll->finance_notes);
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
        $approvedCount = $query->where('status', 'approved')->count();
        $paidCount = $query->where('status', 'paid')->count();
        $totalStaff = $query->distinct('user_id')->count();

        return response()->json([
            'ok' => true,
            'data' => [
                'total_payroll' => $totalPayroll,
                'pending_count' => $pendingCount,
                'approved_count' => $approvedCount,
                'paid_count' => $paidCount,
                'total_staff' => $totalStaff,
            ]
        ]);
    }

    private function getDailyRate(string $role): float
    {
        $rates = [
            'STAFF' => 600.00,
            'BRANCH_MANAGER' => 800.00,
            'HR' => 750.00,
            'FINANCE' => 750.00,
            'LOGISTICS' => 700.00,
            'INVENTORY' => 650.00,
            'KITCHEN' => 650.00,
            'CASHIER' => 600.00,
        ];

        return $rates[strtoupper($role)] ?? 600.00;
    }
}