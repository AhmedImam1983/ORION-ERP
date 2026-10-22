<?php

namespace App\Filament\Admin\Resources\BankAccounts\Pages;

use App\Filament\Admin\Resources\BankAccounts\BankAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBankAccount extends EditRecord
{
    protected static string $resource = BankAccountResource::class;

    protected string $view = 'filament.admin.resources.bank-accounts.pages.edit-bank-account';

    public function getTitle(): string
    {
        return 'تعديل الحساب البنكي';
    }

    public function getBreadcrumb(): string
    {
        return 'تعديل';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('حذف الحساب')
                ->icon('heroicon-o-trash'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ تعديلات الحساب البنكي';
    }
}
