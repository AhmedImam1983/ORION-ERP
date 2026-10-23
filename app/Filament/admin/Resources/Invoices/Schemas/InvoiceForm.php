<?php

namespace App\Filament\Admin\Resources\Invoices\Schemas;

use App\Models\Product;
use App\Models\Warehouse;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الفاتورة')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('رقم الفاتورة')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn() => \App\Models\Invoice::generateNumber('sale')),

                        Select::make('type')
                            ->label('نوع الفاتورة')
                            ->options([
                                'sale'            => 'فاتورة بيع',
                                'purchase'        => 'فاتورة شراء',
                                'sale_return'     => 'مرتجع بيع',
                                'purchase_return' => 'مرتجع شراء',
                            ])
                            ->default('sale')
                            ->required()
                            ->live(),

                        Select::make('payment_type')
                            ->label('طريقة الدفع')
                            ->options([
                                'cash'        => 'نقدي',
                                'credit'      => 'آجل',
                                'installment' => 'تقسيط',
                            ])
                            ->default('cash')
                            ->required(),

                        Select::make('customer_id')
                            ->label('العميل')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->default(null),

                        Select::make('supplier_id')
                            ->label('المورد')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->default(null),

                        DatePicker::make('invoice_date')
                            ->label('تاريخ الفاتورة')
                            ->required()
                            ->default(now()),

                        DatePicker::make('due_date')
                            ->label('تاريخ الاستحقاق'),

                        Select::make('warehouse_id')
                            ->label('المخزن')
                            ->options(fn () => Warehouse::query()
                                ->where('is_active', true)
                                ->orderByDesc('is_default')
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->default(fn () => Warehouse::getDefault()?->id)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->helperText('كل حركة مخزون في الفاتورة ستؤثر على هذا المخزن فقط.'),

                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'draft'     => 'مسودة',
                                'confirmed' => 'مؤكدة',
                                'paid'      => 'مدفوعة',
                                'partial'   => 'مدفوعة جزئياً',
                                'cancelled' => 'ملغية',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('الأصناف')
                    ->schema([
                        Repeater::make('items')
                            ->label('أصناف الفاتورة')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('الصنف')
                                    ->options(function (Get $get) {
                                        $type = (string) ($get('../../type') ?? 'sale');
                                        $warehouseId = $get('../../warehouse_id');

                                        $query = Product::query()->where('is_active', true);

                                        // Service items are valid on sales and sales returns, but
                                        // are not inventory purchases in this initial workflow.
                                        if (in_array($type, ['purchase', 'purchase_return'], true)) {
                                            $query->where('item_type', 'stock');
                                        }

                                        if ($warehouseId && $type === 'sale') {
                                            $query->where(function ($items) use ($warehouseId) {
                                                $items->where('item_type', 'service')
                                                    ->orWhereHas('warehouseStocks', function ($stock) use ($warehouseId) {
                                                        $stock->where('warehouse_id', $warehouseId)
                                                            ->where('quantity', '>', 0);
                                                    });
                                            });
                                        } elseif ($warehouseId && $type === 'purchase_return') {
                                            $query->whereHas('warehouseStocks', function ($stock) use ($warehouseId) {
                                                $stock->where('warehouse_id', $warehouseId)
                                                    ->where('quantity', '>', 0);
                                            });
                                        }

                                        return $query->orderBy('name')->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (!$state) {
                                            return;
                                        }

                                        $product = Product::find($state);

                                        if (!$product) {
                                            return;
                                        }

                                        $set('product_name', $product->name);
                                        $set('product_code', $product->code);
                                        $set('unit', $product->unit);

                                        /*
                                         * داخل الـRepeater:
                                         * ../../type = نوع الفاتورة الرئيسي
                                         */
                                        $invoiceType = $get('../../type');

                                        /*
                                         * شراء / مرتجع شراء:
                                         * نستخدم سعر التكلفة.
                                         *
                                         * بيع / مرتجع بيع:
                                         * نستخدم سعر البيع.
                                         */
                                        $unitPrice = in_array(
                                            $invoiceType,
                                            ['purchase', 'purchase_return'],
                                            true
                                        )
                                            ? ($product->cost_price ?? 0)
                                            : ($product->sale_price ?? 0);

                                        $set('unit_price', $unitPrice);
                                        $set('cost_price', $product->cost_price ?? 0);

                                        // ZATCA VAT Snapshot
                                        $set(
                                            'vat_type',
                                            $product->vat_type ?? 'S'
                                        );

                                        $set(
                                            'tax_percent',
                                            $product->vat_percent ?? 15
                                        );

                                        $set(
                                            'vat_exemption_reason_code',
                                            $product->vat_exemption_reason_code
                                        );

                                        $set(
                                            'vat_exemption_reason',
                                            $product->vat_exemption_reason
                                        );

                                        self::updateItemTotal($get, $set);
                                    })
                                    ->columnSpan(2),

                                Hidden::make('product_name'),

                                Hidden::make('product_code'),

                                TextInput::make('unit')
                                    ->label('الوحدة')
                                    ->default('قطعة'),

                                TextInput::make('quantity')
                                    ->label('الكمية')
                                    ->numeric()
                                    ->default(1)
                                    ->live()
                                    ->afterStateUpdated(
                                        fn(Get $get, Set $set) =>
                                            self::updateItemTotal($get, $set)
                                    ),

                                TextInput::make('unit_price')
                                    ->label('السعر')
                                    ->numeric()
                                    ->prefix('ر.س')
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(
                                        fn(Get $get, Set $set) =>
                                            self::updateItemTotal($get, $set)
                                    ),

                                Hidden::make('cost_price')
                                    ->default(0),

                                TextInput::make('discount_percent')
                                    ->label('خصم٪')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(
                                        fn(Get $get, Set $set) =>
                                            self::updateItemTotal($get, $set)
                                    ),

                                Hidden::make('discount_amount')
                                    ->default(0),

                                // ZATCA VAT
                                Hidden::make('vat_type')
                                    ->default('S'),

                                Hidden::make('vat_exemption_reason_code'),

                                Hidden::make('vat_exemption_reason'),

                                TextInput::make('tax_percent')
                                    ->label('ضريبة٪')
                                    ->numeric()
                                    ->default(15)
                                    ->suffix('%')
                                    ->live()
                                    ->afterStateUpdated(
                                        fn(Get $get, Set $set) =>
                                            self::updateItemTotal($get, $set)
                                    ),

                                Hidden::make('tax_amount')
                                    ->default(0),

                                TextInput::make('total')
                                    ->label('الإجمالي')
                                    ->numeric()
                                    ->prefix('ر.س')
                                    ->default(0)
                                    ->readOnly(),
                            ])
                            ->columns(4)
                            ->addActionLabel('إضافة صنف')
                            ->reorderable()
                            ->cloneable()
                            ->collapsible()
                            ->live()
                            ->afterStateUpdated(
                                fn(Get $get, Set $set) =>
                                    self::updateInvoiceTotals($get, $set)
                            ),
                    ]),

                Section::make('الإجماليات')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('المجموع')
                            ->numeric()
                            ->prefix('ر.س')
                            ->default(0)
                            ->readOnly(),

                        TextInput::make('discount_percent')
                            ->label('نسبة الخصم٪')
                            ->numeric()
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(
                                fn(Get $get, Set $set) =>
                                    self::updateInvoiceTotals($get, $set)
                            ),

                        TextInput::make('discount_amount')
                            ->label('قيمة الخصم')
                            ->numeric()
                            ->prefix('ر.س')
                            ->default(0)
                            ->readOnly(),

                        TextInput::make('tax_percent')
                            ->label('نسبة الضريبة٪')
                            ->numeric()
                            ->default(0),

                        TextInput::make('tax_amount')
                            ->label('قيمة الضريبة')
                            ->numeric()
                            ->prefix('ر.س')
                            ->default(0)
                            ->readOnly(),

                        TextInput::make('total')
                            ->label('الإجمالي النهائي')
                            ->numeric()
                            ->prefix('ر.س')
                            ->default(0)
                            ->readOnly(),

                        TextInput::make('paid_amount')
                            ->label('المبلغ المدفوع')
                            ->numeric()
                            ->prefix('ر.س')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(
                                fn(Get $get, Set $set) =>
                                    self::updateRemainingAmount($get, $set)
                            ),

                        TextInput::make('remaining_amount')
                            ->label('المتبقي')
                            ->numeric()
                            ->prefix('ر.س')
                            ->default(0)
                            ->readOnly(),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->default(null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * حساب إجمالي الصنف.
     */
    protected static function updateItemTotal(Get $get, Set $set): void
    {
        $quantity = (float) ($get('quantity') ?? 0);

        $unitPrice = (float) ($get('unit_price') ?? 0);

        $discountPercent = (float) ($get('discount_percent') ?? 0);

        $taxPercent = (float) ($get('tax_percent') ?? 0);

        $subtotal = $quantity * $unitPrice;

        $discountAmount =
            $subtotal * ($discountPercent / 100);

        $afterDiscount =
            $subtotal - $discountAmount;

        $taxAmount =
            $afterDiscount * ($taxPercent / 100);

        $total =
            $afterDiscount + $taxAmount;

        $set(
            'discount_amount',
            round($discountAmount, 2)
        );

        $set(
            'tax_amount',
            round($taxAmount, 2)
        );

        $set(
            'total',
            round($total, 2)
        );

        /*
         * تحديث إجمالي الفاتورة بالكامل.
         */
        self::updateInvoiceTotals($get, $set);
    }

    /**
     * تجميع جميع أصناف الفاتورة.
     */
    protected static function updateInvoiceTotals(
        Get $get,
        Set $set
    ): void {
        $items = $get('../../items') ?? [];

        $subtotal = 0;
        $itemsDiscount = 0;
        $itemsTax = 0;

        foreach ($items as $item) {
            $quantity =
                (float) ($item['quantity'] ?? 0);

            $unitPrice =
                (float) ($item['unit_price'] ?? 0);

            $discountPercent =
                (float) ($item['discount_percent'] ?? 0);

            $taxPercent =
                (float) ($item['tax_percent'] ?? 0);

            $lineSubtotal =
                $quantity * $unitPrice;

            $lineDiscount =
                $lineSubtotal *
                ($discountPercent / 100);

            $afterDiscount =
                $lineSubtotal - $lineDiscount;

            $lineTax =
                $afterDiscount *
                ($taxPercent / 100);

            $subtotal += $lineSubtotal;
            $itemsDiscount += $lineDiscount;
            $itemsTax += $lineTax;
        }

        /*
         * خصم الفاتورة العام.
         */
        $invoiceDiscountPercent =
            (float) ($get('../../discount_percent') ?? 0);

        $invoiceDiscount =
            $subtotal *
            ($invoiceDiscountPercent / 100);

        $discountAmount =
            $itemsDiscount + $invoiceDiscount;

        $taxableAmount =
            max($subtotal - $discountAmount, 0);

        /*
         * الضرائب الموجودة على مستوى الأصناف
         * محسوبة بالفعل داخل items.
         */
        $taxAmount = $itemsTax;

        $total =
            $subtotal -
            $discountAmount +
            $taxAmount;

        $set(
            '../../subtotal',
            round($subtotal, 2)
        );

        $set(
            '../../discount_amount',
            round($discountAmount, 2)
        );

        $set(
            '../../tax_amount',
            round($taxAmount, 2)
        );

        $set(
            '../../total',
            round($total, 2)
        );

        self::updateRemainingAmount($get, $set);
    }

    /**
     * حساب المتبقي.
     */
    protected static function updateRemainingAmount(
        Get $get,
        Set $set
    ): void {
        $total =
            (float) ($get('../../total') ?? 0);

        $paid =
            (float) ($get('../../paid_amount') ?? 0);

        $remaining =
            max($total - $paid, 0);

        $set(
            '../../remaining_amount',
            round($remaining, 2)
        );
    }
}