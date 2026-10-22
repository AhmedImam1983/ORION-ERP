<?php

namespace App\Filament\Admin\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المجموعة')
                    ->description(fn ($record) => $record->name_en ?: 'بدون اسم إنجليزي')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('gray'),

                TextColumn::make('code')
                    ->label('الكود')
                    ->placeholder('—')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('products_count')
                    ->label('عدد الأصناف')
                    ->counts('products')
                    ->badge()
                    ->color(fn ($state): string => ((int) $state > 0) ? 'primary' : 'gray')
                    ->sortable(),

                ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular()
                    ->size(38),

                TextColumn::make('is_active')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'نشط' : 'غير نشط')
                    ->color(fn ($state): string => $state ? 'success' : 'gray')
                    ->icon(fn ($state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle'),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('name_en')
                    ->label('الاسم الإنجليزي')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(45)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('المحذوفات'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),
                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                    ForceDeleteBulkAction::make()->label('حذف نهائي'),
                    RestoreBulkAction::make()->label('استعادة'),
                ]),
            ])
            ->searchPlaceholder('ابحث باسم المجموعة أو الكود...')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد مجموعات بعد')
            ->emptyStateDescription('ابدأ بإضافة أول مجموعة لتنظيم الأصناف داخل المخزون.')
            ->emptyStateIcon('heroicon-o-rectangle-stack');
    }
}
