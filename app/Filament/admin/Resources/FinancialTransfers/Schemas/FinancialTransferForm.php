<?php

namespace App\Filament\Admin\Resources\FinancialTransfers\Schemas;

use App\Models\BankAccount;
use App\Models\Treasury;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class FinancialTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات التحويل المالي')
                ->description('تحويل موحد بين الخزائن والبنوك بقيد واحد وحركتين مترابطتين.')
                ->schema([
                    DatePicker::make('transfer_date')
                        ->label('تاريخ التحويل')
                        ->required()
                        ->default(today()),

                    Select::make('from_type')
                        ->label('نوع المصدر')
                        ->options([
                            'treasury' => 'خزينة',
                            'bank' => 'حساب بنكي',
                        ])
                        ->default('treasury')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            $set('from_treasury_id', null);
                            $set('from_bank_account_id', null);
                        }),

                    Select::make('from_treasury_id')
                        ->label('خزينة المصدر')
                        ->options(fn () => Treasury::query()
                            ->where('is_active', true)
                            ->orderByDesc('is_default')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn ($treasury) => [
                                $treasury->id => $treasury->name . ' — ' .
                                    number_format((float) $treasury->current_balance, 2) . ' ر.س',
                            ]))
                        ->searchable()
                        ->required(fn (Get $get) => $get('from_type') === 'treasury')
                        ->visible(fn (Get $get) => $get('from_type') === 'treasury')
                        ->live(),

                    Select::make('from_bank_account_id')
                        ->label('الحساب البنكي المصدر')
                        ->options(fn () => BankAccount::query()
                            ->where('is_active', true)
                            ->orderBy('bank_name')
                            ->get()
                            ->mapWithKeys(fn ($bank) => [
                                $bank->id => $bank->bank_name . ' — ' .
                                    $bank->account_name . ' — ' .
                                    number_format((float) $bank->current_balance, 2) . ' ر.س',
                            ]))
                        ->searchable()
                        ->required(fn (Get $get) => $get('from_type') === 'bank')
                        ->visible(fn (Get $get) => $get('from_type') === 'bank')
                        ->live(),

                    Select::make('to_type')
                        ->label('نوع الوجهة')
                        ->options([
                            'treasury' => 'خزينة',
                            'bank' => 'حساب بنكي',
                        ])
                        ->default('treasury')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            $set('to_treasury_id', null);
                            $set('to_bank_account_id', null);
                        }),

                    Select::make('to_treasury_id')
                        ->label('خزينة الوجهة')
                        ->options(function (Get $get) {
                            $query = Treasury::query()
                                ->where('is_active', true)
                                ->orderByDesc('is_default')
                                ->orderBy('name');

                            if ($get('from_type') === 'treasury' && $get('from_treasury_id')) {
                                $query->where('id', '!=', $get('from_treasury_id'));
                            }

                            return $query->get()->mapWithKeys(fn ($treasury) => [
                                $treasury->id => $treasury->name . ' — ' .
                                    number_format((float) $treasury->current_balance, 2) . ' ر.س',
                            ]);
                        })
                        ->searchable()
                        ->required(fn (Get $get) => $get('to_type') === 'treasury')
                        ->visible(fn (Get $get) => $get('to_type') === 'treasury'),

                    Select::make('to_bank_account_id')
                        ->label('الحساب البنكي الوجهة')
                        ->options(function (Get $get) {
                            $query = BankAccount::query()
                                ->where('is_active', true)
                                ->orderBy('bank_name');

                            if ($get('from_type') === 'bank' && $get('from_bank_account_id')) {
                                $query->where('id', '!=', $get('from_bank_account_id'));
                            }

                            return $query->get()->mapWithKeys(fn ($bank) => [
                                $bank->id => $bank->bank_name . ' — ' .
                                    $bank->account_name . ' — ' .
                                    number_format((float) $bank->current_balance, 2) . ' ر.س',
                            ]);
                        })
                        ->searchable()
                        ->required(fn (Get $get) => $get('to_type') === 'bank')
                        ->visible(fn (Get $get) => $get('to_type') === 'bank'),

                    TextInput::make('amount')
                        ->label('المبلغ')
                        ->numeric()
                        ->minValue(0.01)
                        ->prefix('ر.س')
                        ->required(),

                    TextInput::make('external_reference')
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
