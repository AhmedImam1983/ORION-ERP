<?php

namespace App\Filament\Admin\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية')
                    ->schema([
                        TextInput::make('code')
                            ->label('كود المورد')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('name')
                            ->label('اسم المورد')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('التليفون')
                            ->tel()
                            ->default(null),
                        TextInput::make('phone2')
                            ->label('تليفون 2')
                            ->tel()
                            ->default(null),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->default(null),
                        TextInput::make('tax_number')
                            ->label('الرقم الضريبي')
                            ->default(null),
                        Textarea::make('address')
                            ->label('العنوان')
                            ->default(null)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('البيانات المالية')
                    ->schema([
                        TextInput::make('opening_balance')
                            ->label('الرصيد الافتتاحي')
                            ->required()
                            ->numeric()
                            ->prefix('ر.س')
                            ->default(0),
                        Select::make('balance_type')
                            ->label('نوع الرصيد')
                            ->options([
                                'debit'  => 'مدين',
                                'credit' => 'دائن',
                            ])
                            ->default('credit')
                            ->required(),
                    ])->columns(2),

                Section::make('البيانات البنكية')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('اسم البنك')
                            ->default(null),
                        TextInput::make('bank_account')
                            ->label('رقم الحساب')
                            ->default(null),
                    ])->columns(2),

                Section::make('ملاحظات')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->default(null)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}