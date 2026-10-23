<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Models\Concerns\EnforcesBranchAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Expense extends Model
{
    use EnforcesBranchAssignment;

    protected $fillable = [
        'expense_category_id', 'branch_id', 'amount',
        'description', 'expense_date', 'created_by', 'journal_entry_id',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope());

        static::creating(function ($expense) {
            if (empty($expense->branch_id) && auth()->check()) {
                $expense->branch_id = auth()->user()->branch_id;
            }
            if (empty($expense->created_by) && auth()->check()) {
                $expense->created_by = auth()->id();
            }
            if (empty($expense->expense_date)) {
                $expense->expense_date = today();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function paymentVoucher(): HasOne
    {
        return $this->hasOne(PaymentVoucher::class, 'journal_entry_id', 'journal_entry_id');
    }

    public function getPostingStatusAttribute(): string
    {
        if (!$this->journal_entry_id) {
            return 'legacy';
        }

        return $this->paymentVoucher?->status ?? 'posted';
    }
}
