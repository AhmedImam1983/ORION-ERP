<?php

namespace App\Filament\Admin\Resources\Branches;

use App\Filament\Admin\Resources\Branches\Pages\CreateBranch;
use App\Filament\Admin\Resources\Branches\Pages\EditBranch;
use App\Filament\Admin\Resources\Branches\Pages\ListBranches;
use App\Filament\Admin\Resources\Branches\Schemas\BranchForm;
use App\Filament\Admin\Resources\Branches\Tables\BranchesTable;
use App\Models\Branch;
use App\Filament\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionPrefix(): string
    {
        return 'branches';
    }

    protected static ?string $model = Branch::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;
    protected static ?string $navigationLabel = 'الفروع';
    protected static ?string $modelLabel = 'فرع';
    protected static ?string $pluralModelLabel = 'الفروع';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات · Settings';
    }

    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'edit'   => EditBranch::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }
}
