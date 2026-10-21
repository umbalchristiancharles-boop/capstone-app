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
        'brand',
        'description',
        'storage_requirements',
        'is_perishable',
        'unit',
        'image_path',
        'per_pack_or_individual',
        'pack_quantity',
        'pack_unit',
        'price',
        'cost_price',
        'stock',
        'min_stock',
        'sku',
        'barcode',
        'barcode_is_generated',
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
        'is_perishable' => 'boolean',
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
        'barcode_is_generated' => 'boolean',
        'requires_logistics' => 'boolean',
        'approved_by_logistics_main' => 'integer',
        'approved_by_owner' => 'integer',
        'approved_at' => 'datetime',
    ];

    // Expose aggregated real stock for products belonging to the same supplier.
    // When persisted, `real_stock` column will be used. The accessor falls back
    // to computing the supplier-specific sum when the column is not available.
    protected $appends = ['real_stock', 'image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        $path = ltrim($this->image_path, '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'product-images/')) {
            if (!is_file(public_path($path)) && !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return null;
            }

            return asset('product-image/' . substr($path, strlen('product-images/')));
        }

        if (!is_file(public_path('storage/' . $path)) && !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return null;
        }

        return asset('storage/' . $path);
    }

    /**
     * Get the aggregated stock for this logical product and supplier.
     * If the product has an SKU, sum stock across products with the same SKU,
     * branch, and supplier. Otherwise, fall back to an exact name match.
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

            if ($this->supplier_id === null) {
                $query->whereNull('supplier_id');
            } else {
                $query->where('supplier_id', $this->supplier_id);
            }

            return (int) $query->sum('stock');
        } catch (\Exception $e) {
            return (int) ($this->attributes['stock'] ?? 0);
        }
    }

    /**
     * Recompute and persist `real_stock` per supplier within a product group.
     * Product grouping is by `branch_id` + `sku` when SKU is present, otherwise
     * by normalized name.
     *
     * @param int $branchId
     * @param string|null $sku
     * @param string|null $name
     * @return void
     */
    public static function recomputeRealStockForGroup(int $branchId, ?string $sku, ?string $name): void
    {
        $query = self::where('branch_id', $branchId);
        if (!empty($sku)) {
            $query->where('sku', $sku);
        } else {
            if (empty($name)) return;
            $normalized = trim(strtoupper($name));
            $query->whereRaw('TRIM(UPPER(name)) = ?', [$normalized]);
        }

        $products = $query->get(['id', 'supplier_id', 'stock']);
        foreach ($products->groupBy(fn (Product $product) => $product->supplier_id === null
            ? 'unassigned'
            : 'supplier:' . $product->supplier_id) as $supplierProducts) {
            $sum = (int) $supplierProducts->sum('stock');
            self::whereIn('id', $supplierProducts->pluck('id'))->update(['real_stock' => $sum]);
        }
    }

    public static function transferInventoryForSupplierChange(Product $sourceProduct, User $newSupplier, ?Product $targetProduct = null): Product
    {
        return DB::transaction(function () use ($sourceProduct, $newSupplier, $targetProduct) {
            $source = self::where('id', $sourceProduct->id)->lockForUpdate()->firstOrFail();

            $supplierName = trim((string) ($newSupplier->full_name ?? $newSupplier->username ?? $newSupplier->email ?? ''));
            $supplierName = $supplierName !== '' ? $supplierName : null;

            if ((int) $source->supplier_id === (int) $newSupplier->id) {
                if ($source->supplier_name !== $supplierName) {
                    $source->supplier_name = $supplierName;
                    $source->save();
                }

                self::clearOtherSupplierStock($source);
                self::recomputeRealStockForGroup($source->branch_id, $source->sku, $source->name);
                return $source->fresh();
            }

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

                $destination->stock = (int) $destination->stock + $sourceStock;
                $destination->supplier_id = $newSupplier->id;
                $destination->supplier_name = $supplierName;
                $destination->is_published = true;
                $destination->save();

                $source->stock = 0;
                $source->real_stock = 0;
                $source->save();

                self::clearOtherSupplierStock($destination);
                self::recomputeRealStockForGroup($destination->branch_id, $destination->sku, $destination->name);
                return $destination->fresh();
            }

            throw new \RuntimeException('No product row exists for the selected supplier');
        });
    }

    private static function clearOtherSupplierStock(Product $selectedProduct): void
    {
        $query = self::where('branch_id', $selectedProduct->branch_id)
            ->where('id', '<>', $selectedProduct->id);

        if (!empty($selectedProduct->sku)) {
            $query->where('sku', $selectedProduct->sku);
        } else {
            $query->whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper((string) $selectedProduct->name))]);
        }

        if ($selectedProduct->supplier_id === null) {
            $query->whereNotNull('supplier_id');
        } else {
            $query->where(function ($supplierQuery) use ($selectedProduct) {
                $supplierQuery->whereNull('supplier_id')
                    ->orWhere('supplier_id', '<>', $selectedProduct->supplier_id);
            });
        }

        $query->update(['stock' => 0, 'real_stock' => 0]);
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
