<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('بيانات الصنف')->schema([
                ImageEntry::make('image')
                    ->label('الصورة')
                    ->circular()
                    ->placeholder('-'),
                TextEntry::make('code')
                    ->label('الكود')
                    ->weight('bold')
                    ->color('primary')
                    ->copyable(),
                TextEntry::make('name')
                    ->label('اسم الصنف')
                    ->weight('bold')
                    ->size('lg'),
                TextEntry::make('name_en')
                    ->label('الاسم بالإنجليزي')
                    ->placeholder('-'),
                TextEntry::make('item_type')
                    ->label('نوع العنصر')
                    ->formatStateUsing(fn ($state): string => $state === 'service' ? 'خدمة' : 'صنف مخزني')
                    ->badge()
                    ->color(fn (Product $record): string => $record->isService() ? 'info' : 'success'),
                TextEntry::make('brand.name')
                    ->label('العلامة التجارية')
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),
                TextEntry::make('part_number')
                    ->label('رقم المرجع')
                    ->copyable()
                    ->placeholder('-'),
                TextEntry::make('category.name')
                    ->label('المجموعة')
                    ->badge()
                    ->color('primary')
                    ->placeholder('-'),
                TextEntry::make('warehouse.name')
                    ->label('المخزن الافتراضي')
                    ->badge()
                    ->color('warning')
                    ->placeholder('-')
                    ->visible(fn (Product $record): bool => !$record->isService()),
                TextEntry::make('unit')
                    ->label('وحدة القياس'),
                TextEntry::make('barcode')
                    ->label('باركود')
                    ->placeholder('-')
                    ->copyable()
                    ->icon('heroicon-o-qr-code'),
                IconEntry::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])->columns(3),

            Section::make('الأسعار والمخزون')->schema([
                TextEntry::make('cost_price')
                    ->label('سعر التكلفة')
                    ->money('SAR')
                    ->color('danger')
                    ->visible(fn (Product $record): bool => !$record->isService()),
                TextEntry::make('sale_price')
                    ->label('سعر البيع')
                    ->money('SAR')
                    ->color('success')
                    ->weight('bold'),
                TextEntry::make('sale_price_2')
                    ->label('سعر البيع 2')
                    ->money('SAR')
                    ->placeholder('-'),
                TextEntry::make('sale_price_3')
                    ->label('سعر البيع 3')
                    ->money('SAR')
                    ->placeholder('-'),
                TextEntry::make('quantity')
                    ->label('إجمالي الرصيد')
                    ->weight('bold')
                    ->color(fn ($record) => $record->quantity <= $record->min_quantity ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state, Product $record): string => self::formatQuantity($state) . ' ' . $record->unit)
                    ->visible(fn (Product $record): bool => !$record->isService()),
                TextEntry::make('min_quantity')
                    ->label('الحد الأدنى')
                    ->color('warning')
                    ->formatStateUsing(fn ($state, Product $record): string => self::formatQuantity($state) . ' ' . $record->unit)
                    ->visible(fn (Product $record): bool => !$record->isService()),
            ])->columns(3),

            Section::make('أرصدة المخازن')
                ->visible(fn (Product $record): bool => !$record->isService())
                ->description('الرصيد الفعلي للصنف داخل كل مخزن')
                ->icon('heroicon-o-building-storefront')
                ->schema([
                    TextEntry::make('warehouse_stock_breakdown')
                        ->hiddenLabel()
                        ->state(function (Product $record): string {
                            $stocks = $record->warehouseStocks()
                                ->with('warehouse')
                                ->where('quantity', '!=', 0)
                                ->orderByDesc('quantity')
                                ->orderBy('warehouse_id')
                                ->get();

                            if ($stocks->isEmpty()) {
                                return '<div style="padding:14px 16px;border:1px dashed rgba(148,163,184,.28);border-radius:12px;color:#94a3b8;text-align:center">لا يوجد رصيد موزع على المخازن</div>';
                            }

                            $cards = $stocks->map(function ($stock) use ($record): string {
                                $warehouseName = e($stock->warehouse?->name ?? ('مخزن #' . $stock->warehouse_id));
                                $quantity = e(self::formatQuantity($stock->quantity));
                                $unit = e((string) $record->unit);

                                return '<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 16px;border:1px solid rgba(148,163,184,.18);border-radius:12px;background:rgba(15,23,42,.35)">' .
                                    '<div style="display:flex;align-items:center;gap:10px;min-width:0">' .
                                        '<span style="width:8px;height:8px;border-radius:999px;background:#22c55e;box-shadow:0 0 0 4px rgba(34,197,94,.10);flex:0 0 auto"></span>' .
                                        '<span style="font-weight:800;color:#e5e7eb;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' . $warehouseName . '</span>' .
                                    '</div>' .
                                    '<div style="white-space:nowrap;text-align:left">' .
                                        '<strong style="font-size:18px;color:#34d399">' . $quantity . '</strong>' .
                                        '<span style="margin-inline-start:5px;color:#94a3b8;font-size:12px">' . $unit . '</span>' .
                                    '</div>' .
                                '</div>';
                            })->implode('');

                            return '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;width:100%">' . $cards . '</div>';
                        })
                        ->html()
                        ->columnSpanFull(),
                ]),

            Section::make('مواقع المخزون')
                ->visible(fn (Product $record): bool => !$record->isService())
                ->schema([
                    TextEntry::make('locations_list')
                        ->label('المواقع')
                        ->state(function (Product $record): string {
                            return $record->warehouseStocks
                                ->flatMap(fn ($stock) => $stock->locations)
                                ->pluck('location_code')
                                ->filter()
                                ->unique()
                                ->implode(', ') ?: '-';
                        })
                        ->placeholder('-')
                        ->columnSpanFull(),
                ]),

            Section::make('معلومات إضافية')->schema([
                TextEntry::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i'),
                TextEntry::make('updated_at')
                    ->label('آخر تعديل')
                    ->dateTime('d/m/Y H:i'),
            ])->columns(2)->collapsible(),

        ]);
    }

    private static function formatQuantity(mixed $value): string
    {
        $number = round((float) $value, 3);

        if (abs($number - round($number)) < 0.0005) {
            return number_format($number, 0, '.', ',');
        }

        return rtrim(rtrim(number_format($number, 3, '.', ','), '0'), '.');
    }
}
