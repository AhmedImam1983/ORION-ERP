<?php

namespace App\Filament\Admin\Resources\Warehouses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class WarehousesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary')
                    ->fontFamily('mono'),

                TextColumn::make('name')
                    ->label('اسم المخزن')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('location')
                    ->label('الموقع')
                    ->searchable()
                    ->placeholder('غير محدد'),

                TextColumn::make('manager')
                    ->label('مدير المخزن')
                    ->searchable()
                    ->placeholder('غير محدد'),

                TextColumn::make('stocks_count')
                    ->label('عدد الأصناف بالمخزن')
                    ->counts('stocks')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state): string => $state ? 'نشط' : 'غير نشط')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'gray')
                    ->icon(fn ($state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle'),

                TextColumn::make('is_default')
                    ->label('الافتراضي')
                    ->formatStateUsing(fn ($state): string => $state ? 'المخزن الافتراضي' : 'عادي')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'warning' : 'gray')
                    ->icon(fn ($state): string => $state ? 'heroicon-m-star' : 'heroicon-m-building-storefront'),

                TextColumn::make('phone')
                    ->label('التليفون')
                    ->searchable()
                    ->placeholder('غير مسجل')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y - h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('حالة المخزن')
                    ->placeholder('كل الحالات')
                    ->trueLabel('المخازن النشطة')
                    ->falseLabel('المخازن غير النشطة'),

                TernaryFilter::make('is_default')
                    ->label('المخزن الافتراضي')
                    ->placeholder('الكل')
                    ->trueLabel('الافتراضي فقط')
                    ->falseLabel('غير الافتراضي'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),
                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد مخازن بعد')
            ->emptyStateDescription('ابدأ بإضافة مخزن جديد للنظام')
            ->emptyStateIcon('heroicon-o-building-storefront');
    }
}
