<?php

namespace App\Filament\Admin\Resources\FinancialTransfers;

use App\Filament\Admin\Resources\Concerns\HasAccountingReversalAction;
use App\Filament\Admin\Resources\FinancialTransfers\Pages\CreateFinancialTransfer;
use App\Filament\Admin\Resources\FinancialTransfers\Pages\ListFinancialTransfers;
use App\Filament\Admin\Resources\FinancialTransfers\Pages\ViewFinancialTransfer;
use App\Filament\Admin\Resources\FinancialTransfers\Schemas\FinancialTransferForm;
use App\Filament\Admin\Resources\FinancialTransfers\Tables\FinancialTransfersTable;
use App\Models\FinancialTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FinancialTransferResource extends Resource
{
    use HasAccountingReversalAction;

    protected static ?string $model = FinancialTransfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'التحويلات المالية';

    protected static ?string $modelLabel = 'تحويل مالي';

    protected static ?string $pluralModelLabel = 'التحويلات المالية';

    protected static ?string $recordTitleAttribute = 'transfer_number';

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public static function form(Schema $schema): Schema
    {
        return FinancialTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialTransfersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListFinancialTransfers::route('/'),
            'create' => CreateFinancialTransfer::route('/create'),

            // Step 6.1: read-only details page.
            'view'   => ViewFinancialTransfer::route('/{record}'),
        ];
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
