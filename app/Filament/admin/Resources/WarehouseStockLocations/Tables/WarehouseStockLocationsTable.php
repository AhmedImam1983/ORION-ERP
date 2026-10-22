<?php

namespace App\Filament\Admin\Resources\WarehouseStockLocations\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Warehouse;

class WarehouseStockLocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['warehouseStock.product', 'warehouseStock.warehouse']))
            ->columns([
                TextColumn::make('location_code')
                    ->label('الموقع')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('warehouseStock.product.code')
                    ->label('كود الصنف')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('warehouseStock.product.name')
                    ->label('اسم الصنف')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('warehouseStock.warehouse.name')
                    ->label('المخزن')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('warehouseStock.quantity')
                    ->label('الرصيد')
                    ->numeric(decimalPlaces: 3)
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($record) => (float) ($record->warehouseStock?->quantity ?? 0) > 0 ? 'success' : 'danger'),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(40)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('المخزن')
                    ->options(fn () => Warehouse::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $q, $warehouseId) => $q->whereHas('warehouseStock', fn (Builder $sq) => $sq->where('warehouse_id', $warehouseId))
                        );
                    }),
            ])
            ->defaultSort('warehouse_stock_id')
            ->recordActions([
                EditAction::make()->label('تعديل'),
            ])
            ->striped()
            ->paginated([20, 50, 100]);
    }
}
