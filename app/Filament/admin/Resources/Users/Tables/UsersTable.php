<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('المستخدم')
                    ->description(fn ($record) => $record->email)
                    ->searchable(['name', 'email'])
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-user-circle'),

                TextColumn::make('roles.name')
                    ->label('الدور')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'super_admin' => 'مدير النظام',
                        'admin'       => 'مدير',
                        'cashier'     => 'كاشير',
                        'accountant'  => 'محاسب',
                        'storekeeper' => 'أمين مخزن',
                        default       => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'super_admin' => 'danger',
                        'admin'       => 'warning',
                        'cashier'     => 'success',
                        'accountant'  => 'info',
                        'storekeeper' => 'primary',
                        default       => 'gray',
                    }),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->badge()
                    ->color(fn ($state) => filled($state) ? 'info' : 'gray')
                    ->placeholder('كل الفروع')
                    ->sortable(),

                TextColumn::make('verification_status')
                    ->label('البريد')
                    ->state(fn ($record): bool => filled($record->email_verified_at))
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'موثّق' : 'غير موثّق')
                    ->color(fn ($state): string => $state ? 'success' : 'gray')
                    ->icon(fn ($state): string => $state ? 'heroicon-m-check-badge' : 'heroicon-m-envelope'),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('الدور')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
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
                ]),
            ])
            ->searchPlaceholder('ابحث بالاسم أو البريد الإلكتروني...')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا يوجد مستخدمون بعد')
            ->emptyStateDescription('أضف أول مستخدم وحدد دوره والفرع المسؤول عنه.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
