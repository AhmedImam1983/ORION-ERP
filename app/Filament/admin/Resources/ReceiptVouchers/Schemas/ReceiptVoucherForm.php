<?php

namespace App\Filament\Admin\Resources\ReceiptVouchers\Schemas;

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

class ReceiptVoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات سند القبض')
                    ->description('تحصيل من عميل وربطه بفاتورة بيع وخزينة أو حساب بنكي.')
                    ->schema([
                        DatePicker::make('receipt_date')
                            ->label('تاريخ القبض')
                            ->required()
                            ->default(today()),

                        Select::make('customer_id')
                            ->label('العميل')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

                        Select::make('invoice_id')
                            ->label('فاتورة البيع')
                            ->options(function (Get $get) {
                                $customerId = $get('customer_id');

                                return Invoice::query()
                                    ->where('type', 'sale')
                                    ->whereIn('status', ['confirmed', 'partial'])
                                    ->where('remaining_amount', '>', 0)
                                    ->when(
                                        $customerId,
                                        fn ($query) => $query->where('customer_id', $customerId)
                                    )
                                    ->orderByDesc('invoice_date')
                                    ->limit(100)
                                    ->pluck('invoice_number', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (!$state) {
                                    return;
                                }

                                $invoice = Invoice::query()->find($state);

                                if (!$invoice) {
                                    return;
                                }

                                $set('customer_id', $invoice->customer_id);
                                $set(
                                    'amount',
                                    round((float) $invoice->remaining_amount, 2)
                                );
                            }),

                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->numeric()
                            ->minValue(0.01)
                            ->prefix('ر.س')
                            ->required(),

                        Select::make('destination_type')
                            ->label('وجهة التحصيل')
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
                                    $get('destination_type') === 'treasury'
                            )
                            ->visible(
                                fn (Get $get) =>
                                    $get('destination_type') === 'treasury'
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
                                    $get('destination_type') === 'bank'
                            )
                            ->visible(
                                fn (Get $get) =>
                                    $get('destination_type') === 'bank'
                            ),

                        Select::make('payment_method')
                            ->label('طريقة التحصيل')
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
