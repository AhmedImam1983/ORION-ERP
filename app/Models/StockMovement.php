<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id', 'warehouse_id', 'branch_id', 'invoice_id',
        'type', 'quantity', 'unit_price', 'total',
        'balance_before', 'balance_after', 'reference', 'notes', 'created_by',
    ];

    protected $casts = [
        'quantity'       => 'decimal:3',
        'unit_price'     => 'decimal:2',
        'total'          => 'decimal:2',
        'balance_before' => 'decimal:3',
        'balance_after'  => 'decimal:3',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'purchase'        => 'شراء',
            'sale'            => 'بيع',
            'sale_return'     => 'مرتجع بيع',
            'purchase_return' => 'مرتجع شراء',
            'transfer_in'     => 'تحويل وارد',
            'transfer_out'    => 'تحويل صادر',
            'adjustment_in'   => 'تسوية زيادة',
            'adjustment_out'  => 'تسوية نقص',
            'opening'         => 'رصيد افتتاحي',
            default           => $this->type,
        };
    }

    public function isIncoming(): bool
    {
        return in_array($this->type, ['purchase', 'sale_return', 'transfer_in', 'adjustment_in', 'opening']);
    }
}