<?php

namespace App\Filament\Admin\Pages;

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Treasury;
use App\Services\PaymentPostingService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExpensesPage extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin',
            'admin',
            'accountant',
        ]);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'المصروفات اليومية';
    protected static ?string $title = 'المصروفات اليومية';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.admin.pages.expenses-page';

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public float $amount = 0;
    public string $description = '';
    public ?int $selectedCategoryId = null;
    public string $filterDate = '';

    public string $sourceType = 'treasury';
    public ?int $treasuryId = null;
    public ?int $bankAccountId = null;
    public string $paymentMethod = 'cash';

    public function mount(): void
    {
        $this->filterDate = today()->format('Y-m-d');

        $defaultTreasury = Treasury::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first()
            ?? Treasury::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->first();

        $this->treasuryId = $defaultTreasury?->id;
    }

    public function getCategories()
    {
        return ExpenseCategory::query()
            ->with('account')
            ->where('is_active', true)
            ->orderBy('sort')
            ->get();
    }

    public function getTreasuries()
    {
        return Treasury::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function getBankAccounts()
    {
        return BankAccount::query()
            ->where('is_active', true)
            ->orderBy('bank_name')
            ->orderBy('account_name')
            ->get();
    }

    public function getTodayExpenses()
    {
        return Expense::with('category', 'createdBy', 'journalEntry', 'paymentVoucher')
            ->whereDate('expense_date', $this->filterDate)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getTodayTotal(): float
    {
        return (float) $this->getTodayExpenses()
            ->reject(fn ($expense) => $expense->posting_status === 'reversed')
            ->sum('amount');
    }

    public function selectCategory(int $id): void
    {
        $this->selectedCategoryId = $id;
        $this->amount = 0;
        $this->description = '';
    }

    public function updatedSourceType(string $value): void
    {
        if ($value === 'bank') {
            $this->treasuryId = null;
            $this->paymentMethod = 'bank_transfer';

            if (!$this->bankAccountId) {
                $this->bankAccountId = BankAccount::query()
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->value('id');
            }

            return;
        }

        $this->bankAccountId = null;
        $this->paymentMethod = 'cash';

        if (!$this->treasuryId) {
            $this->treasuryId = Treasury::query()
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->value('id');
        }
    }

    public function saveExpense(): void
    {
        if (!$this->selectedCategoryId) {
            Notification::make()
                ->title('اختر نوع المصروف أولاً')
                ->warning()
                ->send();

            return;
        }

        if ($this->amount <= 0) {
            Notification::make()
                ->title('أدخل مبلغًا صحيحًا')
                ->warning()
                ->send();

            return;
        }

        if (!in_array($this->sourceType, ['treasury', 'bank'], true)) {
            Notification::make()
                ->title('اختر مصدر الصرف')
                ->warning()
                ->send();

            return;
        }

        if ($this->sourceType === 'treasury' && !$this->treasuryId) {
            Notification::make()
                ->title('اختر الخزينة')
                ->warning()
                ->send();

            return;
        }

        if ($this->sourceType === 'bank' && !$this->bankAccountId) {
            Notification::make()
                ->title('اختر الحساب البنكي')
                ->warning()
                ->send();

            return;
        }

        try {
            $result = DB::transaction(function () {
                $category = ExpenseCategory::query()
                    ->with('account')
                    ->lockForUpdate()
                    ->findOrFail($this->selectedCategoryId);

                if (!$category->is_active) {
                    throw new RuntimeException('تصنيف المصروف غير نشط.');
                }

                $expenseAccount = $category->account;

                if (!$expenseAccount || !$expenseAccount->is_active) {
                    throw new RuntimeException(
                        'تصنيف "' . $category->name .
                        '" غير مربوط بحساب مصروف نشط. راجع ربط تصنيفات المصروفات.'
                    );
                }

                if (!str_starts_with((string) $expenseAccount->code, '5')) {
                    throw new RuntimeException(
                        'الحساب المرتبط بتصنيف "' . $category->name .
                        '" ليس حساب مصروف (5xxx).'
                    );
                }

                /*
                 * Keep the historical Expense record because the current
                 * daily-expenses UI and reports read from the expenses table.
                 * Accounting is NOT posted here.
                 */
                $expense = Expense::create([
                    'expense_category_id' => $category->id,
                    'amount'              => round((float) $this->amount, 2),
                    'description'         => $this->description ?: $category->name,
                    'expense_date'        => today(),
                ]);

                /*
                 * Single accounting engine:
                 * PaymentPostingService creates:
                 * - PaymentVoucher
                 * - exactly one JournalEntry
                 * - TreasuryTransaction / BankTransaction
                 * - balance update
                 */
                $voucher = app(PaymentPostingService::class)->post([
                    'payment_date'       => today(),
                    'purpose'            => 'direct_expense',
                    'expense_account_id' => $expenseAccount->id,
                    'source_type'        => $this->sourceType,
                    'treasury_id'        => $this->sourceType === 'treasury'
                        ? $this->treasuryId
                        : null,
                    'bank_account_id'    => $this->sourceType === 'bank'
                        ? $this->bankAccountId
                        : null,
                    'amount'             => round((float) $this->amount, 2),
                    'payment_method'     => $this->paymentMethod,
                    'beneficiary_name'   => $category->name,
                    'reference'          => 'EXP-' . $expense->id,
                    'description'        => $expense->description,
                ]);

                /*
                 * Link the legacy Expense row to the SAME journal entry.
                 * Direct property assignment avoids depending on $fillable.
                 */
                $expense->journal_entry_id = $voucher->journal_entry_id;
                $expense->branch_id = $voucher->branch_id;
                $expense->save();

                return [
                    'expense'  => $expense,
                    'category' => $category,
                    'voucher'  => $voucher,
                ];
            });

            $this->amount = 0;
            $this->description = '';
            $this->selectedCategoryId = null;

            Notification::make()
                ->title('✅ تم تسجيل وترحيل المصروف')
                ->body(
                    $result['category']->name .
                    ' — ' .
                    number_format((float) $result['expense']->amount, 2) .
                    ' ر.س — ' .
                    $result['voucher']->voucher_number
                )
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('تعذر تسجيل المصروف')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Posted expenses must not be physically deleted.
     * Reversal/void will be added as a dedicated accounting workflow.
     */
    public function deleteExpense(int $id): void
    {
        Notification::make()
            ->title('الحذف المباشر متوقف')
            ->body('المصروف المرحّل لا يُحذف. سيتم التعامل معه بقيد عكسي عند إضافة Workflow الإلغاء.')
            ->warning()
            ->send();
    }
}
