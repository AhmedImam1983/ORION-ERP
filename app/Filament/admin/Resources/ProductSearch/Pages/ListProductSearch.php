<?php

namespace App\Filament\Admin\Resources\ProductSearch\Pages;

use App\Filament\Admin\Resources\ProductSearch\ProductSearchResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListProductSearch extends ListRecords
{
    protected static string $resource = ProductSearchResource::class;

    protected static ?string $title = 'بحث الأصناف';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('products')
                ->label('إدارة الأصناف')
                ->icon('heroicon-o-archive-box')
                ->url(fn () => \App\Filament\Admin\Resources\Products\ProductResource::getUrl('index')),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'بحث سريع في الأصناف والكود ورقم المرجع والباركود والعلامة التجارية';
    }
}
