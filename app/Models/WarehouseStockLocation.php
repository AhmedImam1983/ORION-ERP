<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseStockLocation extends Model
{
    protected $table = 'warehouse_stock_locations';

    protected $fillable = [
        'warehouse_stock_id',
        'location_code',
        'notes',
    ];

    public function warehouseStock(): BelongsTo
    {
        return $this->belongsTo(WarehouseStock::class);
    }
}
