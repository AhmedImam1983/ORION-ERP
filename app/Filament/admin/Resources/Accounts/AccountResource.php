<?php

namespace App\Filament\Admin\Resources\Accounts;

use App\Filament\Admin\Resources\Accounts\Pages\CreateAccount;
use App\Filament\Admin\Resources\Accounts\Pages\EditAccount;
use App\Filament\Admin\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Admin\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Admin\Resources\Accounts\Schemas\AccountForm;
use App\Filament\Admin\Resources\Accounts\Schemas\AccountInfolist;
use App\Filament\Admin\Resources\Accounts\Tables\AccountsTable;
use App\Models\Account;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\HasResourcePermissions;

class AccountResource extends Resource
{
use HasResourcePermissions;
    protected static ?string $model = Account::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static ?string $navigationLabel = 'شجرة الحسابات';
    protected static ?string $modelLabel = 'حساب';
    protected static ?string $pluralModelLabel = 'الحسابات';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public static function form(Schema $schema): Schema
    {
        return AccountForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccountInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAccounts::route('/'),
            'create' => CreateAccount::route('/create'),
            'view'   => ViewAccount::route('/{record}'),
            'edit'   => EditAccount::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }
}