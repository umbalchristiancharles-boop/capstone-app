<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceMarkupRequest extends Model
{
    protected $fillable = [
        'branch_id',
        'requested_by',
        'current_percentage',
        'requested_percentage',
        'reason',
        'status',
        'main_finance_approval',
        'main_finance_approved_by',
        'main_finance_approved_at',
        'main_finance_notes',
        'owner_approval',
        'owner_approved_by',
        'owner_approved_at',
        'owner_notes',
        'activated_at',
    ];

    protected $casts = [
        'current_percentage' => 'decimal:2',
        'requested_percentage' => 'decimal:2',
        'main_finance_approved_at' => 'datetime',
        'owner_approved_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relationship: Requested by user
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Relationship: Main finance approved by
     */
    public function mainFinanceApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'main_finance_approved_by');
    }

    /**
     * Relationship: Owner approved by
     */
    public function ownerApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_approved_by');
    }

    /**
     * Check if request is fully approved
     */
    public function isFullyApproved(): bool
    {
        return $this->main_finance_approval === 'approved' && $this->owner_approval === 'approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->main_finance_approval === 'rejected' || $this->owner_approval === 'rejected';
    }
}
