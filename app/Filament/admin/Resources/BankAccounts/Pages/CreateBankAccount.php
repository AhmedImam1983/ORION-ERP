<?php

namespace App\Filament\Admin\Resources\BankAccounts\Pages;

use App\Filament\Admin\Resources\BankAccounts\BankAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBankAccount extends CreateRecord
{
    protected static string $resource = BankAccountResource::class;

    protected string $view = 'filament.admin.resources.bank-accounts.pages.create-bank-account';

    public function getTitle(): string
    {
        return 'إضافة حساب بنكي جديد';
    }

    public function getBreadcrumb(): string
    {
        return 'إضافة';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء الحساب البنكي بنجاح';
    }
}
