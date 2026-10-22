<?php

namespace App\Filament\Admin\Resources\Accounts\Schemas;

use App\Models\Account;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الحساب')
                ->description('الكود والاسم وموقع الحساب داخل شجرة الحسابات')
                ->schema([
                    TextInput::make('code')
                        ->label('كود الحساب')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(30),

                    TextInput::make('name')
                        ->label('اسم الحساب')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('name_en')
                        ->label('اسم الحساب بالإنجليزية')
                        ->default(null)
                        ->maxLength(255),

                    Select::make('parent_id')
                        ->label('الحساب الأب')
                        ->options(
                            Account::query()
                                ->where('is_active', true)
                                ->whereIn('level', ['main', 'sub'])
                                ->orderBy('code')
                                ->get()
                                ->mapWithKeys(fn ($account) => [
                                    $account->id => $account->code . ' · ' . $account->name,
                                ])
                        )
                        ->searchable()
                        ->preload()
                        ->placeholder('بدون حساب أب · حساب رئيسي')
                        ->helperText('عند اختيار حساب أب، سيقوم النظام بتوحيد النوع وتحديد المستوى عند الحفظ.'),

                    Select::make('type')
                        ->label('نوع الحساب')
                        ->options([
                            'asset'     => 'أصول',
                            'liability' => 'خصوم',
                            'equity'    => 'حقوق الملكية',
                            'revenue'   => 'إيرادات',
                            'expense'   => 'مصروفات',
                        ])
                        ->required(),

                    Select::make('level')
                        ->label('مستوى الحساب')
                        ->options([
                            'main'   => 'رئيسي',
                            'sub'    => 'فرعي',
                            'detail' => 'تفصيلي',
                        ])
                        ->default('detail')
                        ->required()
                        ->helperText('يُضبط تلقائياً من الحساب الأب عند الحفظ إن تم اختياره.'),
                ])
                ->columns(2),

            Section::make('البيانات المالية')
                ->description('الرصيد الافتتاحي وطبيعة الرصيد وحالة الحساب')
                ->schema([
                    TextInput::make('opening_balance')
                        ->label('الرصيد الافتتاحي')
                        ->required()
                        ->numeric()
                        ->prefix('ر.س')
                        ->default(0),

                    Select::make('balance_type')
                        ->label('طبيعة الحساب')
                        ->options([
                            'debit'  => 'مدين',
                            'credit' => 'دائن',
                        ])
                        ->default('debit')
                        ->required(),

                    Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true)
                        ->required(),

                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(3)
                        ->default(null)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
