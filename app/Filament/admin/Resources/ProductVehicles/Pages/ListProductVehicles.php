<?php

namespace App\Filament\Admin\Resources\ProductVehicles\Pages;

use App\Filament\Admin\Resources\ProductVehicles\ProductVehicleResource;
use App\Models\ProductVehicle;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductVehicles extends ListRecords
{
    protected static string $resource = ProductVehicleResource::class;

    protected string $view = 'filament.admin.resources.product-vehicles.pages.list-product-vehicles';

    public function getSubheading(): ?string
    {
        return 'إدارة توافق قطع الغيار مع السيارات من شاشة تشغيلية واحدة';
    }

    public function getFitmentStats(): array
    {
        $base = ProductVehicle::query();

        return [
            'total'       => (clone $base)->count(),
            'products'    => (clone $base)->distinct('product_id')->count('product_id'),
            'makes'       => (clone $base)->whereNotNull('make')->where('make', '!=', '')->distinct('make')->count('make'),
            'with_years'  => (clone $base)->where(function ($q) {
                $q->whereNotNull('year_from')->orWhereNotNull('year_to');
            })->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة توافق')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
