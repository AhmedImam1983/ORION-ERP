<?php

namespace App\Filament\Admin\Resources\Accounts\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AccountsTable
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
                    ->label('اسم الحساب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.name')
                    ->label('الحساب الأب')
                    ->searchable()
                    ->default('-'),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'asset'     => 'أصول',
                        'liability' => 'خصوم',
                        'equity'    => 'حقوق الملكية',
                        'revenue'   => 'إيرادات',
                        'expense'   => 'مصروفات',
                        default     => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'asset'     => 'success',
                        'liability' => 'danger',
                        'equity'    => 'primary',
                        'revenue'   => 'info',
                        'expense'   => 'warning',
                        default     => 'gray',
                    }),

                TextColumn::make('level')
                    ->label('المستوى')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'main'   => 'رئيسي',
                        'sub'    => 'فرعي',
                        'detail' => 'تفصيلي',
                        default  => $state,
                    }),

                TextColumn::make('opening_balance')
                    ->label('الرصيد الافتتاحي')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('balance_type')
                    ->label('الطبيعة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'debit' ? 'مدين' : 'دائن')
                    ->color(fn ($state) => $state === 'debit' ? 'danger' : 'success'),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'asset'     => 'أصول',
                        'liability' => 'خصوم',
                        'equity'    => 'حقوق الملكية',
                        'revenue'   => 'إيرادات',
                        'expense'   => 'مصروفات',
                    ]),

                SelectFilter::make('level')
                    ->label('المستوى')
                    ->options([
                        'main'   => 'رئيسي',
                        'sub'    => 'فرعي',
                        'detail' => 'تفصيلي',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
            ])
            ->defaultSort('code')
            ->emptyStateHeading('لا توجد حسابات')
            ->emptyStateDescription('ابدأ ببناء شجرة الحسابات')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}
