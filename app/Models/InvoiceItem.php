<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'original_invoice_item_id',
        'product_id',
        'product_name',
        'product_code',
        'unit',
        'quantity',
        'unit_price',
        'cost_price',
        'discount_percent',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'total',
        'notes',
        // ── ZATCA VAT Snapshot ──
        'vat_type',
        'vat_exemption_reason_code',
        'vat_exemption_reason',
    ];

    protected $casts = [
        'quantity'         => 'decimal:3',
        'unit_price'       => 'decimal:2',
        'cost_price'       => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'tax_percent'      => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'total'            => 'decimal:2',
    ];

    public function originalInvoiceItem(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original_invoice_item_id');
    }

    public function returnItems()
    {
        return $this->hasMany(self::class, 'original_invoice_item_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateTotal(): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        $this->discount_amount = $subtotal * ($this->discount_percent / 100);
        $afterDiscount = $subtotal - $this->discount_amount;
        $this->tax_amount = $afterDiscount * ($this->tax_percent / 100);
        $this->total = $afterDiscount + $this->tax_amount;
    }
}