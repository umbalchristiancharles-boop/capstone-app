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
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at'    => 'datetime',
            'cancelled_at'  => 'datetime',
            'approved_at'   => 'datetime',
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
    }

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
}
