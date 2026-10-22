<?php

namespace App\Filament\Admin\Resources\ProductVehicles;

use App\Filament\Admin\Resources\ProductVehicles\Pages\CreateProductVehicle;
use App\Filament\Admin\Resources\ProductVehicles\Pages\EditProductVehicle;
use App\Filament\Admin\Resources\ProductVehicles\Pages\ListProductVehicles;
use App\Filament\Admin\Resources\ProductVehicles\Schemas\ProductVehicleForm;
use App\Filament\Admin\Resources\ProductVehicles\Tables\ProductVehiclesTable;
use App\Models\ProductVehicle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Traits\HasResourcePermissions;

class ProductVehicleResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = ProductVehicle::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;
    protected static ?string $navigationLabel = 'توافق القطع';
    protected static ?string $modelLabel = 'توافق قطعة';
    protected static ?string $pluralModelLabel = 'توافق القطع';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'CATALOG · الأصناف';
    }

    public static function form(Schema $schema): Schema
    {
        return ProductVehicleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductVehiclesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProductVehicles::route('/'),
            'create' => CreateProductVehicle::route('/create'),
            'edit'   => EditProductVehicle::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['product',]);
    }
}
