<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'dish_id',
        'is_dish_product',
        'name',
        'slug',
        'category',
        'per_pack_or_individual',
        'pack_quantity',
        'pack_unit',
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
        'real_stock',
        'open_pack_used',
        'published_by',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'pack_quantity' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'is_kitchen_dish' => 'boolean',
        'has_been_ordered' => 'boolean',
        'logistics_request_available' => 'boolean',
        'expires_at' => 'datetime',
        'real_stock' => 'integer',
        'open_pack_used' => 'decimal:4',
        'published_at' => 'datetime',
        'dish_id' => 'integer',
        'published_by' => 'integer',
        'is_dish_product' => 'boolean',
    ];

    // Expose aggregated real stock (sum across supplier/product duplicates) to API consumers
    // When persisted, `real_stock` column will be used. The accessor falls back to computing
    // the sum when the column is not available.
    protected $appends = ['real_stock'];

    /**
     * Get the aggregated stock for this logical product.
     * If the product has an SKU, sum stock across products with the same SKU in the same branch.
     * Otherwise, fall back to exact name match (case-insensitive).
     * This helps surface the "real" available stock when supplier-specific product rows exist.
     */
    public function getRealStockAttribute()
    {
        // Prefer persisted column when present
        if (array_key_exists('real_stock', $this->attributes) && !is_null($this->attributes['real_stock'])) {
            return (int) $this->attributes['real_stock'];
        }

        try {
            $query = self::where('branch_id', $this->branch_id)->where('is_active', 1);

            if (!empty($this->sku)) {
                $query->where('sku', $this->sku);
            } else {
                $name = trim(strtoupper((string) $this->name));
                $query->whereRaw('TRIM(UPPER(name)) = ?', [$name]);
            }

            return (int) $query->sum('stock');
        } catch (\Exception $e) {
            return (int) ($this->attributes['stock'] ?? 0);
        }
    }

    /**
     * Recompute and persist `real_stock` for all product rows in the same group.
     * Grouping is by `branch_id` + `sku` when SKU is present, otherwise by normalized name.
     *
     * @param int $branchId
     * @param string|null $sku
     * @param string|null $name
     * @return void
     */
    public static function recomputeRealStockForGroup(int $branchId, ?string $sku, ?string $name): void
    {
        if (!empty($sku)) {
            $sum = (int) self::where('branch_id', $branchId)->where('sku', $sku)->sum('stock');
            self::where('branch_id', $branchId)->where('sku', $sku)->update(['real_stock' => $sum]);
            return;
        }

        if (empty($name)) return;

        $normalized = trim(strtoupper($name));
        $ids = self::where('branch_id', $branchId)->whereRaw('TRIM(UPPER(name)) = ?', [$normalized])->pluck('id')->toArray();
        if (empty($ids)) return;
        $sum = (int) self::whereIn('id', $ids)->sum('stock');
        self::whereIn('id', $ids)->update(['real_stock' => $sum]);
    }
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

