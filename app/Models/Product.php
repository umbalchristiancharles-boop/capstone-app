<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'per_pack_or_individual',
        'price',
        'cost_price',
        'stock',
        'min_stock',
        'sku',
        'branch_id',
        'supplier_name',
        'supplier_id',
        'is_published',
        'is_active',
        'is_kitchen_dish',
        'has_been_ordered',
        'logistics_request_available',
        'expires_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'is_kitchen_dish' => 'boolean',
        'has_been_ordered' => 'boolean',
        'logistics_request_available' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProductComment::class);
    }

    public function procurementRequests(): HasMany
    {
        return $this->hasMany(ProcurementRequest::class);
    }

    public function dishIngredients(): HasMany
    {
        return $this->hasMany(DishIngredient::class);
    }
}

