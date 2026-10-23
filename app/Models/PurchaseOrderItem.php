<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class PurchaseOrderItem extends Model
{
    protected static function booted(): void
    {
        static::saving(function (PurchaseOrderItem $item): void {
            if ($item->product_id && Product::query()->whereKey($item->product_id)->where('item_type', 'service')->exists()) {
                throw ValidationException::withMessages([
                    'items' => 'أوامر الشراء المخزنية تقبل الأصناف المخزنية فقط؛ الخدمة المشتراة تُسجل كمصروف.',
                ]);
            }
        });
    }

    protected $fillable = [
        'purchase_order_id', 'product_id', 'product_name', 'product_code',
        'unit', 'quantity', 'received_quantity', 'unit_price', 'total', 'notes',
    ];

    protected $casts = [
        'quantity'          => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'unit_price'        => 'decimal:2',
        'total'             => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}