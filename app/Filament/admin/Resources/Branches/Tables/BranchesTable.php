<?php

namespace App\Filament\Admin\Resources\Branches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BranchesTable
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
                    ->label('اسم الفرع')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('address')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(42)
                    ->placeholder('غير محدد'),

                TextColumn::make('manager')
                    ->label('مدير الفرع')
                    ->searchable()
                    ->placeholder('غير محدد'),

                TextColumn::make('warehouse.name')
                    ->label('المخزن الافتراضي')
                    ->badge()
                    ->color('warning')
                    ->placeholder('غير محدد'),

                TextColumn::make('invoices_count')
                    ->label('الفواتير')
                    ->counts('invoices')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state): string => $state ? 'نشط' : 'غير نشط')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'gray')
                    ->icon(fn ($state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle'),

                TextColumn::make('is_main')
                    ->label('نوع الفرع')
                    ->formatStateUsing(fn ($state): string => $state ? 'الفرع الرئيسي' : 'فرع')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'primary' : 'gray')
                    ->icon(fn ($state): string => $state ? 'heroicon-m-star' : 'heroicon-m-building-office-2'),

                TextColumn::make('phone')
                    ->label('التليفون')
                    ->searchable()
                    ->copyable()
                    ->placeholder('غير مسجل')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y - h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('حالة الفرع')
                    ->placeholder('كل الحالات')
                    ->trueLabel('الفروع النشطة')
                    ->falseLabel('الفروع غير النشطة'),

                TernaryFilter::make('is_main')
                    ->label('نوع الفرع')
                    ->placeholder('الكل')
                    ->trueLabel('الفرع الرئيسي فقط')
                    ->falseLabel('الفروع الأخرى'),

                SelectFilter::make('warehouse_id')
                    ->label('المخزن الافتراضي')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('is_main', 'desc')
            ->emptyStateHeading('لا توجد فروع بعد')
            ->emptyStateDescription('ابدأ بإضافة الفرع الرئيسي للنظام')
            ->emptyStateIcon('heroicon-o-building-office-2');
    }
}
