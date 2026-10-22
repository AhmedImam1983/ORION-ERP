<?php

namespace App\Filament\Admin\Resources\Suppliers\Pages;

use App\Filament\Admin\Resources\Suppliers\SupplierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected string $view = 'filament.admin.resources.suppliers.pages.edit-supplier';

    public function getSubheading(): ?string
    {
        return 'راجع بيانات المورد والرصيد الافتتاحي وبيانات التواصل والبنك';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('عرض'),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث بيانات المورد بنجاح';
    }
}
