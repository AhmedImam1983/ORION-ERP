<?php

namespace App\Filament\Admin\Resources\FixedAssets;

use App\Filament\Admin\Resources\FixedAssets\Pages\CreateFixedAsset;
use App\Filament\Admin\Resources\FixedAssets\Pages\EditFixedAsset;
use App\Filament\Admin\Resources\FixedAssets\Pages\ListFixedAssets;
use App\Filament\Admin\Resources\FixedAssets\Schemas\FixedAssetForm;
use App\Filament\Admin\Resources\FixedAssets\Tables\FixedAssetsTable;
use App\Models\FixedAsset;
use App\Filament\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FixedAssetResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionPrefix(): string
    {
        return 'fixed_assets';
    }

    protected static ?string $model = FixedAsset::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static ?string $navigationLabel = 'الأصول الثابتة';
    protected static ?string $modelLabel = 'أصل ثابت';
    protected static ?string $pluralModelLabel = 'الأصول الثابتة';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public static function form(Schema $schema): Schema
    {
        return FixedAssetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FixedAssetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListFixedAssets::route('/'),
            'create' => CreateFixedAsset::route('/create'),
            'edit'   => EditFixedAsset::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count() ?: null;
    }
}
