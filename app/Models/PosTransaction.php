<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosTransaction extends Model
{
    protected $fillable = [
        'pos_session_id', 'invoice_id', 'transaction_number',
        'payment_method', 'amount', 'cash_amount', 'card_amount',
        'wallet_amount', 'change_amount', 'type', 'notes',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'cash_amount'   => 'decimal:2',
        'card_amount'   => 'decimal:2',
        'wallet_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}