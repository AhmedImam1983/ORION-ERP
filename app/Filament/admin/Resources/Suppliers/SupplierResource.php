<?php

namespace App\Filament\Admin\Resources\Suppliers;

use App\Filament\Admin\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Admin\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Admin\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Admin\Resources\Suppliers\Pages\ViewSupplier;
use App\Filament\Admin\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\Admin\Resources\Suppliers\Schemas\SupplierInfolist;
use App\Filament\Admin\Resources\Suppliers\Tables\SuppliersTable;
use App\Models\Supplier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\HasResourcePermissions;

class SupplierResource extends Resource
{
     use HasResourcePermissions;
    protected static ?string $model = Supplier::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;
    protected static ?string $navigationLabel = 'الموردين';
    protected static ?string $modelLabel = 'مورد';
    protected static ?string $pluralModelLabel = 'الموردين';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }
public static function getNavigationGroup(): ?string
{
    return 'العملاء والموردين · CRM';
}

    public static function infolist(Schema $schema): Schema
    {
        return SupplierInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuppliersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'view'   => ViewSupplier::route('/{record}'),
            'edit'   => EditSupplier::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}