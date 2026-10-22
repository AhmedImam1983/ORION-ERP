<?php

namespace App\Filament\Admin\Resources\ProductVehicles\Pages;

use App\Filament\Admin\Resources\ProductVehicles\ProductVehicleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateProductVehicle extends CreateRecord
{
    protected static string $resource = ProductVehicleResource::class;

    public function getTitle(): string
    {
        return 'إضافة توافق قطعة';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة توافق القطعة بنجاح';
    }

    public function getBreadcrumb(): string
    {
        return 'إضافة';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('حفظ');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('حفظ وإضافة أخرى');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('إلغاء');
    }
}
