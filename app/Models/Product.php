<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'name_en',
        'category_id', 'brand_id', 'part_number', 'part_number_normalized', 'warehouse_id',
        'unit', 'cost_price', 'sale_price',
        'sale_price_2', 'sale_price_3',
        'quantity', 'min_quantity',
        'barcode', 'tax_code', 'gpc_brick',
        'description', 'image', 'is_active',
        'vat_type', 'vat_percent',
        'vat_exemption_reason_code', 'vat_exemption_reason',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'cost_price'   => 'decimal:2',
        'sale_price'   => 'decimal:2',
        'sale_price_2' => 'decimal:2',
        'sale_price_3' => 'decimal:2',
        'quantity'     => 'decimal:3',
        'min_quantity' => 'decimal:3',
        'vat_percent'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (!$product->warehouse_id) {
                $product->warehouse_id = Warehouse::getDefault()?->id;
            }
        });

        // Covers Product creation from any entry point (Filament, import, seeder, etc.).
        // Existing products are migrated separately by the warehouse_stocks migration.
        static::created(function (Product $product) {
            if (!$product->warehouse_id) {
                return;
            }

            $quantity = max(0, round((float) ($product->quantity ?? 0), 3));

            WarehouseStock::query()->firstOrCreate(
                [
                    'product_id'   => $product->id,
                    'warehouse_id' => $product->warehouse_id,
                ],
                [
                    'opening_quantity' => $quantity,
                    'quantity'         => $quantity,
                ]
            );
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(ProductVehicle::class);
    }

    /**
     * Compatibility/default warehouse only.
     * The real stock location is warehouseStocks().
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockInWarehouse(int $warehouseId): float
    {
        return round((float) ($this->warehouseStocks()
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0), 3);
    }
}
