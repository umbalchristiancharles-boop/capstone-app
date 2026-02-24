<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'order_code',
        'owner_id',
        'branch_id',
        'customer_name',
        'status',
        'grand_total',
        'ordered_at',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'grand_total' => 'decimal:2',
        ];
    }

    /**
     * Relationship: Order belongs to a user (owner)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Relationship: Order belongs to a branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
