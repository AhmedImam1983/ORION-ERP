<?php

namespace App\Filament\Admin\Resources\JournalEntries\Schemas;

use App\Models\Account;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات القيد')
                ->description('بيانات رأس القيد والمرجع وحالة الترحيل')
                ->schema([
                    TextInput::make('entry_number')
                        ->label('رقم القيد')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default(fn () => \App\Models\JournalEntry::generateNumber()),

                    DatePicker::make('entry_date')
                        ->label('تاريخ القيد')
                        ->required()
                        ->default(now()),

                    TextInput::make('reference')
                        ->label('المرجع')
                        ->placeholder('مثال: INV-000015 أو EXP-26')
                        ->default(null),

                    Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'draft'    => 'مسودة',
                            'posted'   => 'مرحّل',
                            'reversed' => 'معكوس',
                        ])
                        ->default('draft')
                        ->required(),

                    Textarea::make('description')
                        ->label('البيان')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('سطور القيد')
                ->description('كل سطر يجب أن يحتوي مبلغاً في المدين أو الدائن فقط')
                ->schema([
                    Repeater::make('lines')
                        ->label('')
                        ->relationship()
                        ->schema([
                            Select::make('account_id')
                                ->label('الحساب')
                                ->options(
                                    Account::where('is_active', true)
                                        ->where('level', 'detail')
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn ($account) => [
                                            $account->id => ($account->code ? $account->code . ' · ' : '') . $account->name,
                                        ])
                                )
                                ->searchable()
                                ->required()
                                ->columnSpan(2),

                            TextInput::make('debit')
                                ->label('مدين')
                                ->numeric()
                                ->prefix('ر.س')
                                ->minValue(0)
                                ->default(0),

                            TextInput::make('credit')
                                ->label('دائن')
                                ->numeric()
                                ->prefix('ر.س')
                                ->minValue(0)
                                ->default(0),

                            Textarea::make('description')
                                ->label('بيان السطر')
                                ->rows(2)
                                ->default(null)
                                ->columnSpan(4),
                        ])
                        ->columns(4)
                        ->addActionLabel('إضافة سطر')
                        ->minItems(2)
                        ->reorderable()
                        ->collapsible()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set): void {
                            $rows = is_array($state) ? $state : [];

                            $debit = collect($rows)->sum(
                                fn ($row) => (float) ($row['debit'] ?? 0)
                            );

                            $credit = collect($rows)->sum(
                                fn ($row) => (float) ($row['credit'] ?? 0)
                            );

                            $set('total_debit', round($debit, 2));
                            $set('total_credit', round($credit, 2));
                        }),
                ]),

            Section::make('التحقق من التوازن')
                ->description('لا يمكن ترحيل قيد غير متوازن')
                ->schema([
                    TextInput::make('total_debit')
                        ->label('إجمالي المدين')
                        ->numeric()
                        ->prefix('ر.س')
                        ->default(0)
                        ->readOnly(),

                    TextInput::make('total_credit')
                        ->label('إجمالي الدائن')
                        ->numeric()
                        ->prefix('ر.س')
                        ->default(0)
                        ->readOnly(),
                ])
                ->columns(2),
        ]);
    }
}
