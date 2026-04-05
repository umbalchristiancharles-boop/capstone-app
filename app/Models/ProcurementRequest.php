<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'logistics_user_id',
        'procurement_user_id',
        'finance_user_id',
        'supplier_id',
        'product_id',
        'quantity',
        'price',
        'total_amount',
        'status',
        'budget_approved',
        'supplier_confirmed',
        'budget_amount',
        'branch_id',
        'receipt_path',
        'receipt_uploaded_by',
        'receipt_uploaded_at',
        'receipt_confirmed',
        'receipt_confirmed_by',
        'receipt_confirmed_at',
        'confirmed_quantity',
        'variance_quantity',
        'variance_reason',
        'variance_reported_at',
        'delivery_proof_path',
    ];

    protected $casts = [
        'budget_approved' => 'boolean',
        'supplier_confirmed' => 'boolean',
        'budget_amount' => 'decimal:2',
        'price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'receipt_confirmed' => 'boolean',
        'confirmed_quantity' => 'integer',
        'variance_quantity' => 'integer',
        'variance_reported_at' => 'datetime',
    ];

    public function logisticsUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logistics_user_id');
    }

    public function procurementUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procurement_user_id');
    }

    public function financeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplierOrders(): HasMany
    {
        return $this->hasMany(SupplierOrder::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}

