<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountItem extends Model
{
    protected $fillable = [
        'stock_count_id', 'product_id',
        'system_quantity', 'actual_quantity',
        'difference', 'cost_price', 'difference_value', 'notes',
    ];

    protected $casts = [
        'system_quantity'  => 'decimal:3',
        'actual_quantity'  => 'decimal:3',
        'difference'       => 'decimal:3',
        'cost_price'       => 'decimal:2',
        'difference_value' => 'decimal:2',
    ];

    public function stockCount(): BelongsTo { return $this->belongsTo(StockCount::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    public function getDifferenceColorAttribute(): string
    {
        if ($this->difference > 0) return '#4ade80';
        if ($this->difference < 0) return '#f87171';
        return '#94a3b8';
    }
}