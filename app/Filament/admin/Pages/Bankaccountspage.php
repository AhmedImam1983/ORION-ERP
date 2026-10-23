<?php

namespace App\Filament\Admin\Pages;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Services\FinancialTransferPostingService;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class BankAccountsPage extends Page
{

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'accountant']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'البنوك';
    protected static ?string $title = 'إدارة الحسابات البنكية';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.admin.pages.bank-accounts-page';

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    // Modal states
    public bool $showDeposit    = false;
    public bool $showWithdraw   = false;
    public bool $showTransfer   = false;
    public ?int $selectedBankId = null;
    public float $txAmount      = 0;
    public string $txDesc       = '';
    public string $txRef        = '';
    public ?int $transferToId   = null;
    public ?int $viewTxBankId   = null;

    public function getBankAccounts()
    {
        return BankAccount::with('branch')->where('is_active', true)->get();
    }

    public function getTotalBalance(): float
    {
        return BankAccount::where('is_active', true)->sum('current_balance');
    }

    public function getTransactions()
    {
        if (!$this->viewTxBankId) return collect();
        if (!BankAccount::query()->whereKey($this->viewTxBankId)->exists()) return collect();
        return BankTransaction::with('createdBy', 'fromAccount', 'toAccount')
            ->where('bank_account_id', $this->viewTxBankId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
    }

    public function openDeposit(int $id): void
    {
        $this->selectedBankId = $id;

        Notification::make()
            ->title('الإيداع المباشر متوقف')
            ->body('استخدم التحويلات المالية لنقل النقد من خزينة إلى بنك؛ حتى يكون الرصيد والحركة والقيد في مسار واحد موحد.')
            ->warning()
            ->send();
    }

    public function openWithdraw(int $id): void
    {
        $this->selectedBankId = $id;

        Notification::make()
            ->title('السحب المباشر متوقف')
            ->body('استخدم التحويلات المالية لنقل المبلغ من البنك إلى خزينة؛ حتى يكون الرصيد والحركة والقيد في مسار واحد موحد.')
            ->warning()
            ->send();
    }

    public function openTransfer(int $id): void
    {
        if (!BankAccount::query()->whereKey($id)->exists()) {
            Notification::make()->title('الحساب البنكي غير متاح لحسابك.')->danger()->send();
            return;
        }

        $this->selectedBankId = $id;
        $this->txAmount = 0;
        $this->txDesc = '';
        $this->transferToId = null;
        $this->showTransfer = true;
    }

    public function doDeposit(): void
    {
        $this->showDeposit = false;

        Notification::make()
            ->title('الإيداع المباشر متوقف')
            ->body('استخدم صفحة التحويلات المالية بدل BankAccount::deposit().')
            ->warning()
            ->send();
    }

    public function doWithdraw(): void
    {
        $this->showWithdraw = false;

        Notification::make()
            ->title('السحب المباشر متوقف')
            ->body('استخدم صفحة التحويلات المالية بدل BankAccount::withdraw().')
            ->warning()
            ->send();
    }

    public function doTransfer(): void
    {
        if (!$this->selectedBankId || !$this->transferToId || $this->txAmount <= 0) {
            Notification::make()->title('أدخل البيانات كاملة')->warning()->send();
            return;
        }

        if ((int) $this->selectedBankId === (int) $this->transferToId) {
            Notification::make()->title('لا يمكن التحويل لنفس الحساب البنكي')->warning()->send();
            return;
        }

        try {
            $transfer = app(FinancialTransferPostingService::class)->post([
                'transfer_date'        => today(),
                'from_type'            => 'bank',
                'from_bank_account_id' => $this->selectedBankId,
                'to_type'              => 'bank',
                'to_bank_account_id'   => $this->transferToId,
                'amount'               => $this->txAmount,
                'description'          => $this->txDesc,
            ]);

            $this->showTransfer = false;

            Notification::make()
                ->title('✅ تم التحويل والترحيل بنجاح')
                ->body(
                    $transfer->transfer_number .
                    ' — ' .
                    number_format((float) $transfer->amount, 2) .
                    ' ر.س'
                )
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('تعذر التحويل')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function toggleTransactions(int $id): void
    {
        if (!BankAccount::query()->whereKey($id)->exists()) {
            Notification::make()->title('الحساب البنكي غير متاح لحسابك.')->danger()->send();
            return;
        }

        $this->viewTxBankId = $this->viewTxBankId === $id ? null : $id;
    }
}