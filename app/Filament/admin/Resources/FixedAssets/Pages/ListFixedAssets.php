<?php

namespace App\Filament\Admin\Resources\FixedAssets\Pages;

use App\Filament\Admin\Resources\FixedAssets\FixedAssetResource;
use App\Models\FixedAsset;
use Filament\Resources\Pages\ListRecords;

class ListFixedAssets extends ListRecords
{
    protected static string $resource = FixedAssetResource::class;

    protected string $view = 'filament.admin.resources.fixed-assets.pages.list-fixed-assets';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getFixedAssetStats(): array
    {
        $query = FixedAsset::query();

        $total = (clone $query)->count();
        $active = (clone $query)->where('status', 'active')->count();
        $purchase = (float) (clone $query)->sum('purchase_price');
        $accumulated = (float) (clone $query)->sum('accumulated_depreciation');
        $netBook = (float) (clone $query)->sum('net_book_value');

        return [
            'total' => $total,
            'active' => $active,
            'purchase' => $purchase,
            'accumulated' => $accumulated,
            'net_book' => $netBook,
            'depreciation_ratio' => $purchase > 0 ? round(($accumulated / $purchase) * 100, 1) : 0,
        ];
    }
}
