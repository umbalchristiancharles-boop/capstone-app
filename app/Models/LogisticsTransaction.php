<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticsTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_request_id',
        'supplier_order_id',
        'product_id',
        'source_branch_id',
        'destination_branch_id',
        'branch_id',
        'type',
        'status',
        'quantity',
        'quantity_verified',
        'unit',
        'reference_number',
        'description',
        'notes',
        'created_by_user_id',
        'updated_by_user_id',
        'verified_by_user_id',
        'initiated_at',
        'in_transit_at',
        'at_destination_at',
        'verified_at',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'expected_quantity',
        'actual_quantity',
        'variance_reason',
        'source_location',
        'destination_location',
        'delivery_address',
        'receipt_path',
        'proof_of_delivery_path',
        'documentation_files',
        'cost_price',
        'cost_reference',
        'is_duplicate',
        'duplicate_of_transaction_id',
        'audit_notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_verified' => 'decimal:2',
        'expected_quantity' => 'integer',
        'actual_quantity' => 'integer',
        'cost_price' => 'decimal:2',
        'is_duplicate' => 'boolean',
        'initiated_at' => 'datetime',
        'in_transit_at' => 'datetime',
        'at_destination_at' => 'datetime',
        'verified_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'documentation_files' => 'json',
    ];

    // Relationships
    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function supplierOrder(): BelongsTo
    {
        return $this->belongsTo(SupplierOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'source_branch_id');
    }

    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    // Helper methods for status transitions
    public function canTransitionTo($newStatus)
    {
        $validTransitions = [
            'pending' => ['in_transit', 'cancelled'],
            'in_transit' => ['at_destination', 'cancelled'],
            'at_destination' => ['verified', 'cancelled'],
            'verified' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        return in_array($newStatus, $validTransitions[$this->status] ?? []);
    }

    public function transitionTo($newStatus, $userId = null)
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \Exception("Cannot transition from {$this->status} to {$newStatus}");
        }

        $this->status = $newStatus;
        $fieldName = $newStatus . '_at';
        if ($this->hasCast($fieldName)) {
            $this->{$fieldName} = now();
        }

        if ($userId) {
            $this->updated_by_user_id = $userId;
        }

        $this->save();
    }

    // Get variance between expected and actual quantity
    public function getVariance()
    {
        if ($this->actual_quantity === null) {
            return null;
        }
        return $this->actual_quantity - $this->expected_quantity;
    }

    public function hasVariance()
    {
        $variance = $this->getVariance();
        return $variance !== null && $variance !== 0;
    }
}
