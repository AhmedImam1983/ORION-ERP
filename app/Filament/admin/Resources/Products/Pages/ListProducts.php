<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.admin.resources.products.pages.list-products';

    public function getSubheading(): ?string
    {
        return 'إدارة الأصناف والمخزون والأسعار والباركود من شاشة تشغيلية واحدة';
    }

    public function getProductStats(): array
    {
        $base = Product::query();

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('is_active', true)->count(),
            'inactive' => (clone $base)->where('is_active', false)->count(),
            'low_stock' => (clone $base)
                ->where('is_active', true)
                ->whereColumn('quantity', '<=', 'min_quantity')
                ->count(),
            'without_warehouse' => (clone $base)
                ->where('is_active', true)
                ->whereDoesntHave('warehouseStocks')
                ->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة صنف جديد')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}