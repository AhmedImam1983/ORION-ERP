<?php

namespace App\Filament\Admin\Resources\Invoices\Schemas;

use App\Models\Invoice;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('بيانات الفاتورة')->schema([
                TextEntry::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->weight('bold')
                    ->color('primary')
                    ->copyable(),
                TextEntry::make('type')
                    ->label('نوع الفاتورة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'sale'            => 'فاتورة بيع',
                        'purchase'        => 'فاتورة شراء',
                        'sale_return'     => 'مرتجع بيع',
                        'purchase_return' => 'مرتجع شراء',
                        default           => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'sale'            => 'success',
                        'purchase'        => 'info',
                        'sale_return'     => 'warning',
                        'purchase_return' => 'danger',
                        default           => 'gray',
                    }),
                TextEntry::make('payment_type')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'cash'        => 'نقدي',
                        'credit'      => 'آجل',
                        'installment' => 'تقسيط',
                        default       => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'cash'        => 'success',
                        'credit'      => 'warning',
                        'installment' => 'info',
                        default       => 'gray',
                    }),
                TextEntry::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'draft'     => 'مسودة',
                        'confirmed' => 'مؤكدة',
                        'paid'      => 'مدفوعة',
                        'partial'   => 'مدفوعة جزئياً',
                        'cancelled' => 'ملغية',
                        default     => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'paid'      => 'success',
                        'confirmed' => 'info',
                        'partial'   => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),
                TextEntry::make('customer.name')
                    ->label('العميل')
                    ->placeholder('عميل نقدي')
                    ->icon('heroicon-o-user'),
                TextEntry::make('supplier.name')
                    ->label('المورد')
                    ->placeholder('-')
                    ->icon('heroicon-o-truck'),
                TextEntry::make('invoice_date')
                    ->label('تاريخ الفاتورة')
                    ->date('d/m/Y')
                    ->icon('heroicon-o-calendar'),
                TextEntry::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->icon('heroicon-o-clock'),
                // `invoices` still has a legacy string column named `warehouse`.
                // That attribute shadows the Eloquent relationship with the same name,
                // so `warehouse.name` can render as '-' even when warehouse_id is valid.
                // Read the canonical warehouse_id explicitly for display.
                TextEntry::make('warehouse_display')
                    ->label('المخزن')
                    ->state(fn (Invoice $record): ?string => $record->warehouse_id
                        ? \App\Models\Warehouse::query()->whereKey($record->warehouse_id)->value('name')
                        : null)
                    ->placeholder('-')
                    ->icon('heroicon-o-archive-box'),
                TextEntry::make('branch.name')
                    ->label('الفرع')
                    ->placeholder('-')
                    ->icon('heroicon-o-building-office'),
            ])->columns(2),

            Section::make('الإجماليات المالية')->schema([
                TextEntry::make('subtotal')
                    ->label('المجموع قبل الخصم')
                    ->money('SAR')
                    ->color('gray'),
                TextEntry::make('discount_amount')
                    ->label('قيمة الخصم')
                    ->money('SAR')
                    ->color('warning'),
                TextEntry::make('tax_amount')
                    ->label('قيمة الضريبة')
                    ->money('SAR')
                    ->color('gray'),
                TextEntry::make('total')
                    ->label('الإجمالي النهائي')
                    ->money('SAR')
                    ->weight('bold')
                    ->size('lg')
                    ->color('primary'),
                TextEntry::make('paid_amount')
                    ->label('المبلغ المدفوع')
                    ->money('SAR')
                    ->color('success'),
                TextEntry::make('remaining_amount')
                    ->label('المبلغ المتبقي')
                    ->money('SAR')
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),
            ])->columns(3),

            Section::make('الأصناف')->schema([
                \Filament\Infolists\Components\RepeatableEntry::make('items')
                    ->label('')
                    ->schema([
                        TextEntry::make('product_name')
                            ->label('الصنف')
                            ->weight('bold'),
                        TextEntry::make('quantity')
                            ->label('الكمية')
                            ->numeric()
                            ->suffix(fn($record) => ' ' . $record->unit),
                        TextEntry::make('unit_price')
                            ->label('السعر')
                            ->money('SAR'),
                        TextEntry::make('discount_percent')
                            ->label('خصم٪')
                            ->suffix('%')
                            ->placeholder('0'),
                        TextEntry::make('total')
                            ->label('الإجمالي')
                            ->money('SAR')
                            ->weight('bold')
                            ->color('primary'),
                    ])
                    ->columns(5),
            ]),

            Section::make('ملاحظات ومعلومات إضافية')->schema([
                TextEntry::make('notes')
                    ->label('ملاحظات')
                    ->placeholder('لا توجد ملاحظات')
                    ->columnSpanFull(),
                TextEntry::make('createdBy.name')
                    ->label('أنشأ بواسطة')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i'),
                TextEntry::make('updated_at')
                    ->label('آخر تعديل')
                    ->dateTime('d/m/Y H:i'),
            ])->columns(2)->collapsible(),

        ]);
    }
}