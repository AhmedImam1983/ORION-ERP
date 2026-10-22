<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingReversal extends Model
{
    protected $fillable = [
        'reversal_number',
        'reversal_date',
        'source_type',
        'source_id',
        'source_number',
        'original_journal_entry_id',
        'reversal_journal_entry_id',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'reversal_date' => 'date',
    ];

    public function originalJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'original_journal_entry_id');
    }

    public function reversalJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversal_journal_entry_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');

        $last = static::query()
            ->where('reversal_number', 'like', "REV-{$year}-%")
            ->orderByDesc('id')
            ->first();

        $number = 1;

        if ($last && preg_match('/REV-\d{4}-(\d+)$/', $last->reversal_number, $matches)) {
            $number = ((int) $matches[1]) + 1;
        }

        $candidate = 'REV-' . $year . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);

        while (static::query()->where('reversal_number', $candidate)->exists()) {
            $number++;
            $candidate = 'REV-' . $year . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    public function getSourceTypeLabelAttribute(): string
    {
        return match ($this->source_type) {
            'invoice'            => 'فاتورة',
            'receipt_voucher'    => 'سند قبض',
            'payment_voucher'    => 'سند صرف',
            'financial_transfer' => 'تحويل مالي',
            default              => $this->source_type,
        };
    }
}
