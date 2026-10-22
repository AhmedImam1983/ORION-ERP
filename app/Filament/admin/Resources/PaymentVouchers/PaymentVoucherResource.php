<?php

namespace App\Filament\Admin\Resources\PaymentVouchers;

use App\Filament\Admin\Resources\Concerns\HasAccountingReversalAction;
use App\Filament\Admin\Resources\PaymentVouchers\Pages\CreatePaymentVoucher;
use App\Filament\Admin\Resources\PaymentVouchers\Pages\ListPaymentVouchers;
use App\Filament\Admin\Resources\PaymentVouchers\Pages\ViewPaymentVoucher;
use App\Filament\Admin\Resources\PaymentVouchers\Schemas\PaymentVoucherForm;
use App\Filament\Admin\Resources\PaymentVouchers\Tables\PaymentVouchersTable;
use App\Models\PaymentVoucher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentVoucherResource extends Resource
{
    use HasAccountingReversalAction;

    protected static ?string $model = PaymentVoucher::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;
    protected static ?string $navigationLabel = 'سندات الصرف';
    protected static ?string $modelLabel = 'سند صرف';
    protected static ?string $pluralModelLabel = 'سندات الصرف';
    protected static ?string $recordTitleAttribute = 'voucher_number';
    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentVoucherForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentVouchersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPaymentVouchers::route('/'),
            'create' => CreatePaymentVoucher::route('/create'),
            'view'   => ViewPaymentVoucher::route('/{record}'),
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
