<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetRequest extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'purpose',
        'requested_amount',
        'status',
        'date_requested',
        'processed_by',
        'date_processed',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'date_requested' => 'date',
        'date_processed' => 'date',
    ];

    /**
     * Relationship: Budget request belongs to a branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relationship: Budget request belongs to a user (requester)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Budget request processed by a user
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}

