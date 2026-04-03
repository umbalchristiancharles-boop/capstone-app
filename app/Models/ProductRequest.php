<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'unit',
        'requested_by',
        'branch_id',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'product_id',
        'status',
        'approved_by_logistics',
        'logistics_approval_notes',
        'approved_by_owner',
        'owner_approval_notes',
        'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function logisticsApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_logistics');
    }

    public function ownerApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_owner');
    }
}
