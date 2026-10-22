<?php

namespace App\Filament\Admin\Resources\ProductVehicles\Pages;

use App\Filament\Admin\Resources\ProductVehicles\ProductVehicleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditProductVehicle extends EditRecord
{
    protected static string $resource = ProductVehicleResource::class;

    public function getTitle(): string
    {
        return 'تعديل توافق قطعة';
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ التعديلات';
    }

    public function getBreadcrumb(): string
    {
        return 'تعديل';
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('حفظ');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('إلغاء');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('حذف'),
        ];
    }
}
