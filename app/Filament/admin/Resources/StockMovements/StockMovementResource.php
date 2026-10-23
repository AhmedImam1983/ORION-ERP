<?php

namespace App\Filament\Admin\Resources\StockMovements;

use App\Filament\Admin\Resources\StockMovements\Pages\CreateStockMovement;
use App\Filament\Admin\Resources\StockMovements\Pages\ListStockMovements;
use App\Filament\Admin\Resources\StockMovements\Tables\StockMovementsTable;
use App\Models\StockMovement;
use App\Filament\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockMovementResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionPrefix(): string
    {
        return 'stock_movements';
    }

    protected static ?string $model = StockMovement::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;
    protected static ?string $navigationLabel = 'حركة المخزون';
    protected static ?string $modelLabel = 'حركة';
    protected static ?string $pluralModelLabel = 'حركة المخزون';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'المخازن · Warehouse';
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Admin\Resources\StockMovements\Schemas\StockMovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockMovementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListStockMovements::route('/'),
            'create' => CreateStockMovement::route('/create'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'storekeeper'])
            || auth()->user()?->can('create_stock_movements') === true;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count() ?: null;
    }
}
