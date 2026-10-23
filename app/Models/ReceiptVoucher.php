<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Models\Concerns\EnforcesBranchAssignment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptVoucher extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope());
    }

    use EnforcesBranchAssignment;

    protected $fillable = [
        'voucher_number',
        'receipt_date',
        'branch_id',
        'purpose',
        'customer_id',
        'supplier_id',
        'invoice_id',
        'installment_id',
        'destination_type',
        'treasury_id',
        'bank_account_id',
        'amount',
        'payment_method',
        'reference',
        'description',
        'status',
        'journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    public function treasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
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

        $last = static::withoutGlobalScopes()
            ->where('voucher_number', 'like', "RCV-{$year}-%")
            ->orderByDesc('id')
            ->first();

        $number = 1;

        if ($last && preg_match('/RCV-\d{4}-(\d+)$/', $last->voucher_number, $matches)) {
            $number = ((int) $matches[1]) + 1;
        }

        $candidate = 'RCV-' . $year . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);

        while (static::withoutGlobalScopes()->where('voucher_number', $candidate)->exists()) {
            $number++;
            $candidate = 'RCV-' . $year . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }


    public function getPurposeLabelAttribute(): string
    {
        return match ($this->purpose) {
            'supplier_refund' => 'استرداد من مورد',
            'customer_receipt', null, '' => 'تحصيل من عميل',
            default => $this->purpose,
        };
    }

    public function getCounterpartyLabelAttribute(): string
    {
        if ($this->purpose === 'supplier_refund') {
            return $this->supplier?->name ?? 'مورد';
        }

        return $this->customer?->name ?? 'عميل نقدي';
    }

    public function getDestinationLabelAttribute(): string
    {
        if ($this->destination_type === 'treasury') {
            return $this->treasury?->name ?? 'خزينة';
        }

        if ($this->destination_type === 'bank') {
            return trim(
                ($this->bankAccount?->bank_name ?? 'بنك') .
                ' — ' .
                ($this->bankAccount?->account_name ?? '')
            );
        }

        return '—';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'          => 'نقدي',
            'bank_transfer' => 'تحويل بنكي',
            'card'          => 'بطاقة',
            'cheque'        => 'شيك',
            default         => $this->payment_method,
        };
    }
}
