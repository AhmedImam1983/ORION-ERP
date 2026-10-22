<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSessionItem extends Model
{
    protected $fillable = [
        'pos_session_id', 'product_id', 'product_name', 'product_code',
        'unit', 'quantity', 'unit_price', 'cost_price', 'stock', 'total',
        'vat_type', 'tax_percent',
        'vat_exemption_reason_code', 'vat_exemption_reason',
    ];

    protected $casts = [
        'quantity'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'cost_price'  => 'decimal:2',
        'stock'       => 'decimal:2',
        'total'       => 'decimal:2',
        'tax_percent' => 'decimal:2',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
