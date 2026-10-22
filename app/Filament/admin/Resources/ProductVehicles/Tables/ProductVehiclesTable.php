<?php

namespace App\Filament\Admin\Resources\ProductVehicles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductVehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.code')
                    ->label('كود الصنف')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('product.name')
                    ->label('الصنف')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('product.part_number')
                    ->label('رقم القطعة')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),

                TextColumn::make('make')
                    ->label('ماركة المركبة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('model')
                    ->label('الموديل')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('year_range')
                    ->label('السنوات')
                    ->state(fn ($record): string => $record->year_range)
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('year_from', $direction);
                    }),

                TextColumn::make('engine')
                    ->label('المحرك')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('الصنف')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->color('primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('make');
    }
}
