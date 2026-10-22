<?php

namespace App\Filament\Admin\Resources\Warehouses\Pages;

use App\Filament\Admin\Resources\Warehouses\WarehouseResource;
use App\Models\Warehouse;
use Filament\Resources\Pages\ListRecords;

class ListWarehouses extends ListRecords
{
    protected static string $resource = WarehouseResource::class;

    protected string $view = 'filament.admin.resources.warehouses.pages.list-warehouses';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getWarehouseStats(): array
    {
        $warehouses = Warehouse::query()
            ->withCount('stocks')
            ->get();

        $defaultWarehouse = $warehouses->firstWhere('is_default', true);

        return [
            'total' => $warehouses->count(),
            'active' => $warehouses->where('is_active', true)->count(),
            'products' => (int) $warehouses->sum('stocks_count'),
            'default_name' => $defaultWarehouse?->name ?? 'غير محدد',
        ];
    }
}
