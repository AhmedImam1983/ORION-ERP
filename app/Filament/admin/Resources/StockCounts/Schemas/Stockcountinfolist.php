<?php

namespace App\Filament\Admin\Resources\StockCounts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockCountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('بيانات الجرد')->schema([
                TextEntry::make('count_number')
                    ->label('رقم الجرد')
                    ->weight('bold')
                    ->color('primary')
                    ->copyable(),
                TextEntry::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state === 'full' ? 'جرد كامل' : 'جرد جزئي')
                    ->color(fn($state) => $state === 'full' ? 'info' : 'warning'),
                TextEntry::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'draft'       => 'مسودة',
                        'in_progress' => 'جاري',
                        'completed'   => 'مكتمل',
                        'cancelled'   => 'ملغي',
                        default       => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'completed'   => 'success',
                        'in_progress' => 'warning',
                        'cancelled'   => 'danger',
                        default       => 'gray',
                    }),
                TextEntry::make('warehouse.name')
                    ->label('المخزن')
                    ->placeholder('-'),
                TextEntry::make('count_date')
                    ->label('تاريخ الجرد')
                    ->date('d/m/Y'),
                TextEntry::make('createdBy.name')
                    ->label('أنشأ بواسطة')
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->label('ملاحظات')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ])->columns(3),

            Section::make('أصناف الجرد')->schema([
                \Filament\Infolists\Components\RepeatableEntry::make('items')
                    ->label('')
                    ->schema([
                        TextEntry::make('product.name')
                            ->label('الصنف')
                            ->weight('bold'),
                        TextEntry::make('product.code')
                            ->label('الكود')
                            ->color('primary'),
                        TextEntry::make('system_quantity')
                            ->label('كمية النظام')
                            ->numeric(),
                        TextEntry::make('actual_quantity')
                            ->label('الكمية الفعلية')
                            ->numeric()
                            ->placeholder('لم يُجرد'),
                        TextEntry::make('difference')
                            ->label('الفرق')
                            ->formatStateUsing(fn($state) => $state === null ? '—' : number_format((float) $state, 2))
                            ->placeholder('—')
                            ->color(fn($state) => $state === null ? 'gray' : ($state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))),
                        TextEntry::make('difference_value')
                            ->label('قيمة الفرق')
                            ->formatStateUsing(fn($state) => $state === null ? '—' : number_format((float) $state, 2) . ' ر.س')
                            ->placeholder('—')
                            ->color(fn($state) => $state === null ? 'gray' : ($state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))),
                    ])
                    ->columns(6),
            ]),

        ]);
    }
}