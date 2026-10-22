<?php

namespace App\Filament\Admin\Resources\JournalEntries;

use App\Filament\Admin\Resources\JournalEntries\Pages\CreateJournalEntry;
use App\Filament\Admin\Resources\JournalEntries\Pages\EditJournalEntry;
use App\Filament\Admin\Resources\JournalEntries\Pages\ListJournalEntries;
use App\Filament\Admin\Resources\JournalEntries\Pages\ViewJournalEntry;
use App\Filament\Admin\Resources\JournalEntries\Schemas\JournalEntryForm;
use App\Filament\Admin\Resources\JournalEntries\Schemas\JournalEntryInfolist;
use App\Filament\Admin\Resources\JournalEntries\Tables\JournalEntriesTable;
use App\Filament\Traits\HasResourcePermissions;
use App\Models\JournalEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JournalEntryResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = JournalEntry::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;
    protected static ?string $navigationLabel = 'القيود اليومية';
    protected static ?string $modelLabel = 'قيد';
    protected static ?string $pluralModelLabel = 'القيود اليومية';
    protected static ?string $recordTitleAttribute = 'entry_number';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public static function form(Schema $schema): Schema
    {
        return JournalEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JournalEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JournalEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListJournalEntries::route('/'),
            'create' => CreateJournalEntry::route('/create'),
            'view'   => ViewJournalEntry::route('/{record}'),
            'edit'   => EditJournalEntry::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('entry_date', today())->count() ?: null;
    }
}
