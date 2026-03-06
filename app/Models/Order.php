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
        'grand_total',
        'amount_paid',
        'change_amount',
        'ordered_at',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at'    => 'datetime',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
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
