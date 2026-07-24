<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls';

    protected $fillable = [
        'user_id',
        'branch_id',
        'pay_period_start',
        'pay_period_end',
        'payroll_type',
        'pay_date',
        'days_worked',
        'days_late',
        'days_overtime',
        'total_hours_worked',
        'total_overtime_hours',
        'daily_rate',
        'hourly_rate',
        'base_salary',
        'late_deductions',
        'overtime_pay',
        'gross_salary',
        'net_salary',
        'status',
        'notes',
        'confirmed_by',
        'confirmed_at',
        'finance_notes',
    ];

    protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'pay_date' => 'date',
        'confirmed_at' => 'datetime',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'base_salary' => 'decimal:2',
        'late_deductions' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'total_hours_worked' => 'decimal:2',
        'total_overtime_hours' => 'decimal:2',
    ];

    /**
     * Relationship: Payroll belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Payroll belongs to a Branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Scope for pending payrolls
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved payrolls
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for paid payrolls
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for branch payrolls
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope for user payrolls
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge--warning',
            'approved' => 'badge--info',
            'paid' => 'badge--success',
            'rejected' => 'badge--danger',
            default => 'badge--info',
        };
    }

    /**
     * Get formatted pay period
     */
    public function getFormattedPayPeriodAttribute(): string
    {
        return $this->pay_period_start->format('M d, Y') . ' - ' . $this->pay_period_end->format('M d, Y');
    }

    /**
     * Get payroll type label
     */
    public function getPayrollTypeLabelAttribute(): string
    {
        return match($this->payroll_type) {
            'mid_month' => 'Mid-Month (15th)',
            'end_month' => 'End of Month',
            default => ucfirst(str_replace('_', ' ', $this->payroll_type)),
        };
    }
}