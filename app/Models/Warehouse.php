<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'code', 'name', 'name_en', 'location',
        'phone', 'manager', 'is_active', 'is_default', 'notes',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Legacy/default-product relationship kept for compatibility only.
     * Do not use this relation to calculate real warehouse stock.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function stockProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'warehouse_stocks')
            ->withPivot(['opening_quantity', 'quantity'])
            ->withTimestamps();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first()
            ?? static::where('is_active', true)->first();
    }
}
