<?php

namespace App\Filament\Admin\Resources\StockMovements\Tables;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        $incomingTypes = ['purchase', 'sale_return', 'transfer_in', 'adjustment_in', 'opening'];
        $outgoingTypes = ['sale', 'purchase_return', 'transfer_out', 'adjustment_out'];

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($incomingTypes, $outgoingTypes): Builder {
                return match (request()->query('quick')) {
                    'incoming' => $query->whereIn('type', $incomingTypes),
                    'outgoing' => $query->whereIn('type', $outgoingTypes),
                    'transfers' => $query->whereIn('type', ['transfer_in', 'transfer_out']),
                    'adjustments' => $query->whereIn('type', ['adjustment_in', 'adjustment_out', 'opening']),
                    'today' => $query->whereDate('created_at', today()),
                    default => $query,
                };
            })
            ->columns([
                ImageColumn::make('product.image')
                    ->label('الصورة')
                    ->circular(),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('product.code')
                    ->label('الكود')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('product.name')
                    ->label('الصنف')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('نوع الحركة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'purchase'        => 'شراء',
                        'sale'            => 'بيع',
                        'sale_return'     => 'مرتجع بيع',
                        'purchase_return' => 'مرتجع شراء',
                        'transfer_in'     => 'تحويل وارد',
                        'transfer_out'    => 'تحويل صادر',
                        'adjustment_in'   => 'تسوية زيادة',
                        'adjustment_out'  => 'تسوية نقص',
                        'opening'         => 'رصيد افتتاحي',
                        default           => $state,
                    })
                    ->color(fn($state) => in_array($state, $incomingTypes, true) ? 'success' : 'danger'),

                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->color(fn($record) => in_array($record->type, $incomingTypes, true) ? 'success' : 'danger'),

                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('SAR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('balance_before')
                    ->label('الرصيد قبل')
                    ->numeric()
                    ->sortable()
                    ->color('warning'),

                TextColumn::make('balance_after')
                    ->label('الرصيد بعد')
                    ->numeric()
                    ->sortable()
                    ->color(fn($record) => $record->balance_after > $record->balance_before ? 'success' : 'danger')
                    ->weight('bold'),

                TextColumn::make('reference')
                    ->label('المرجع')
                    ->searchable()
                    ->copyable()
                    ->color('primary'),

                TextColumn::make('warehouse.name')
                    ->label('المخزن')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->default('-'),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->searchable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع الحركة')
                    ->options([
                        'purchase'        => 'شراء',
                        'sale'            => 'بيع',
                        'sale_return'     => 'مرتجع بيع',
                        'purchase_return' => 'مرتجع شراء',
                        'transfer_in'     => 'تحويل وارد',
                        'transfer_out'    => 'تحويل صادر',
                        'adjustment_in'   => 'تسوية زيادة',
                        'adjustment_out'  => 'تسوية نقص',
                        'opening'         => 'رصيد افتتاحي',
                    ]),

                SelectFilter::make('warehouse_id')
                    ->label('المخزن')
                    ->relationship('warehouse', 'name'),
            ])
            // Stock movements are an audit ledger; no destructive bulk actions.
            ->recordActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد حركات مخزون بعد')
            ->emptyStateDescription('ستظهر الحركات تلقائياً عند تأكيد الفواتير أو عند تسجيل تسوية يدوية')
            ->emptyStateIcon('heroicon-o-arrow-path');
    }
}
