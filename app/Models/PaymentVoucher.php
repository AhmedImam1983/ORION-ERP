<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Models\Concerns\EnforcesBranchAssignment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVoucher extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope());
    }

    use EnforcesBranchAssignment;

    protected $fillable = [
        'voucher_number',
        'payment_date',
        'branch_id',
        'purpose',
        'supplier_id',
        'invoice_id',
        'expense_account_id',
        'source_type',
        'treasury_id',
        'bank_account_id',
        'amount',
        'payment_method',
        'beneficiary_name',
        'reference',
        'description',
        'status',
        'journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
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
            ->where('voucher_number', 'like', "PAY-{$year}-%")
            ->orderByDesc('id')
            ->first();

        $number = 1;

        if ($last && preg_match('/PAY-\d{4}-(\d+)$/', $last->voucher_number, $matches)) {
            $number = ((int) $matches[1]) + 1;
        }

        $candidate = 'PAY-' . $year . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);

        while (static::withoutGlobalScopes()->where('voucher_number', $candidate)->exists()) {
            $number++;
            $candidate = 'PAY-' . $year . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    public function getPurposeLabelAttribute(): string
    {
        return match ($this->purpose) {
            'supplier_payment' => 'سداد مورد',
            'direct_expense'   => 'مصروف مباشر',
            'customer_refund' => 'رد مبلغ لعميل',
            default            => $this->purpose,
        };
    }

    public function getSourceLabelAttribute(): string
    {
        if ($this->source_type === 'treasury') {
            return $this->treasury?->name ?? 'خزينة';
        }

        if ($this->source_type === 'bank') {
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
            'wallet'        => 'محفظة',
            'cheque'        => 'شيك',
            default         => $this->payment_method,
        };
    }
}
