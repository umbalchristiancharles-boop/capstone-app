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
        'date_made',
        'real_stock',
        'open_pack_used',
        'published_by',
        'published_at',
        'status',
        'requires_logistics',
        'approved_by_logistics_main',
        'approved_by_owner',
        'rejection_reason',
        'approved_at',
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
        'date_made' => 'date',
        'real_stock' => 'integer',
        'open_pack_used' => 'decimal:4',
        'published_at' => 'datetime',
        'dish_id' => 'integer',
        'published_by' => 'integer',
        'is_dish_product' => 'boolean',
        'requires_logistics' => 'boolean',
        'approved_by_logistics_main' => 'integer',
        'approved_by_owner' => 'integer',
        'approved_at' => 'datetime',
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

    public static function transferInventoryForSupplierChange(Product $sourceProduct, User $newSupplier, ?Product $targetProduct = null): Product
    {
        return DB::transaction(function () use ($sourceProduct, $newSupplier, $targetProduct) {
            $source = self::where('id', $sourceProduct->id)->lockForUpdate()->firstOrFail();

            $supplierName = trim((string) ($newSupplier->full_name ?? $newSupplier->username ?? $newSupplier->email ?? ''));
            $supplierName = $supplierName !== '' ? $supplierName : null;

            $destination = $targetProduct
                ? self::where('id', $targetProduct->id)->lockForUpdate()->first()
                : self::where('branch_id', $source->branch_id)
                    ->where('id', '<>', $source->id)
                    ->where('supplier_id', $newSupplier->id)
                    ->where(function ($query) use ($source) {
                        if (!empty($source->sku)) {
                            $query->where('sku', $source->sku);
                        } else {
                            $query->whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper((string) $source->name))]);
                        }
                    })
                    ->lockForUpdate()
                    ->first();

            if ($destination && $destination->id !== $source->id) {
                $sourceStock = (int) $source->stock;
                $sourceRealStock = (int) $source->real_stock;

                $destination->stock = (int) $destination->stock + $sourceStock;
                $destination->real_stock = (int) $destination->real_stock + $sourceRealStock;
                $destination->supplier_id = $newSupplier->id;
                $destination->supplier_name = $supplierName;
                $destination->save();

                $source->stock = 0;
                $source->real_stock = 0;
                $source->supplier_id = $newSupplier->id;
                $source->supplier_name = $supplierName;
                $source->save();

                self::recomputeRealStockForGroup((int) $source->branch_id, $source->sku, $source->name);
                self::recomputeRealStockForGroup((int) $destination->branch_id, $destination->sku, $destination->name);

                return $destination->fresh();
            }

            $source->supplier_id = $newSupplier->id;
            $source->supplier_name = $supplierName;
            $source->stock = (int) $source->stock;
            $source->real_stock = (int) $source->real_stock;
            $source->save();

            self::recomputeRealStockForGroup((int) $source->branch_id, $source->sku, $source->name);

            return $source->fresh();
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function logisticsApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_logistics_main');
    }

    public function ownerApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_owner');
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

    public function inventoryLots(): HasMany
    {
        return $this->hasMany(InventoryLot::class);
    }
}

