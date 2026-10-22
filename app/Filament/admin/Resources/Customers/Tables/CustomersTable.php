<?php

namespace App\Filament\Admin\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
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
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->email ?: null),

                TextColumn::make('phone')
                    ->label('التليفون')
                    ->searchable()
                    ->copyable()
                    ->placeholder('غير مسجل'),

                TextColumn::make('type')
                    ->label('نوع العميل')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'individual' => 'فرد',
                        'company' => 'شركة',
                        'government' => 'جهة حكومية',
                        default => (string) $state,
                    })
                    ->color(fn ($state): string => match ($state) {
                        'company' => 'primary',
                        'government' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn ($state): string => match ($state) {
                        'company' => 'heroicon-m-building-office-2',
                        'government' => 'heroicon-m-building-library',
                        default => 'heroicon-m-user',
                    }),

                TextColumn::make('opening_balance')
                    ->label('الرصيد الافتتاحي')
                    ->money('SAR')
                    ->sortable()
                    ->color(fn ($record): string => $record->balance_type === 'credit' ? 'success' : 'danger'),

                TextColumn::make('balance_type')
                    ->label('طبيعة الرصيد')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state === 'debit' ? 'مدين' : 'دائن')
                    ->color(fn ($state): string => $state === 'debit' ? 'danger' : 'success'),

                TextColumn::make('credit_limit')
                    ->label('حد الائتمان')
                    ->money('SAR')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('is_active')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state): string => $state ? 'نشط' : 'غير نشط')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'gray')
                    ->icon(fn ($state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle'),

                TextColumn::make('tax_number')
                    ->label('الرقم الضريبي')
                    ->copyable()
                    ->placeholder('غير مسجل')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y - h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع العميل')
                    ->options([
                        'individual' => 'فرد',
                        'company' => 'شركة',
                        'government' => 'جهة حكومية',
                    ]),

                SelectFilter::make('balance_type')
                    ->label('طبيعة الرصيد')
                    ->options([
                        'debit' => 'مدين',
                        'credit' => 'دائن',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('حالة العميل')
                    ->placeholder('كل الحالات')
                    ->trueLabel('العملاء النشطون')
                    ->falseLabel('العملاء غير النشطين'),

                TrashedFilter::make(),
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
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا يوجد عملاء بعد')
            ->emptyStateDescription('ابدأ بإضافة أول عميل أو استيراد قائمة العملاء من Excel.')
            ->emptyStateIcon('heroicon-o-users');
    }
}
