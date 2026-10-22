<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'name_en', 'parent_id', 'type',
        'level', 'opening_balance', 'balance_type', 'is_active', 'notes',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function getBalanceAttribute(): float
    {
        $debit  = $this->journalLines()->sum('debit');
        $credit = $this->journalLines()->sum('credit');
        return $this->balance_type === 'debit'
            ? ($this->opening_balance + $debit - $credit)
            : ($this->opening_balance + $credit - $debit);
    }
}