<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'bank_name', 'account_name', 'account_number',
        'iban', 'swift_code', 'currency',
        'branch_id', 'account_id',
        'opening_balance', 'current_balance',
        'is_active', 'is_default', 'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active'       => 'boolean',
        'is_default'      => 'boolean',
    ];

    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
    public function transactions(): HasMany { return $this->hasMany(BankTransaction::class); }

protected static function booted(): void
{
    static::creating(function ($bank) {
        if (empty($bank->account_id)) {
            $account = Account::where('code', '1110')->first();
            $bank->account_id = $account?->id;
        }
        if (empty($bank->branch_id) && auth()->check()) {
            $bank->branch_id = auth()->user()->branch_id;
        }
        if (empty($bank->current_balance)) {
            $bank->current_balance = $bank->opening_balance ?? 0;
        }
    });
}
    public function deposit(float $amount, string $description = '', string $reference = ''): BankTransaction
    {
        $this->increment('current_balance', $amount);
        $this->refresh();

        $tx = BankTransaction::create([
            'bank_account_id'  => $this->id,
            'type'             => 'deposit',
            'amount'           => $amount,
            'balance_after'    => $this->current_balance,
            'reference'        => $reference,
            'description'      => $description ?: 'إيداع',
            'transaction_date' => today(),
            'created_by'       => auth()->id(),
        ]);

        $this->createJournalEntry($tx, 'deposit');
        return $tx;
    }

    public function withdraw(float $amount, string $description = '', string $reference = ''): BankTransaction
    {
        if ($this->current_balance < $amount) {
            throw new \Exception('رصيد الحساب البنكي غير كافٍ');
        }

        $this->decrement('current_balance', $amount);
        $this->refresh();

        $tx = BankTransaction::create([
            'bank_account_id'  => $this->id,
            'type'             => 'withdrawal',
            'amount'           => $amount,
            'balance_after'    => $this->current_balance,
            'reference'        => $reference,
            'description'      => $description ?: 'سحب',
            'transaction_date' => today(),
            'created_by'       => auth()->id(),
        ]);

        $this->createJournalEntry($tx, 'withdrawal');
        return $tx;
    }

    public static function transfer(int $fromId, int $toId, float $amount, string $description = ''): void
    {
        $from = static::findOrFail($fromId);
        $to   = static::findOrFail($toId);

        if ($from->current_balance < $amount) {
            throw new \Exception('رصيد الحساب غير كافٍ');
        }

        $from->decrement('current_balance', $amount);
        $from->refresh();

        $to->increment('current_balance', $amount);
        $to->refresh();

        BankTransaction::create([
            'bank_account_id'  => $from->id,
            'type'             => 'transfer_out',
            'amount'           => $amount,
            'balance_after'    => $from->current_balance,
            'description'      => $description ?: 'تحويل لـ ' . $to->bank_name,
            'from_account_id'  => $fromId,
            'to_account_id'    => $toId,
            'transaction_date' => today(),
            'created_by'       => auth()->id(),
        ]);

        BankTransaction::create([
            'bank_account_id'  => $to->id,
            'type'             => 'transfer_in',
            'amount'           => $amount,
            'balance_after'    => $to->current_balance,
            'description'      => $description ?: 'تحويل من ' . $from->bank_name,
            'from_account_id'  => $fromId,
            'to_account_id'    => $toId,
            'transaction_date' => today(),
            'created_by'       => auth()->id(),
        ]);
    }

    private function createJournalEntry(BankTransaction $tx, string $type): void
    {
       $bankAccount = $this->account 
    ?? Account::where('name', 'like', '%بنك%')
              ->orWhere('name', 'like', '%bank%')
              ->first();
        $cashAccount = Account::where('name', 'like', '%نقدية%')->orWhere('code', '1001')->first();

        if (!$bankAccount || !$cashAccount) return;

        $entry = JournalEntry::create([
            'entry_number' => JournalEntry::generateNumber(),
            'entry_date'   => today(),
            'reference'    => $tx->reference ?? 'BNK-' . $tx->id,
            'description'  => $tx->description,
            'status'       => 'posted',
            'created_by'   => auth()->id(),
            'total_debit'  => $tx->amount,
            'total_credit' => $tx->amount,
        ]);

        if ($type === 'deposit') {
            JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $bankAccount->id, 'debit' => $tx->amount, 'credit' => 0, 'description' => 'إيداع بنكي']);
            JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $cashAccount->id, 'debit' => 0, 'credit' => $tx->amount, 'description' => 'إيداع بنكي']);
        } else {
            JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $cashAccount->id, 'debit' => $tx->amount, 'credit' => 0, 'description' => 'سحب بنكي']);
            JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $bankAccount->id, 'debit' => 0, 'credit' => $tx->amount, 'description' => 'سحب بنكي']);
        }

        $tx->update(['journal_entry_id' => $entry->id]);
    }
}