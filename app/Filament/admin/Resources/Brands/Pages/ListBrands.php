<?php

namespace App\Filament\Admin\Resources\Brands\Pages;

use App\Filament\Admin\Resources\Brands\BrandResource;
use App\Models\Brand;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBrands extends ListRecords
{
    protected static string $resource = BrandResource::class;

    protected string $view = 'filament.admin.resources.brands.pages.list-brands';

    public function getSubheading(): ?string
    {
        return 'إدارة العلامات التجارية وربطها بالأصناف من شاشة تشغيلية واحدة';
    }

    public function getBrandStats(): array
    {
        $base = Brand::query();

        return [
            'total'        => (clone $base)->count(),
            'active'       => (clone $base)->where('is_active', true)->count(),
            'aftermarket'  => (clone $base)->where('type', 'aftermarket')->count(),
            'with_products'=> (clone $base)->has('products')->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة علامة تجارية')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
