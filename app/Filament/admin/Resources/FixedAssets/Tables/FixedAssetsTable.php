<?php

namespace App\Filament\Admin\Resources\FixedAssets\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FixedAssetsTable
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
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('الأصل')
                    ->description(fn ($record) => collect([
                        $record->serial_number ? 'S/N ' . $record->serial_number : null,
                        $record->location ?: null,
                    ])->filter()->implode(' · '))
                    ->searchable(['name', 'code', 'serial_number', 'location'])
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category')
                    ->label('الفئة')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->badge()
                    ->color('info')
                    ->placeholder('غير محدد')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('purchase_date')
                    ->label('تاريخ الشراء')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('purchase_price')
                    ->label('سعر الشراء')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('accumulated_depreciation')
                    ->label('مجمع الاستهلاك')
                    ->money('SAR')
                    ->sortable()
                    ->color('warning'),

                TextColumn::make('net_book_value')
                    ->label('صافي القيمة')
                    ->money('SAR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('depreciation_rate')
                    ->label('الاستهلاك')
                    ->suffix('%')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'active'   => 'نشط',
                        'disposed' => 'تالف',
                        'sold'     => 'مباع',
                        default    => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'active'   => 'success',
                        'disposed' => 'danger',
                        'sold'     => 'warning',
                        default    => 'gray',
                    })
                    ->icon(fn ($state) => match($state) {
                        'active' => 'heroicon-m-check-circle',
                        'disposed' => 'heroicon-m-exclamation-triangle',
                        'sold' => 'heroicon-m-banknotes',
                        default => null,
                    }),

                TextColumn::make('salvage_value')
                    ->label('قيمة الخردة')
                    ->money('SAR')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('depreciation_method')
                    ->label('طريقة الاستهلاك')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'straight_line' => 'القسط الثابت',
                        'declining_balance' => 'القسط المتناقص',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('location')
                    ->label('الموقع')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('الفئة')
                    ->options([
                        'أثاث ومفروشات'   => 'أثاث ومفروشات',
                        'أجهزة كمبيوتر'   => 'أجهزة كمبيوتر',
                        'سيارات'          => 'سيارات',
                        'معدات وآلات'     => 'معدات وآلات',
                        'مباني وعقارات'   => 'مباني وعقارات',
                        'أجهزة كهربائية'  => 'أجهزة كهربائية',
                        'أخرى'            => 'أخرى',
                    ]),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'   => 'نشط',
                        'disposed' => 'تالف',
                        'sold'     => 'مباع',
                    ]),

                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('depreciation_method')
                    ->label('طريقة الاستهلاك')
                    ->options([
                        'straight_line' => 'القسط الثابت',
                        'declining_balance' => 'القسط المتناقص',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square'),

                Action::make('depreciate')
                    ->label('تسجيل استهلاك')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('تسجيل استهلاك شهري')
                    ->modalDescription('سيتم تسجيل الاستهلاك الشهري لهذا الأصل وفق إعداداته الحالية.')
                    ->modalSubmitActionLabel('تسجيل الاستهلاك')
                    ->action(fn ($record) => $record->processMonthlyDepreciation())
                    ->visible(fn ($record) => $record->status === 'active' && $record->net_book_value > $record->salvage_value),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->searchPlaceholder('ابحث باسم الأصل أو الكود أو الرقم التسلسلي أو الموقع...')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد أصول ثابتة بعد')
            ->emptyStateDescription('أضف أول أصل وابدأ متابعة القيمة الدفترية والاستهلاك.')
            ->emptyStateIcon('heroicon-o-building-office');
    }
}
