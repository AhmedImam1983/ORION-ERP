<?php

namespace App\Filament\Admin\Resources\PurchaseOrders\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // ─────────────────────────────────────────────────────────────
            // بيانات أمر الشراء
            // ─────────────────────────────────────────────────────────────
            Section::make('بيانات أمر الشراء')
                ->description('البيانات الأساسية للطلب والمورد والتواريخ')
                ->icon('heroicon-o-shopping-cart')
                ->extraAttributes([
                    'class' => 'orion-form-section',
                ])
                ->schema([
                    TextInput::make('order_number')
                        ->label('رقم أمر الشراء')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default(fn () => \App\Models\PurchaseOrder::generateNumber())
                        ->prefixIcon('heroicon-o-document-text')
                        ->readOnly(),

                    Select::make('supplier_id')
                        ->label('المورد')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->prefixIcon('heroicon-o-user-group'),

                    DatePicker::make('order_date')
                        ->label('تاريخ الطلب')
                        ->required()
                        ->default(today())
                        ->displayFormat('d/m/Y')
                        ->native(false),

                    DatePicker::make('expected_date')
                        ->label('تاريخ الاستلام المتوقع')
                        ->displayFormat('d/m/Y')
                        ->native(false)
                        ->placeholder('اختر تاريخ الاستلام'),

                    Select::make('warehouse_id')
                        ->label('المخزن')
                        ->relationship('warehouse', 'name')
                        ->searchable()
                        ->preload()
                        ->prefixIcon('heroicon-o-building-storefront')
                        ->placeholder('اختر المخزن'),

                    Select::make('branch_id')
                        ->label('الفرع')
                        ->relationship('branch', 'name')
                        ->searchable()
                        ->preload()
                        ->prefixIcon('heroicon-o-building-office-2')
                        ->placeholder('اختر الفرع'),

                    Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'draft'     => 'مسودة',
                            'sent'      => 'مُرسل للمورد',
                            'confirmed' => 'مؤكد',
                            'partial'   => 'استلام جزئي',
                            'received'  => 'مستلم كامل',
                            'cancelled' => 'ملغي',
                        ])
                        ->default('draft')
                        ->required()
                        ->prefixIcon('heroicon-o-flag'),

                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->placeholder('أضف أي ملاحظات أو تعليمات خاصة بالطلب...')
                        ->rows(3)
                        ->default(null)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            // ─────────────────────────────────────────────────────────────
            // الأصناف المطلوبة
            // ─────────────────────────────────────────────────────────────
            Section::make('الأصناف المطلوبة')
                ->description('أضف الأصناف والكميات والأسعار المطلوبة')
                ->icon('heroicon-o-cube')
                ->extraAttributes([
                    'class' => 'orion-form-section orion-items-section',
                ])
                ->schema([
                    Repeater::make('items')
                        ->label('الأصناف')
                        ->relationship()
                        ->schema([
                            Select::make('product_id')
                                ->label('الصنف')
                                ->options(Product::where('is_active', true)->where('item_type', 'stock')->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->live()
                                ->placeholder('اختر الصنف')
                                ->prefixIcon('heroicon-o-cube')
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($state) {
                                        $product = Product::find($state);

                                        if ($product) {
                                            $set('product_name', $product->name);
                                            $set('product_code', $product->code);
                                            $set('unit', $product->unit);
                                            $set('unit_price', $product->cost_price);
                                        }
                                    }
                                })
                                ->columnSpan(2),

                            TextInput::make('product_name')
                                ->label('اسم الصنف')
                                ->required()
                                ->prefixIcon('heroicon-o-tag')
                                ->placeholder('اسم الصنف')
                                ->columnSpan(2),

                            TextInput::make('unit')
                                ->label('الوحدة')
                                ->default('قطعة')
                                ->prefixIcon('heroicon-o-scale'),

                            TextInput::make('quantity')
                                ->label('الكمية المطلوبة')
                                ->numeric()
                                ->minValue(0)
                                ->default(1)
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, $get) {
                                    $set('total', round(floatval($state) * floatval($get('unit_price')), 2));
                                }),

                            TextInput::make('unit_price')
                                ->label('سعر الوحدة')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('ر.س')
                                ->default(0)
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, $get) {
                                    $set('total', round(floatval($state) * floatval($get('quantity')), 2));
                                }),

                            TextInput::make('total')
                                ->label('الإجمالي')
                                ->numeric()
                                ->prefix('ر.س')
                                ->default(0)
                                ->readOnly()
                                ->extraInputAttributes([
                                    'class' => 'font-semibold',
                                ]),

                            TextInput::make('received_quantity')
                                ->label('الكمية المستلمة')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                        ])
                        ->columns(4)
                        ->addActionLabel('إضافة صنف')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['product_name'] ?? 'صنف جديد')
                        ->extraAttributes([
                            'class' => 'orion-purchase-items',
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}