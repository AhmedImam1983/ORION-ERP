<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class WarehouseStock extends Model
{
    protected static function booted(): void
    {
        static::saving(function (WarehouseStock $stock): void {
            $productId = $stock->product_id ?: $stock->getOriginal('product_id');

            if ($productId && Product::withTrashed()->whereKey($productId)->value('item_type') === 'service') {
                throw new RuntimeException('الخدمات لا تنشئ أرصدة مخزنية. استخدم صنفاً مخزنياً لحركات المخزون.');
            }
        });
    }

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'opening_quantity',
        'quantity',
    ];

    protected $casts = [
        'opening_quantity' => 'decimal:3',
        'quantity'         => 'decimal:3',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(WarehouseStockLocation::class);
    }
}
