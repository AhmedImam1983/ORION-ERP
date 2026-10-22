<?php

namespace App\Filament\Admin\Resources\PosSessions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PosSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return match (request()->query('quick')) {
                    'open' => $query->where('status', 'open'),
                    'closed' => $query->where('status', 'closed'),
                    'today' => $query->whereDate('opened_at', today()),
                    'refunds' => $query->where('total_refunds', '>', 0),
                    default => $query,
                };
            })
            ->columns([
                TextColumn::make('session_number')
                    ->label('رقم الجلسة')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('user.name')
                    ->label('الكاشير')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->badge()
                    ->color('info')
                    ->default('-'),

                TextColumn::make('opened_at')
                    ->label('وقت الفتح')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('closed_at')
                    ->label('وقت الإغلاق')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('مفتوحة الآن'),

                TextColumn::make('opening_cash')
                    ->label('رصيد الافتتاح')
                    ->money('SAR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_sales')
                    ->label('المبيعات')
                    ->money('SAR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('total_refunds')
                    ->label('المرتجعات')
                    ->money('SAR')
                    ->sortable()
                    ->color('danger'),

                TextColumn::make('transactions_count')
                    ->label('العمليات')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('closing_cash')
                    ->label('رصيد الإغلاق')
                    ->money('SAR')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'open' ? 'مفتوحة' : 'مغلقة')
                    ->color(fn ($state) => $state === 'open' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'open' => 'مفتوحة',
                        'closed' => 'مغلقة',
                    ]),

                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('تفاصيل الجلسة')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->color('gray')
                    ->extraAttributes(['title' => 'تفاصيل الجلسة']),
            ])
            ->toolbarActions([])
            ->defaultSort('opened_at', 'desc')
            ->emptyStateHeading('لا توجد جلسات كاشير بعد')
            ->emptyStateDescription('يتم فتح الجلسات من شاشة الكاشير POS')
            ->emptyStateIcon('heroicon-o-computer-desktop');
    }
}
