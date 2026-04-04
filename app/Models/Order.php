<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'order_code',
        'owner_id',
        'cashier_id',
        'branch_id',
        'customer_name',
        'status',
        'subtotal',
        'discount_type',
        'discount_percent',
        'discount_amount',
        'vat_percent',
        'vat_amount',
        'grand_total',
        'amount_paid',
        'change_amount',
        'ordered_at',
        'is_cancelled',
        'cancelled_at',
        'cancelled_by',
        'refund_reason',
        'approved_at',
        'approved_by',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'ordered_at'    => 'datetime',
        'cancelled_at'  => 'datetime',
        'approved_at'   => 'datetime',
        'completed_at'  => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'subtotal'      => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'vat_percent'   => 'decimal:2',
        'vat_amount'    => 'decimal:2',
        'grand_total'   => 'decimal:2',
        'amount_paid'   => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // ============ FINANCIAL SCOPES ============

    /**
     * Scope: Get only completed orders (actual revenue)
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Get only cancelled orders (refunds)
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope: Get orders in kitchen or pending (not finalized)
     */
    public function scopeUnfinalized($query)
    {
        return $query->whereIn('status', ['pending', 'in_kitchen', 'approved']);
    }

    /**
     * Scope: Sum total revenue (completed orders only)
     */
    public function scopeTotalRevenue($query)
    {
        return $query->completed()->sum('grand_total');
    }

    /**
     * Scope: Sum total refunds (cancelled orders only)
     */
    public function scopeTotalRefunds($query)
    {
        return $query->cancelled()->sum('grand_total');
    }

    /**
     * Scope: Get orders by branch
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope: Get orders within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Check if this order has a duplicate in recent records
     *
     * @param int $minutesWindow
     * @return bool
     */
    public function hasDuplicate($minutesWindow = 5)
    {
        $count = static::where('order_code', $this->order_code)
            ->where('branch_id', $this->branch_id)
            ->where('id', '!=', $this->id)
            ->whereBetween('created_at', [
                now()->subMinutes($minutesWindow),
                now()
            ])
            ->count();

        return $count > 0;
    }

    /**
     * Verify order calculations are correct
     *
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function verifyCalculations()
    {
        return \App\Services\FinancialConsistencyValidator::validateOrderCalculations($this);
    }

    /**
     * Verify refund data is complete (for cancelled orders)
     *
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function verifyRefundData()
    {
        return \App\Services\FinancialConsistencyValidator::validateRefundData($this);
    }
}
