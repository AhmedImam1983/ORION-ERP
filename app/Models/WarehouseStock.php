<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseStock extends Model
{
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
