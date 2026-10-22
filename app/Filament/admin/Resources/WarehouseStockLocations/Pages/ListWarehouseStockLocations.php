<?php

namespace App\Filament\Admin\Resources\WarehouseStockLocations\Pages;

use App\Filament\Admin\Resources\WarehouseStockLocations\WarehouseStockLocationResource;
use App\Models\WarehouseStockLocation;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseStockLocations extends ListRecords
{
    protected static string $resource = WarehouseStockLocationResource::class;

    protected string $view = 'filament.admin.resources.warehouse-stock-locations.pages.list-warehouse-stock-locations';

    public function getSubheading(): ?string
    {
        return 'إدارة مواقع الأصناف داخل المخازن للوصول السريع وتقليل أخطاء التخزين';
    }

    public function getLocationStats(): array
    {
        $base = WarehouseStockLocation::query();

        return [
            'total' => (clone $base)->count(),
            'warehouses' => (clone $base)->whereHas('warehouseStock.warehouse')->distinct('warehouse_stock_id')->count('warehouse_stock_id'),
            'stocked' => (clone $base)->whereHas('warehouseStock', fn ($q) => $q->where('quantity', '>', 0))->count(),
            'empty' => (clone $base)->whereHas('warehouseStock', fn ($q) => $q->where('quantity', '<=', 0))->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة موقع تخزين')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
