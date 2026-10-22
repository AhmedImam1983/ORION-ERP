<?php

namespace App\Filament\Admin\Resources\Warehouses\Pages;

use App\Filament\Admin\Resources\Warehouses\WarehouseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouse extends CreateRecord
{
    protected static string $resource = WarehouseResource::class;

    protected string $view = 'filament.admin.resources.warehouses.pages.create-warehouse';

    public function getTitle(): string
    {
        return 'إضافة مخزن جديد';
    }

    public function getBreadcrumb(): string
    {
        return 'إضافة';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء المخزن بنجاح';
    }
}
