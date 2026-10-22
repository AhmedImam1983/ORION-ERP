<?php

namespace App\Filament\Admin\Resources\Warehouses\Pages;

use App\Filament\Admin\Resources\Warehouses\WarehouseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWarehouse extends EditRecord
{
    protected static string $resource = WarehouseResource::class;

    protected string $view = 'filament.admin.resources.warehouses.pages.edit-warehouse';

    public function getTitle(): string
    {
        return 'تعديل: ' . $this->getRecord()->name;
    }

    public function getBreadcrumb(): string
    {
        return 'تعديل';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('عرض المخزن')
                ->icon('heroicon-o-eye'),
            DeleteAction::make()
                ->label('حذف المخزن')
                ->icon('heroicon-o-trash'),
        ];
    }
}
