<?php

namespace App\Filament\Admin\Resources\Warehouses\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'stocks';
    protected static ?string $title = 'أصناف المخزن';
    protected static ?string $label = 'رصيد صنف';
    protected static ?string $pluralLabel = 'أرصدة الأصناف';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.code')
                    ->label('الكود')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('primary')
                    ->fontFamily('mono'),

                TextColumn::make('product.name')
                    ->label('اسم الصنف')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('product.category.name')
                    ->label('المجموعة')
                    ->placeholder('غير محددة')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('product.unit')
                    ->label('الوحدة')
                    ->placeholder('—'),

                TextColumn::make('quantity')
                    ->label('رصيد هذا المخزن')
                    ->numeric(decimalPlaces: 3)
                    ->sortable()
                    ->color(fn ($record) => (float) $record->quantity <= 0 ? 'danger' : 'success')
                    ->weight('bold'),

                TextColumn::make('opening_quantity')
                    ->label('رصيد بداية الـCutover')
                    ->numeric(decimalPlaces: 3)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('product.quantity')
                    ->label('إجمالي كل المخازن')
                    ->numeric(decimalPlaces: 3)
                    ->color('info'),

                TextColumn::make('product.cost_price')
                    ->label('التكلفة')
                    ->money('SAR'),

                TextColumn::make('product.sale_price')
                    ->label('سعر البيع')
                    ->money('SAR'),

                TextColumn::make('product.is_active')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state): string => $state ? 'نشط' : 'غير نشط')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                TernaryFilter::make('has_stock')
                    ->label('الرصيد')
                    ->placeholder('كل الأرصدة')
                    ->trueLabel('رصيد أكبر من صفر')
                    ->falseLabel('رصيد صفر')
                    ->queries(
                        true: fn ($query) => $query->where('quantity', '>', 0),
                        false: fn ($query) => $query->where('quantity', '<=', 0),
                    ),
            ])
            ->defaultSort('product_id')
            ->emptyStateHeading('لا توجد أرصدة أصناف في هذا المخزن')
            ->emptyStateDescription('الصنف يظهر هنا عند وجود رصيد/سجل مخزني له عبر الشراء أو الـCutover أو التحويل.')
            ->emptyStateIcon('heroicon-o-archive-box');
    }
}
