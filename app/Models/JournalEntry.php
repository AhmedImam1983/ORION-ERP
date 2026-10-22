<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'entry_number', 'entry_date', 'reference',
        'description', 'total_debit', 'total_credit', 'status', 'created_by',
    ];

    protected $casts = [
        'entry_date'   => 'date',
        'total_debit'  => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNumber(): string
    {
        $last = static::withTrashed()->max('id');
        $lastEntry = static::withTrashed()->where('id', $last)->first();

        if ($lastEntry) {
            $number = intval(substr($lastEntry->entry_number, -6)) + 1;
        } else {
            $number = 1;
        }

        $entryNumber = 'JE-' . str_pad($number, 6, '0', STR_PAD_LEFT);

        while (static::withTrashed()->where('entry_number', $entryNumber)->exists()) {
            $number++;
            $entryNumber = 'JE-' . str_pad($number, 6, '0', STR_PAD_LEFT);
        }

        return $entryNumber;
    }

    public function calculateTotals(): void
    {
        $this->total_debit  = $this->lines->sum('debit');
        $this->total_credit = $this->lines->sum('credit');
    }

    public function getBalanceDifferenceAttribute(): float
    {
        return round((float) $this->total_debit - (float) $this->total_credit, 2);
    }

    public function getIsBalancedAttribute(): bool
    {
        return abs($this->balance_difference) < 0.01;
    }

    public function getSourceLabelAttribute(): string
    {
        $reference = strtoupper(trim((string) $this->reference));

        if ($reference === 'OPENING') {
            return 'رصيد افتتاحي';
        }

        if (str_contains($reference, '-COGS')) {
            return 'تكلفة مبيعات';
        }

        if (str_starts_with($reference, 'INV-')) {
            return 'فاتورة';
        }

        if (str_starts_with($reference, 'SRT-')) {
            return 'مرتجع بيع';
        }

        if (str_starts_with($reference, 'PRT-')) {
            return 'مرتجع شراء';
        }

        if (str_starts_with($reference, 'PUR-') || str_starts_with($reference, 'PO-')) {
            return 'مشتريات';
        }

        if (str_starts_with($reference, 'RCV-')) {
            return 'سند قبض';
        }

        if (str_starts_with($reference, 'PAY-')) {
            return 'سند صرف';
        }

        if (str_starts_with($reference, 'TRF-')) {
            return 'تحويل مالي';
        }

        if (str_starts_with($reference, 'REV-')) {
            return 'قيد عكسي';
        }

        if (str_starts_with($reference, 'EXP-')) {
            return 'مصروف';
        }

        if (str_starts_with($reference, 'SAL-')) {
            return 'رواتب';
        }

        if ($reference !== '' && ctype_digit(str_replace(['-', ' '], '', $reference))) {
            return 'حركة مالية';
        }

        return 'قيد يدوي';
    }
}
