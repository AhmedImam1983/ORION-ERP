<?php

namespace App\Filament\Admin\Resources\Warehouses\Pages;

use App\Filament\Admin\Resources\Warehouses\WarehouseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWarehouse extends ViewRecord
{
    protected static string $resource = WarehouseResource::class;

    protected string $view = 'filament.admin.resources.warehouses.pages.view-warehouse';

    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    public function getBreadcrumb(): string
    {
        return 'عرض';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('تعديل المخزن')
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}
