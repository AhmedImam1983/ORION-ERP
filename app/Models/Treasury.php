<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Treasury extends Model
{
    protected $fillable = [
        'name', 'code', 'type', 'branch_id', 'account_id',
        'opening_balance', 'current_balance',
        'is_active', 'is_default', 'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active'       => 'boolean',
        'is_default'      => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TreasuryTransaction::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'main'   => '🏦 رئيسية',
            'branch' => '🏪 فرع',
            'pos'    => '🖥️ كاشير',
            default  => '💰 أخرى',
        };
    }

    // إضافة حركة للخزينة
    public function addTransaction(
        string $type,
        float $amount,
        string $description = '',
        string $reference = '',
        ?int $fromId = null,
        ?int $toId = null,
        ?int $journalEntryId = null
    ): TreasuryTransaction {
        $balanceBefore = $this->current_balance;

        if (in_array($type, ['in', 'transfer_in'])) {
            $this->increment('current_balance', $amount);
        } else {
            $this->decrement('current_balance', $amount);
        }

        $this->refresh();

        return TreasuryTransaction::create([
            'treasury_id'      => $this->id,
            'type'             => $type,
            'amount'           => $amount,
            'balance_after'    => $this->current_balance,
            'reference'        => $reference,
            'description'      => $description,
            'from_treasury_id' => $fromId,
            'to_treasury_id'   => $toId,
            'created_by'       => auth()->id(),
            'journal_entry_id' => $journalEntryId,
            'transaction_date' => today(),
        ]);
    }

    // تحويل بين خزينتين
    public static function transfer(
        int $fromId,
        int $toId,
        float $amount,
        string $description = ''
    ): void {
        $from = static::findOrFail($fromId);
        $to   = static::findOrFail($toId);

        if ($from->current_balance < $amount) {
            throw new \Exception('رصيد الخزينة غير كافٍ');
        }

        $from->addTransaction('transfer_out', $amount, $description ?: 'تحويل لـ ' . $to->name, 'TRF', $fromId, $toId);
        $to->addTransaction('transfer_in', $amount, $description ?: 'تحويل من ' . $from->name, 'TRF', $fromId, $toId);
    }

    public static function getDefault(): ?static
    {
        return static::where('is_default', true)->where('is_active', true)->first()
            ?? static::where('is_active', true)->first();
    }
}