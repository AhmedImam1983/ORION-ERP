<?php

namespace App\Filament\Admin\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('هوية العميل')
                ->description('البيانات الأساسية ووسائل التواصل والتصنيف داخل CRM.')
                ->icon('heroicon-o-identification')
                ->schema([
                    TextInput::make('code')
                        ->label('كود العميل')
                        ->placeholder('مثال: CUS-001')
                        ->helperText('كود فريد لا يتكرر بين العملاء.')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('name')
                        ->label('اسم العميل')
                        ->placeholder('اسم الفرد أو المنشأة')
                        ->required()
                        ->maxLength(255),

                    Select::make('type')
                        ->label('نوع العميل')
                        ->options([
                            'individual' => 'فرد',
                            'company' => 'شركة',
                            'government' => 'جهة حكومية',
                        ])
                        ->default('individual')
                        ->required()
                        ->native(false),

                    Toggle::make('is_active')
                        ->label('العميل نشط')
                        ->helperText('العملاء النشطون متاحون للاختيار في العمليات اليومية.')
                        ->default(true)
                        ->required(),

                    TextInput::make('phone')
                        ->label('التليفون الرئيسي')
                        ->placeholder('05xxxxxxxx')
                        ->tel()
                        ->default(null),

                    TextInput::make('phone2')
                        ->label('تليفون إضافي')
                        ->placeholder('رقم اتصال بديل')
                        ->tel()
                        ->default(null),

                    TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->placeholder('customer@example.com')
                        ->email()
                        ->default(null),

                    TextInput::make('tax_number')
                        ->label('الرقم الضريبي')
                        ->placeholder('للشركات والمنشآت عند الحاجة')
                        ->default(null),

                    TextInput::make('national_id')
                        ->label('الرقم القومي / الهوية')
                        ->placeholder('رقم الهوية عند الحاجة')
                        ->default(null),

                    Textarea::make('address')
                        ->label('العنوان')
                        ->placeholder('المدينة، الحي، الشارع، تفاصيل العنوان...')
                        ->rows(3)
                        ->default(null)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('الشروط المالية')
                ->description('الرصيد الافتتاحي وطبيعته وحد الائتمان المسموح للعميل.')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    TextInput::make('opening_balance')
                        ->label('الرصيد الافتتاحي')
                        ->required()
                        ->numeric()
                        ->prefix('ر.س')
                        ->default(0),

                    Select::make('balance_type')
                        ->label('طبيعة الرصيد')
                        ->options([
                            'debit' => 'مدين',
                            'credit' => 'دائن',
                        ])
                        ->default('debit')
                        ->required()
                        ->native(false),

                    TextInput::make('credit_limit')
                        ->label('حد الائتمان')
                        ->helperText('الحد الائتماني المسجل للعميل داخل النظام.')
                        ->required()
                        ->numeric()
                        ->prefix('ر.س')
                        ->default(0),
                ])
                ->columns(3),

            Section::make('ملاحظات داخلية')
                ->description('معلومات اختيارية تساعد فريق المبيعات والحسابات عند التعامل مع العميل.')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Textarea::make('notes')
                        ->label('الملاحظات')
                        ->placeholder('اكتب أي ملاحظات خاصة بالعميل...')
                        ->rows(4)
                        ->default(null)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
