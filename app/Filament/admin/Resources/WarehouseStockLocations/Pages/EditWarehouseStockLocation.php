<?php

namespace App\Filament\Admin\Resources\WarehouseStockLocations\Pages;

use App\Filament\Admin\Resources\WarehouseStockLocations\WarehouseStockLocationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWarehouseStockLocation extends EditRecord
{
    protected static string $resource = WarehouseStockLocationResource::class;

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ موقع التخزين';
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label('حذف')];
    }
}
