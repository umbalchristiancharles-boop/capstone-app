<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'amount',
        'description',
        'created_by',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============ FINANCIAL SCOPES ============

    /**
     * Scope: Get only approved expenses (count toward financial totals)
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Get expenses pending approval
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get rejected expenses
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: Sum total expenses by status
     */
    public function scopeTotalExpenses($query)
    {
        return $query->approved()->sum('amount');
    }

    /**
     * Scope: Get expenses by branch
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope: Get expenses within date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Check if this expense is a potential duplicate
     *
     * @param int $minutesWindow
     * @return bool
     */
    public function isPotentialDuplicate($minutesWindow = 5)
    {
        $count = static::where('branch_id', $this->branch_id)
            ->where('amount', $this->amount)
            ->where('description', $this->description)
            ->where('id', '!=', $this->id ?? null)
            ->whereBetween('created_at', [
                now()->subMinutes($minutesWindow),
                now()
            ])
            ->count();

        return $count > 0;
    }
}

