<?php

namespace App\Filament\Admin\Resources\ReceiptVouchers;

use App\Filament\Admin\Resources\Concerns\HasAccountingReversalAction;
use App\Filament\Admin\Resources\ReceiptVouchers\Pages\CreateReceiptVoucher;
use App\Filament\Admin\Resources\ReceiptVouchers\Pages\ListReceiptVouchers;
use App\Filament\Admin\Resources\ReceiptVouchers\Pages\ViewReceiptVoucher;
use App\Filament\Admin\Resources\ReceiptVouchers\Schemas\ReceiptVoucherForm;
use App\Filament\Admin\Resources\ReceiptVouchers\Tables\ReceiptVouchersTable;
use App\Models\ReceiptVoucher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReceiptVoucherResource extends Resource
{
    use HasAccountingReversalAction;

    protected static ?string $model = ReceiptVoucher::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static ?string $navigationLabel = 'سندات القبض';
    protected static ?string $modelLabel = 'سند قبض';
    protected static ?string $pluralModelLabel = 'سندات القبض';
    protected static ?string $recordTitleAttribute = 'voucher_number';
    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public static function form(Schema $schema): Schema
    {
        return ReceiptVoucherForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceiptVouchersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListReceiptVouchers::route('/'),
            'create' => CreateReceiptVoucher::route('/create'),
            'view'   => ViewReceiptVoucher::route('/{record}'),
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
