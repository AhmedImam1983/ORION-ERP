<?php

namespace App\Filament\Admin\Resources\PaymentVouchers\Schemas;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\Treasury;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PaymentVoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات سند الصرف')
                    ->description('سداد مورد أو تسجيل مصروف مباشر من خزينة أو حساب بنكي.')
                    ->schema([
                        DatePicker::make('payment_date')
                            ->label('تاريخ الصرف')
                            ->required()
                            ->default(today()),

                        Select::make('purpose')
                            ->label('غرض الصرف')
                            ->options([
                                'supplier_payment' => 'سداد مورد',
                                'direct_expense'   => 'مصروف مباشر',
                            ])
                            ->default('supplier_payment')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('supplier_id', null);
                                $set('invoice_id', null);
                                $set('expense_account_id', null);
                                $set('amount', null);
                            }),

                        Select::make('supplier_id')
                            ->label('المورد')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(
                                fn (Get $get) =>
                                    $get('purpose') === 'supplier_payment'
                            )
                            ->visible(
                                fn (Get $get) =>
                                    $get('purpose') === 'supplier_payment'
                            )
                            ->live(),

                        Select::make('invoice_id')
                            ->label('فاتورة الشراء')
                            ->options(function (Get $get) {
                                $supplierId = $get('supplier_id');

                                return Invoice::query()
                                    ->where('type', 'purchase')
                                    ->whereIn('status', ['confirmed', 'partial'])
                                    ->where('remaining_amount', '>', 0)
                                    ->when(
                                        $supplierId,
                                        fn ($query) =>
                                            $query->where('supplier_id', $supplierId)
                                    )
                                    ->orderByDesc('invoice_date')
                                    ->limit(100)
                                    ->pluck('invoice_number', 'id');
                            })
                            ->searchable()
                            ->required(
                                fn (Get $get) =>
                                    $get('purpose') === 'supplier_payment'
                            )
                            ->visible(
                                fn (Get $get) =>
                                    $get('purpose') === 'supplier_payment'
                            )
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (!$state) {
                                    return;
                                }

                                $invoice = Invoice::query()->find($state);

                                if (!$invoice) {
                                    return;
                                }

                                $set('supplier_id', $invoice->supplier_id);
                                $set(
                                    'amount',
                                    round((float) $invoice->remaining_amount, 2)
                                );
                            }),

                        Select::make('expense_account_id')
                            ->label('حساب المصروف')
                            ->options(
                                fn () => Account::query()
                                    ->where('is_active', true)
                                    ->where('level', 'detail')
                                    ->where('code', 'like', '5%')
                                    ->orderBy('code')
                                    ->get()
                                    ->mapWithKeys(fn ($account) => [
                                        $account->id =>
                                            $account->code . ' — ' . $account->name,
                                    ])
                            )
                            ->searchable()
                            ->required(
                                fn (Get $get) =>
                                    $get('purpose') === 'direct_expense'
                            )
                            ->visible(
                                fn (Get $get) =>
                                    $get('purpose') === 'direct_expense'
                            ),

                        TextInput::make('beneficiary_name')
                            ->label('المستفيد')
                            ->maxLength(255)
                            ->visible(
                                fn (Get $get) =>
                                    $get('purpose') === 'direct_expense'
                            ),

                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->numeric()
                            ->minValue(0.01)
                            ->prefix('ر.س')
                            ->required(),

                        Select::make('source_type')
                            ->label('مصدر الصرف')
                            ->options([
                                'treasury' => 'خزينة',
                                'bank'     => 'حساب بنكي',
                            ])
                            ->default('treasury')
                            ->required()
                            ->live(),

                        Select::make('treasury_id')
                            ->label('الخزينة')
                            ->options(
                                fn () => Treasury::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->required(
                                fn (Get $get) =>
                                    $get('source_type') === 'treasury'
                            )
                            ->visible(
                                fn (Get $get) =>
                                    $get('source_type') === 'treasury'
                            ),

                        Select::make('bank_account_id')
                            ->label('الحساب البنكي')
                            ->options(
                                fn () => BankAccount::query()
                                    ->where('is_active', true)
                                    ->orderBy('bank_name')
                                    ->get()
                                    ->mapWithKeys(fn ($bank) => [
                                        $bank->id =>
                                            $bank->bank_name .
                                            ' — ' .
                                            $bank->account_name,
                                    ])
                            )
                            ->searchable()
                            ->required(
                                fn (Get $get) =>
                                    $get('source_type') === 'bank'
                            )
                            ->visible(
                                fn (Get $get) =>
                                    $get('source_type') === 'bank'
                            ),

                        Select::make('payment_method')
                            ->label('طريقة الصرف')
                            ->options([
                                'cash'          => 'نقدي',
                                'bank_transfer' => 'تحويل بنكي',
                                'card'          => 'بطاقة',
                                'cheque'        => 'شيك',
                            ])
                            ->default('cash')
                            ->required(),

                        TextInput::make('reference')
                            ->label('مرجع خارجي / رقم عملية')
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('البيان / ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
