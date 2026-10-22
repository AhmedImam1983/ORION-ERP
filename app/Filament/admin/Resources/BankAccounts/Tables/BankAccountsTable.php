<?php

namespace App\Filament\Admin\Resources\BankAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bank_name')
                    ->label('البنك')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-building-library'),

                TextColumn::make('account_name')
                    ->label('اسم الحساب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('account_number')
                    ->label('رقم الحساب')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->badge()
                    ->color('warning')
                    ->placeholder('كل الفروع'),

                TextColumn::make('account.name')
                    ->label('الحساب المحاسبي')
                    ->badge()
                    ->color('info')
                    ->placeholder('غير مربوط')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('current_balance')
                    ->label('الرصيد الحالي')
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 2) . ' ' . ($record->currency ?: 'SAR'))
                    ->color(fn ($state) => (float) $state >= 0 ? 'success' : 'danger'),

                TextColumn::make('opening_balance')
                    ->label('الرصيد الافتتاحي')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 2) . ' ' . ($record->currency ?: 'SAR'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('currency')
                    ->label('العملة')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('is_active')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'نشط' : 'غير نشط')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                TextColumn::make('is_default')
                    ->label('الاستخدام')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'افتراضي' : 'عادي')
                    ->color(fn ($state) => $state ? 'primary' : 'gray'),

                TextColumn::make('iban')
                    ->label('IBAN')
                    ->copyable()
                    ->fontFamily('mono')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('الحسابات النشطة')
                    ->falseLabel('الحسابات غير النشطة')
                    ->placeholder('كل الحالات'),

                TernaryFilter::make('is_default')
                    ->label('الحساب الافتراضي')
                    ->trueLabel('افتراضي فقط')
                    ->falseLabel('غير افتراضي')
                    ->placeholder('الكل'),

                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
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
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد حسابات بنكية بعد')
            ->emptyStateDescription('ابدأ بإضافة أول حساب بنكي وربطه بالحساب المحاسبي المناسب.')
            ->emptyStateIcon('heroicon-o-building-library');
    }
}
