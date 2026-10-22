<?php

namespace App\Filament\Admin\Resources\Warehouses;

use App\Filament\Admin\Resources\Warehouses\Pages\CreateWarehouse;
use App\Filament\Admin\Resources\Warehouses\Pages\EditWarehouse;
use App\Filament\Admin\Resources\Warehouses\Pages\ListWarehouses;
use App\Filament\Admin\Resources\Warehouses\Pages\ViewWarehouse;
use App\Filament\Admin\Resources\Warehouses\RelationManagers\ProductsRelationManager;
use App\Filament\Admin\Resources\Warehouses\Schemas\WarehouseForm;
use App\Filament\Admin\Resources\Warehouses\Schemas\WarehouseInfolist;
use App\Filament\Admin\Resources\Warehouses\Tables\WarehousesTable;
use App\Filament\Traits\HasResourcePermissions;
use App\Models\Warehouse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WarehouseResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionPrefix(): string
    {
        return 'warehouses';
    }

    protected static ?string $model = Warehouse::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;
    protected static ?string $navigationLabel = 'المخازن';
    protected static ?string $modelLabel = 'مخزن';
    protected static ?string $pluralModelLabel = 'المخازن';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'المخازن · Warehouse';
    }

    public static function form(Schema $schema): Schema
    {
        return WarehouseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WarehouseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarehousesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'view'   => ViewWarehouse::route('/{record}'),
            'edit'   => EditWarehouse::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }
}
