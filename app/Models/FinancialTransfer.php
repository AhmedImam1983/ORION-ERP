<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransfer extends Model
{
    protected $fillable = [
        'transfer_number', 'transfer_date',
        'from_type', 'from_treasury_id', 'from_bank_account_id',
        'to_type', 'to_treasury_id', 'to_bank_account_id',
        'amount', 'external_reference', 'description',
        'status', 'journal_entry_id', 'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function fromTreasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class, 'from_treasury_id');
    }

    public function fromBankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'from_bank_account_id');
    }

    public function toTreasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class, 'to_treasury_id');
    }

    public function toBankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'to_bank_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');

        $last = static::query()
            ->where('transfer_number', 'like', "TRF-{$year}-%")
            ->orderByDesc('id')
            ->first();

        $number = 1;

        if ($last && preg_match('/TRF-\d{4}-(\d+)$/', $last->transfer_number, $matches)) {
            $number = ((int) $matches[1]) + 1;
        }

        $candidate = 'TRF-' . $year . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);

        while (static::query()->where('transfer_number', $candidate)->exists()) {
            $number++;
            $candidate = 'TRF-' . $year . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    public function getFromLabelAttribute(): string
    {
        if ($this->from_type === 'treasury') {
            return $this->fromTreasury?->name ?? 'خزينة';
        }

        return trim(
            ($this->fromBankAccount?->bank_name ?? 'بنك') .
            ' — ' .
            ($this->fromBankAccount?->account_name ?? '')
        );
    }

    public function getToLabelAttribute(): string
    {
        if ($this->to_type === 'treasury') {
            return $this->toTreasury?->name ?? 'خزينة';
        }

        return trim(
            ($this->toBankAccount?->bank_name ?? 'بنك') .
            ' — ' .
            ($this->toBankAccount?->account_name ?? '')
        );
    }

    public function getRouteLabelAttribute(): string
    {
        return match ($this->from_type . '>' . $this->to_type) {
            'treasury>treasury' => 'خزينة ← خزينة',
            'bank>bank'         => 'بنك ← بنك',
            'treasury>bank'     => 'خزينة ← بنك',
            'bank>treasury'     => 'بنك ← خزينة',
            default             => 'تحويل مالي',
        };
    }
}
