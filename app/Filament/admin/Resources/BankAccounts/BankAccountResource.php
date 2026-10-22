<?php

namespace App\Filament\Admin\Resources\BankAccounts;

use App\Filament\Admin\Resources\BankAccounts\Pages\CreateBankAccount;
use App\Filament\Admin\Resources\BankAccounts\Pages\EditBankAccount;
use App\Filament\Admin\Resources\BankAccounts\Pages\ListBankAccounts;
use App\Filament\Admin\Resources\BankAccounts\Schemas\BankAccountForm;
use App\Filament\Admin\Resources\BankAccounts\Tables\BankAccountsTable;
use App\Models\BankAccount;
use App\Filament\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BankAccountResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionPrefix(): string
    {
        return 'bank_accounts';
    }
    protected static ?string $model = BankAccount::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;
    protected static ?string $navigationLabel = 'إدارة البنوك';
    protected static ?string $modelLabel = 'حساب بنكي';
    protected static ?string $pluralModelLabel = 'الحسابات البنكية';
    protected static ?string $recordTitleAttribute = 'bank_name';
    protected static ?int $navigationSort = 4;

    // Keep the CRUD resource available for create/edit URLs,
    // but hide the duplicate "إدارة البنوك" item from the sidebar.
    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public static function form(Schema $schema): Schema
    {
        return BankAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBankAccounts::route('/'),
            'create' => CreateBankAccount::route('/create'),
            'edit'   => EditBankAccount::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count() ?: null;
    }
}