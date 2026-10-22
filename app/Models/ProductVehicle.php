<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVehicle extends Model
{
    protected $fillable = [
        'product_id',
        'make',
        'model',
        'year_from',
        'year_to',
        'engine',
        'notes',
    ];

    protected $casts = [
        'year_from' => 'integer',
        'year_to'   => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getYearRangeAttribute(): string
    {
        if ($this->year_from && $this->year_to) {
            return $this->year_from . ' - ' . $this->year_to;
        }

        return (string) ($this->year_from ?: $this->year_to ?: '-');
    }
}
