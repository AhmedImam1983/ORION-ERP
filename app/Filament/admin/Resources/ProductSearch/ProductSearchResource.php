<?php

namespace App\Filament\Admin\Resources\ProductSearch;

use App\Filament\Admin\Resources\ProductSearch\Pages\ListProductSearch;
use App\Filament\Admin\Resources\ProductSearch\Tables\ProductSearchTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductSearchResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'بحث الأصناف';

    protected static ?string $modelLabel = 'صنف';

    protected static ?string $pluralModelLabel = 'بحث الأصناف';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'المخازن · Warehouse';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductSearch::route('/'),
        ];
    }

    public static function table(Table $table): Table
    {
        return ProductSearchTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'brand',
                'category',
                'warehouse',
                'warehouseStocks.warehouse',
                'warehouseStocks.locations',
                'vehicles',
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
}
