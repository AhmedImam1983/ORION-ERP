<?php

namespace App\Filament\Admin\Resources\ProductSearch\Tables;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductSearchTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with([
                    'brand',
                    'category',
                    'warehouse',
                    'warehouseStocks.warehouse',
                    'warehouseStocks.locations',
                    'vehicles',
                ])
            )
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular(),

                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('اسم الصنف')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('brand.name')
                    ->label('العلامة التجارية')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                TextColumn::make('part_number')
                    ->label('رقم المرجع')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),

                TextColumn::make('category.name')
                    ->label('المجموعة')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->placeholder('-'),
                TextColumn::make('item_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state === 'service' ? 'خدمة' : 'صنف مخزني')
                    ->color(fn ($record): string => $record->isService() ? 'info' : 'success'),

                TextColumn::make('quantity')
                    ->label('الرصيد')
                    ->formatStateUsing(fn ($state, $record): string => $record->isService() ? '—' : number_format((float) $state, 3))
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($record) => $record->isService() ? 'gray' : ($record->quantity <= $record->min_quantity ? 'danger' : 'success')),

                TextColumn::make('warehouse.name')
                    ->label('المخزن الافتراضي')
                    ->badge()
                    ->color('warning')
                    ->placeholder('-'),

                TextColumn::make('locations_summary')
                    ->label('الموقع')
                    ->state(function ($record): string {
                        $locations = $record->warehouseStocks
                            ->flatMap(fn ($stock) => $stock->locations)
                            ->pluck('location_code')
                            ->filter()
                            ->unique()
                            ->values();

                        return $locations->implode(', ') ?: '-';
                    })
                    ->toggleable(),

                TextColumn::make('vehicles_summary')
                    ->label('توافق السيارات')
                    ->state(function ($record): string {
                        $vehicles = $record->vehicles;

                        if ($vehicles->isEmpty()) {
                            return '-';
                        }

                        return $vehicles
                            ->take(3)
                            ->map(function ($vehicle) {
                                $years = '';

                                if ($vehicle->year_from || $vehicle->year_to) {
                                    $years = ' ' . ($vehicle->year_from ?? '؟') . '-' . ($vehicle->year_to ?? '؟');
                                }

                                return trim(
                                    $vehicle->make . ' ' .
                                    ($vehicle->model ?? '') .
                                    $years
                                );
                            })
                            ->implode('، ') .
                            ($vehicles->count() > 3 ? ' ...' : '');
                    })
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('sale_price')
                    ->label('سعر البيع')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('brand_id')
                    ->label('العلامة التجارية')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('category_id')
                    ->label('المجموعة')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد أصناف')
            ->emptyStateDescription('استخدم مربع البحث للعثور على الصنف')
            ->emptyStateIcon('heroicon-o-magnifying-glass');
    }
}
