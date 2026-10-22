<?php

namespace App\Filament\Admin\Resources\AccountingReversals;

use App\Filament\Admin\Resources\AccountingReversals\Pages\ListAccountingReversals;
use App\Filament\Admin\Resources\AccountingReversals\Tables\AccountingReversalsTable;
use App\Models\AccountingReversal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountingReversalResource extends Resource
{
    protected static ?string $model = AccountingReversal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static ?string $navigationLabel = 'القيود العكسية';

    protected static ?string $modelLabel = 'قيد عكسي';

    protected static ?string $pluralModelLabel = 'القيود العكسية';

    protected static ?string $recordTitleAttribute = 'reversal_number';

    protected static ?int $navigationSort = 12;

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public static function table(Table $table): Table
    {
        return AccountingReversalsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountingReversals::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
