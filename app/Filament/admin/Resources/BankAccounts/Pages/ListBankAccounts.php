<?php

namespace App\Filament\Admin\Resources\BankAccounts\Pages;

use App\Filament\Admin\Resources\BankAccounts\BankAccountResource;
use App\Models\BankAccount;
use Filament\Resources\Pages\ListRecords;

class ListBankAccounts extends ListRecords
{
    protected static string $resource = BankAccountResource::class;

    protected string $view = 'filament.admin.resources.bank-accounts.pages.list-bank-accounts';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getBankStats(): array
    {
        $query = BankAccount::query();

        $total = (clone $query)->count();
        $active = (clone $query)->where('is_active', true)->count();
        $balance = (float) (clone $query)->sum('current_balance');
        $opening = (float) (clone $query)->sum('opening_balance');
        $default = (clone $query)->where('is_default', true)->first();

        return [
            'total' => $total,
            'active' => $active,
            'balance' => $balance,
            'opening' => $opening,
            'movement' => $balance - $opening,
            'default_name' => $default?->bank_name ?: 'غير محدد',
            'default_account' => $default?->account_number ?: '—',
            'default_balance' => (float) ($default?->current_balance ?? 0),
            'default_currency' => $default?->currency ?: 'SAR',
        ];
    }
}
