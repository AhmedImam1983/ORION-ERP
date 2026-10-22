<?php

namespace App\Filament\Admin\Resources\Suppliers\Pages;

use App\Filament\Admin\Resources\Suppliers\SupplierResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSupplier extends ViewRecord
{
    protected static string $resource = SupplierResource::class;

    protected string $view = 'filament.admin.resources.suppliers.pages.view-supplier';

    public function getSubheading(): ?string
    {
        return 'ملف المورد وبيانات التواصل والرصيد الافتتاحي والبيانات البنكية';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('تعديل المورد'),
        ];
    }
}
