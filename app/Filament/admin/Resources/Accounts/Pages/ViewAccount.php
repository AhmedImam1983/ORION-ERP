<?php

namespace App\Filament\Admin\Resources\Accounts\Pages;

use App\Filament\Admin\Resources\Accounts\AccountResource;
use App\Models\Account;
use App\Models\JournalEntryLine;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;
    protected string $view = 'filament.admin.resources.accounts.pages.view-account';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('تعديل الحساب'),
        ];
    }

    public function getLedgerStats(): array
    {
        $record = $this->getRecord();

        $lines = JournalEntryLine::query()
            ->where('account_id', $record->id)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'));

        $debit = (float) (clone $lines)->sum('debit');
        $credit = (float) (clone $lines)->sum('credit');

        $openingSigned = $record->balance_type === 'credit'
            ? -1 * (float) $record->opening_balance
            : (float) $record->opening_balance;

        $signedBalance = $openingSigned + $debit - $credit;

        return [
            'debit' => $debit,
            'credit' => $credit,
            'signed_balance' => $signedBalance,
            'balance' => abs($signedBalance),
            'balance_side' => $signedBalance >= 0 ? 'مدين' : 'دائن',
            'lines_count' => (clone $lines)->count(),
            'children_count' => Account::where('parent_id', $record->id)->count(),
        ];
    }

    public function getRecentLedgerLines()
    {
        return JournalEntryLine::query()
            ->with(['journalEntry'])
            ->where('account_id', $this->getRecord()->id)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->latest('id')
            ->limit(12)
            ->get();
    }
}
