<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_account_id', 'type', 'amount', 'balance_after',
        'reference', 'description',
        'from_account_id', 'to_account_id',
        'transaction_date', 'created_by', 'journal_entry_id',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'balance_after'    => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function bankAccount(): BelongsTo { return $this->belongsTo(BankAccount::class); }
    public function fromAccount(): BelongsTo { return $this->belongsTo(BankAccount::class, 'from_account_id'); }
    public function toAccount(): BelongsTo { return $this->belongsTo(BankAccount::class, 'to_account_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'deposit'      => '⬇️ إيداع',
            'withdrawal'   => '⬆️ سحب',
            'transfer_in'  => '↙️ تحويل وارد',
            'transfer_out' => '↗️ تحويل صادر',
            default        => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'deposit', 'transfer_in'    => '#4ade80',
            'withdrawal', 'transfer_out' => '#f87171',
            default                      => '#94a3b8',
        };
    }
}