<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'item_type', 'name', 'name_en',
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

    /**
     * Suggest the next unique code for a new stock item or service.
     * Existing legacy codes (EL-001, RS-001, etc.) are left untouched.
     */
    public static function nextAutoCode(string $type = 'stock'): string
    {
        $prefix = $type === 'service' ? 'SRV' : 'ITEM';

        // Query the table directly so soft-deleted products also reserve their codes.
        $codes = \Illuminate\Support\Facades\DB::table((new static())->getTable())
            ->where('code', 'like', $prefix . '-%')
            ->pluck('code');

        $max = 0;
        $pattern = '/^' . preg_quote($prefix, '/') . '-([0-9]+)$/i';
        foreach ($codes as $existingCode) {
            if (preg_match($pattern, (string) $existingCode, $matches) === 1) {
                $max = max($max, (int) $matches[1]);
            }
        }

        do {
            $candidate = sprintf('%s-%04d', $prefix, ++$max);
        } while (\Illuminate\Support\Facades\DB::table((new static())->getTable())
            ->where('code', $candidate)
            ->exists());

        return $candidate;
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            $product->item_type = $product->item_type ?: 'stock';

            if (!in_array($product->item_type, ['stock', 'service'], true)) {
                throw ValidationException::withMessages([
                    'item_type' => 'نوع العنصر غير صالح. اختر صنفاً مخزنياً أو خدمة.',
                ]);
            }

            // A service is non-stockable in V1. Its labor/subcontract cost must not
            // be credited to inventory until service-cost accounting is configured.
            if ($product->isService()) {
                $product->warehouse_id = null;
                $product->quantity = 0;
                $product->min_quantity = 0;
                $product->cost_price = 0;

                if (!$product->category_id) {
                    $product->category_id = Category::query()
                        ->where(function ($query) {
                            $query->where('code', 'SERVICES')
                                ->orWhere('name', 'الخدمات');
                        })
                        ->value('id');
                }
            }

            // Changing an established product from stock to service (or vice versa)
            // could invalidate historic stock movements. Create a new product instead.
            if ($product->exists && $product->isDirty('item_type')) {
                throw ValidationException::withMessages([
                    'item_type' => 'لا يمكن تغيير نوع العنصر بعد إنشائه حفاظاً على سجل المخزون والفواتير. أنشئ عنصراً جديداً بالنوع المطلوب.',
                ]);
            }
        });

        static::creating(function (Product $product): void {
            $product->item_type = $product->item_type ?: 'stock';

            // Product creation from the UI previews an automatic code, but the server
            // owns the final value. Re-pick it if the preview was stale or mismatched.
            $expectedPrefix = $product->isService() ? 'SRV' : 'ITEM';
            $code = trim((string) $product->code);
            $isAutoCode = preg_match('/^(ITEM|SRV)-[0-9]+$/i', $code) === 1;

            if (blank($code) || $isAutoCode) {
                $matchesType = str_starts_with(strtoupper($code), $expectedPrefix . '-');
                $alreadyExists = $code !== ''
                    && \Illuminate\Support\Facades\DB::table($product->getTable())
                        ->where('code', $code)
                        ->exists();

                if (!$matchesType || $alreadyExists) {
                    $product->code = static::nextAutoCode($product->item_type);
                } else {
                    $product->code = strtoupper($code);
                }
            }

            if ($product->isService()) {
                $product->warehouse_id = null;
                $product->quantity = 0;
                $product->min_quantity = 0;
                $product->cost_price = 0;
                if (blank($product->unit)) {
                    $product->unit = 'خدمة';
                }
                return;
            }

            if (!$product->warehouse_id) {
                $product->warehouse_id = Warehouse::getDefault()?->id;
            }
        });

        // Covers Product creation from any entry point (Filament, import, seeder, etc.).
        // Service items deliberately never receive a warehouse stock row.
        static::created(function (Product $product): void {
            if ($product->isService() || !$product->warehouse_id) {
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

    public function isService(): bool
    {
        return $this->item_type === 'service';
    }

    public function isStockItem(): bool
    {
        return !$this->isService();
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
