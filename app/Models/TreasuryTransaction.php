<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreasuryTransaction extends Model
{
    protected $fillable = [
        'treasury_id', 'type', 'amount', 'balance_after',
        'reference', 'description',
        'from_treasury_id', 'to_treasury_id',
        'created_by', 'journal_entry_id', 'transaction_date',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'balance_after'    => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function treasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class);
    }

    public function fromTreasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class, 'from_treasury_id');
    }

    public function toTreasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class, 'to_treasury_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in'           => '⬇️ وارد',
            'out'          => '⬆️ صادر',
            'transfer_in'  => '↙️ تحويل وارد',
            'transfer_out' => '↗️ تحويل صادر',
            default        => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'in', 'transfer_in'   => '#4ade80',
            'out', 'transfer_out' => '#f87171',
            default               => '#94a3b8',
        };
    }
}