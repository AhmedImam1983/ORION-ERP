<?php

namespace App\Filament\Admin\Resources\Suppliers\Pages;

use App\Filament\Admin\Resources\Suppliers\SupplierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;

    protected string $view = 'filament.admin.resources.suppliers.pages.create-supplier';

    public function getSubheading(): ?string
    {
        return 'أضف البيانات الأساسية والمالية والبنكية للمورد بشكل منظم';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة المورد بنجاح';
    }
}
