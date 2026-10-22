<?php

namespace App\Filament\Admin\Resources\Salaries;

use App\Filament\Admin\Resources\Salaries\Pages\CreateSalary;
use App\Filament\Admin\Resources\Salaries\Pages\EditSalary;
use App\Filament\Admin\Resources\Salaries\Pages\ListSalaries;
use App\Filament\Admin\Resources\Salaries\Schemas\SalaryForm;
use App\Filament\Admin\Resources\Salaries\Tables\SalariesTable;
use App\Models\Salary;
use App\Filament\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalaryResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionPrefix(): string
    {
        return 'salaries';
    }
    protected static ?string $model = Salary::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static ?string $navigationLabel = 'الرواتب';
    protected static ?string $modelLabel = 'راتب';
    protected static ?string $pluralModelLabel = 'الرواتب';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'الموارد البشرية · HR';
    }

    public static function form(Schema $schema): Schema
    {
        return SalaryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalariesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSalaries::route('/'),
            'create' => CreateSalary::route('/create'),
            'edit'   => EditSalary::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'draft')
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->count();
        return $count ?: null;
    }
}