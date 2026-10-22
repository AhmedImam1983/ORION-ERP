<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use App\Models\Warehouse;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('البيانات الأساسية')->schema([
                TextInput::make('code')
                    ->label('كود الصنف')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('name')
                    ->label('اسم الصنف')
                    ->required()
                    ->maxLength(255),
                TextInput::make('name_en')
                    ->label('اسم الصنف (إنجليزي)')
                    ->default(null)
                    ->maxLength(255),
                Select::make('brand_id')
                    ->label('العلامة التجارية')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('part_number')
                    ->label('رقم المرجع')
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Set $set): void {
                        $normalized = preg_replace('/[^\pL\pN]+/u', '', (string) $state);
                        $set('part_number_normalized', $normalized ?: null);
                    }),
                TextInput::make('part_number_normalized')
                    ->label('رقم المرجع الموحّد')
                    ->maxLength(100)
                    ->readOnly()
                    ->dehydrated(),
                Select::make('category_id')
                    ->label('المجموعة')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null),
                TextInput::make('unit')
                    ->label('وحدة القياس')
                    ->required()
                    ->default('قطعة'),
                TextInput::make('barcode')
                    ->label('الباركود')
                    ->default(null),
                Select::make('warehouse_id')
                    ->label('المخزن الافتراضي')
                    ->options(fn () => Warehouse::query()
                        ->where('is_active', true)
                        ->orderByDesc('is_default')
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->default(fn () => Warehouse::getDefault()?->id)
                    ->required()
                    ->helperText('هذا هو المخزن الافتراضي للصنف. تغيير الحقل لاحقاً لا ينقل الرصيد بين المخازن.'),
                TextInput::make('location_code')
                    ->label('موقع الصنف في المخزن')
                    ->maxLength(100)
                    ->dehydrated(false)
                    ->afterStateHydrated(function (TextInput $component, ?Product $record): void {
                        if (!$record) {
                            return;
                        }

                        $location = $record->warehouseStocks()
                            ->with('locations')
                            ->get()
                            ->flatMap(fn ($stock) => $stock->locations)
                            ->first()?->location_code;

                        $component->state($location);
                    })
                    ->helperText('مثال: R-04 / A-12 / درج 3'),
            ])->columns(2),

            Section::make('الأسعار والمخزون')->schema([
                TextInput::make('cost_price')
                    ->label('سعر التكلفة')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('ر.س'),
                TextInput::make('sale_price')
                    ->label('سعر البيع')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('ر.س'),
                TextInput::make('sale_price_2')
                    ->label('سعر البيع 2')
                    ->numeric()
                    ->default(0)
                    ->prefix('ر.س'),
                TextInput::make('sale_price_3')
                    ->label('سعر البيع 3')
                    ->numeric()
                    ->default(0)
                    ->prefix('ر.س'),
                TextInput::make('quantity')
                    ->label('الرصيد الافتتاحي')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->disabledOn('edit')
                    ->helperText('عند إنشاء الصنف فقط: يُرحّل هذا الرصيد إلى المخزن الافتراضي. بعد الإنشاء تتغير الكمية من الشراء/البيع/المرتجعات/التحويل والجرد.'),
                TextInput::make('min_quantity')
                    ->label('الحد الأدنى للمخزون')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('سيظهر تنبيه عند الوصول لهذا الحد'),
            ])->columns(2),

            Section::make('بيانات الضريبة (ZATCA)')
                ->description('تحدد هذه البيانات كيفية حساب الضريبة عند إضافة الصنف للفاتورة')
                ->schema([
                    Select::make('vat_type')
                        ->label('نوع الضريبة')
                        ->options([
                            'S' => 'S — خاضع للضريبة القياسية (Standard)',
                            'Z' => 'Z — نسبة صفرية (Zero Rated)',
                            'E' => 'E — معفى من الضريبة (Exempt)',
                            'O' => 'O — خارج نطاق الضريبة (Out of Scope)',
                        ])
                        ->default('S')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            // لو النوع مش S نصفر النسبة تلقائياً كبداية
                            if ($state !== 'S') {
                                $set('vat_percent', 0);
                            } else {
                                $set('vat_percent', 15);
                            }
                        })
                        ->helperText('S = 15% قياسي · Z/E/O = بدون ضريبة'),

                    TextInput::make('vat_percent')
                        ->label('نسبة الضريبة %')
                        ->numeric()
                        ->default(15)
                        ->suffix('%')
                        ->required(),

                    Select::make('vat_exemption_reason_code')
                        ->label('كود سبب الإعفاء')
                        ->options([
                            'VATEX-SA-29'    => 'VATEX-SA-29 — الخدمات المالية المعفاة',
                            'VATEX-SA-29-7'  => 'VATEX-SA-29-7 — التأمين على الحياة',
                            'VATEX-SA-30'    => 'VATEX-SA-30 — استيراد بعض السلع',
                            'VATEX-SA-32'    => 'VATEX-SA-32 — الأدوية والمعدات الطبية',
                            'VATEX-SA-33'    => 'VATEX-SA-33 — المعادن الاستثمارية',
                            'VATEX-SA-34-1'  => 'VATEX-SA-34-1 — النقل الدولي للركاب',
                            'VATEX-SA-34-2'  => 'VATEX-SA-34-2 — النقل الدولي للبضائع',
                            'VATEX-SA-34-3'  => 'VATEX-SA-34-3 — الخدمات الجوية',
                            'VATEX-SA-34-4'  => 'VATEX-SA-34-4 — وسائل النقل الدولية',
                            'VATEX-SA-34-5'  => 'VATEX-SA-34-5 — الذهب والفضة والبلاتين',
                            'VATEX-SA-35'    => 'VATEX-SA-35 — قطاع العقارات السكنية',
                            'VATEX-SA-36'    => 'VATEX-SA-36 — قطاع الخدمات المالية',
                            'VATEX-SA-EDU'   => 'VATEX-SA-EDU — الخدمات التعليمية',
                            'VATEX-SA-HEA'   => 'VATEX-SA-HEA — الخدمات الصحية',
                            'VATEX-SA-MLTRY' => 'VATEX-SA-MLTRY — السلع العسكرية',
                            'VATEX-SA-OOS'   => 'VATEX-SA-OOS — خارج نطاق الضريبة',
                            'VATEX-SA-32-EXP-GOODS' => 'VATEX-SA-32-EXP-GOODS — تصدير السلع',
                            'VATEX-SA-33-EXP-SERV'  => 'VATEX-SA-33-EXP-SERV — تصدير الخدمات',
                        ])
                        ->searchable()
                        // إلزامي فقط عند الإعفاء (E) أو خارج النطاق (O)
                        // النسبة الصفرية (Z) تُعامل حسب نوع السلعة، غالباً بدون سبب لغير التصدير
                        ->required(fn(Get $get) => in_array($get('vat_type'), ['E', 'O']))
                        ->visible(fn(Get $get) => in_array($get('vat_type'), ['E', 'O', 'Z']))
                        ->helperText('إلزامي عند الإعفاء (E) أو خارج النطاق (O)'),

                    TextInput::make('vat_exemption_reason')
                        ->label('نص سبب الإعفاء')
                        ->required(fn(Get $get) => in_array($get('vat_type'), ['E', 'O']))
                        ->visible(fn(Get $get) => in_array($get('vat_type'), ['E', 'O', 'Z']))
                        ->maxLength(500),
                ])->columns(2),

            Section::make('بيانات إضافية')->schema([
                Textarea::make('description')
                    ->label('الوصف')
                    ->default(null)
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->label('صورة الصنف')
                    ->image()
                    ->directory('products'),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->required(),
            ]),
        ]);
    }
}