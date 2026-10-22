<?php

namespace App\Filament\Admin\Resources\Customers\Pages;

use App\Filament\Admin\Resources\Customers\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected string $view = 'filament.admin.resources.customers.pages.edit-customer';

    public function getTitle(): string
    {
        return 'تعديل: ' . $this->getRecord()->name;
    }

    public function getBreadcrumb(): string
    {
        return 'تعديل';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('عرض العميل')->icon('heroicon-o-eye'),
            DeleteAction::make()->label('حذف')->icon('heroicon-o-trash'),
            ForceDeleteAction::make()->label('حذف نهائي'),
            RestoreAction::make()->label('استعادة'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث بيانات العميل بنجاح';
    }
}
