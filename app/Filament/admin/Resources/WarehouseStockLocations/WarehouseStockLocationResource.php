<?php

namespace App\Filament\Admin\Resources\WarehouseStockLocations;

use App\Filament\Admin\Resources\WarehouseStockLocations\Pages\CreateWarehouseStockLocation;
use App\Filament\Admin\Resources\WarehouseStockLocations\Pages\EditWarehouseStockLocation;
use App\Filament\Admin\Resources\WarehouseStockLocations\Pages\ListWarehouseStockLocations;
use App\Filament\Admin\Resources\WarehouseStockLocations\Schemas\WarehouseStockLocationForm;
use App\Filament\Admin\Resources\WarehouseStockLocations\Tables\WarehouseStockLocationsTable;
use App\Models\WarehouseStockLocation;
use App\Filament\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WarehouseStockLocationResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = WarehouseStockLocation::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;
    protected static ?string $navigationLabel = 'مواقع التخزين';
    protected static ?string $modelLabel = 'موقع تخزين';
    protected static ?string $pluralModelLabel = 'مواقع التخزين';
    protected static ?string $recordTitleAttribute = 'location_code';
    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return 'المخازن';
    }

    public static function form(Schema $schema): Schema
    {
        return WarehouseStockLocationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarehouseStockLocationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWarehouseStockLocations::route('/'),
            'create' => CreateWarehouseStockLocation::route('/create'),
            'edit' => EditWarehouseStockLocation::route('/{record}/edit'),
        ];
    }
}
