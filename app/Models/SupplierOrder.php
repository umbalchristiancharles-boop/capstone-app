<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_request_id',
        'product_id',
        'supplier_id',
        'quantity',
        'price',
        'status',
        'fulfilled_at',
        'branch_id',
        'is_broadcast',
        'expires_at',
        'date_made',
    ];

    protected $casts = [
        'fulfilled_at' => 'datetime',
        'is_broadcast' => 'boolean',
        'expires_at' => 'datetime',
        'date_made' => 'date',
    ];

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}

