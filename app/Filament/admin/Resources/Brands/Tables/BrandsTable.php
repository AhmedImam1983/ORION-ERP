<?php

namespace App\Filament\Admin\Resources\Brands\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name_en')
                    ->label('الاسم بالإنجليزي')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'manufacturer' => 'مصنّع',
                        'aftermarket'  => 'Aftermarket',
                        'generic'      => 'عام',
                        'other'        => 'أخرى',
                        default        => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'manufacturer' => 'primary',
                        'aftermarket'  => 'info',
                        'generic'      => 'gray',
                        default        => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('products_count')
                    ->label('عدد الأصناف')
                    ->counts('products')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'manufacturer' => 'مصنّع',
                        'aftermarket'  => 'Aftermarket',
                        'generic'      => 'عام',
                        'other'        => 'أخرى',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('الحالة'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->color('gray')
                    ->extraAttributes(['title' => 'عرض العلامة التجارية']),

                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->color('primary')
                    ->extraAttributes(['title' => 'تعديل العلامة التجارية']),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                    ForceDeleteBulkAction::make()->label('حذف نهائي'),
                    RestoreBulkAction::make()->label('استعادة'),
                ]),
            ])
            ->defaultSort('name');
    }
}
