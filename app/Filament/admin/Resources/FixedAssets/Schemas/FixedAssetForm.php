<?php

namespace App\Filament\Admin\Resources\FixedAssets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;

class FixedAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('تعريف الأصل')
                ->description('البيانات الأساسية التي تميز الأصل ومكان استخدامه داخل المنشأة.')
                ->schema([
                    TextInput::make('code')
                        ->label('كود الأصل')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),
                    TextInput::make('name')
                        ->label('اسم الأصل')
                        ->required()
                        ->maxLength(255),
                    Select::make('category')
                        ->label('الفئة')
                        ->options([
                            'أثاث ومفروشات'   => 'أثاث ومفروشات',
                            'أجهزة كمبيوتر'   => 'أجهزة كمبيوتر',
                            'سيارات'          => 'سيارات',
                            'معدات وآلات'     => 'معدات وآلات',
                            'مباني وعقارات'   => 'مباني وعقارات',
                            'أجهزة كهربائية'  => 'أجهزة كهربائية',
                            'أخرى'            => 'أخرى',
                        ])
                        ->required(),
                    Select::make('branch_id')
                        ->label('الفرع')
                        ->relationship('branch', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('غير محدد')
                        ->default(null),
                    TextInput::make('location')
                        ->label('الموقع داخل الفرع')
                        ->placeholder('مثال: الإدارة / المستودع / الدور الثاني')
                        ->default(null),
                    TextInput::make('serial_number')
                        ->label('الرقم التسلسلي')
                        ->default(null),
                    TextInput::make('supplier')
                        ->label('المورد')
                        ->default(null),
                    Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'active'   => 'نشط',
                            'disposed' => 'تالف',
                            'sold'     => 'مباع',
                        ])
                        ->default('active')
                        ->required(),
                ])->columns(2),

            Section::make('القيمة والاستهلاك')
                ->description('بيانات الشراء وسياسة الاستهلاك والقيمة الدفترية الحالية.')
                ->schema([
                    DatePicker::make('purchase_date')
                        ->label('تاريخ الشراء')
                        ->required()
                        ->default(today())
                        ->live()
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateDepreciation($set, $get)),
                    TextInput::make('purchase_price')
                        ->label('سعر الشراء')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('ر.س')
                        ->default(0)
                        ->live()
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateDepreciation($set, $get)),
                    TextInput::make('salvage_value')
                        ->label('قيمة الخردة')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('ر.س')
                        ->default(0)
                        ->helperText('القيمة المتوقعة للأصل في نهاية عمره الافتراضي.')
                        ->live()
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateDepreciation($set, $get)),
                    TextInput::make('useful_life_years')
                        ->label('العمر الافتراضي (سنوات)')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->default(5)
                        ->live()
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateDepreciation($set, $get)),
                    Select::make('depreciation_method')
                        ->label('طريقة الاستهلاك')
                        ->options([
                            'straight_line'     => 'القسط الثابت',
                            'declining_balance' => 'القسط المتناقص',
                        ])
                        ->default('straight_line')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateDepreciation($set, $get)),
                    TextInput::make('depreciation_rate')
                        ->label('نسبة الاستهلاك السنوية')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(20)
                        ->suffix('%'),
                    TextInput::make('accumulated_depreciation')
                        ->label('مجمع الاستهلاك')
                        ->numeric()
                        ->prefix('ر.س')
                        ->default(0)
                        ->readOnly(),
                    TextInput::make('net_book_value')
                        ->label('صافي القيمة الدفترية')
                        ->numeric()
                        ->prefix('ر.س')
                        ->default(0)
                        ->readOnly(),
                    DatePicker::make('last_depreciation_date')
                        ->label('آخر تاريخ استهلاك')
                        ->readOnly(),
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->default(null)
                        ->rows(4)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    /**
     * Keep the existing Orion depreciation preview logic unchanged.
     * The actual monthly posting remains handled by the model action.
     */
    private static function calculateDepreciation(Set $set, Get $get): void
    {
        $purchasePrice  = floatval($get('purchase_price') ?? 0);
        $salvageValue   = floatval($get('salvage_value') ?? 0);
        $usefulLife     = intval($get('useful_life_years') ?? 5);
        $purchaseDate   = $get('purchase_date');

        if ($purchasePrice > 0 && $usefulLife > 0 && $purchaseDate) {
            $monthsUsed = now()->diffInMonths($purchaseDate);
            $monthlyDep = ($purchasePrice - $salvageValue) / ($usefulLife * 12);
            $accumulated = min($monthsUsed * $monthlyDep, $purchasePrice - $salvageValue);
            $netValue    = $purchasePrice - $accumulated;

            $set('accumulated_depreciation', round($accumulated, 2));
            $set('net_book_value', round($netValue, 2));
            $rate = $usefulLife > 0 ? round(100 / $usefulLife, 2) : 20;
            $set('depreciation_rate', $rate);
        } else {
            $set('net_book_value', $purchasePrice);
        }
    }
}
