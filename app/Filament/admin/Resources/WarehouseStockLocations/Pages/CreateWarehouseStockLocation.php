<?php

namespace App\Filament\Admin\Resources\WarehouseStockLocations\Pages;

use App\Filament\Admin\Resources\WarehouseStockLocations\WarehouseStockLocationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouseStockLocation extends CreateRecord
{
    protected static string $resource = WarehouseStockLocationResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة موقع التخزين بنجاح';
    }
}
