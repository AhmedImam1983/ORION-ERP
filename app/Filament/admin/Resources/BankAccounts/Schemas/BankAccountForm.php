<?php

namespace App\Filament\Admin\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الحساب البنكي')
                ->description('بيانات تعريف البنك والحساب ومعلومات التحويل البنكي.')
                ->icon('heroicon-o-building-library')
                ->schema([
                    TextInput::make('bank_name')
                        ->label('اسم البنك')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('مثال: البنك الأهلي'),

                    TextInput::make('account_name')
                        ->label('اسم صاحب الحساب')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Orion ERP'),

                    TextInput::make('account_number')
                        ->label('رقم الحساب')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('iban')
                        ->label('IBAN')
                        ->nullable()
                        ->maxLength(255)
                        ->placeholder('EG000000000000000000000000'),

                    TextInput::make('swift_code')
                        ->label('Swift Code')
                        ->nullable()
                        ->maxLength(100),

                    TextInput::make('currency')
                        ->label('العملة')
                        ->required()
                        ->default('SAR')
                        ->maxLength(10)
                        ->helperText('اكتب رمز العملة مثل SAR أو EGP أو USD.'),
                ])->columns(2),

            Section::make('الربط المالي والتنظيمي')
                ->description('اربط الحساب البنكي بشجرة الحسابات والفرع المسؤول.')
                ->icon('heroicon-o-link')
                ->schema([
                    Select::make('account_id')
                        ->label('الحساب المحاسبي')
                        ->relationship('account', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => \App\Models\Account::where('code', '1110')->first()?->id)
                        ->helperText('اختر حساب البنك المقابل من شجرة الحسابات.')
                        ->required(),

                    Select::make('branch_id')
                        ->label('الفرع')
                        ->relationship('branch', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->placeholder('كل الفروع')
                        ->helperText('اتركه فارغًا إذا كان الحساب مركزيًا لجميع الفروع.'),
                ])->columns(2),

            Section::make('الأرصدة والتحكم')
                ->description('الرصيد الحالي للعرض والرقابة، ويتم تحديثه آليًا مع الحركات.')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    TextInput::make('opening_balance')
                        ->label('الرصيد الافتتاحي')
                        ->numeric()
                        ->default(0)
                        ->prefix('ر.س'),

                    TextInput::make('current_balance')
                        ->label('الرصيد الحالي')
                        ->numeric()
                        ->default(0)
                        ->prefix('ر.س')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('يتحدث تلقائيًا مع كل حركة بنكية.'),

                    Toggle::make('is_active')
                        ->label('الحساب نشط')
                        ->helperText('الحسابات غير النشطة تظل محفوظة لأغراض المراجعة.')
                        ->default(true),

                    Toggle::make('is_default')
                        ->label('الحساب الافتراضي')
                        ->helperText('يُستخدم كاختيار افتراضي في العمليات التي تدعم ذلك.')
                        ->default(false),

                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->nullable()
                        ->rows(4)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }
}
