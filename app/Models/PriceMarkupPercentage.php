<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceMarkupPercentage extends Model
{
    protected $fillable = [
        'branch_id',
        'percentage',
        'is_active',
        'set_by',
        'set_at',
        'notes',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'set_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relationship: Set by user
     */
    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }

    /**
     * Get the markup multiplier (e.g., 20% becomes 1.20)
     */
    public function getMultiplier(): float
    {
        return 1 + ((float) $this->percentage / 100);
    }
}
