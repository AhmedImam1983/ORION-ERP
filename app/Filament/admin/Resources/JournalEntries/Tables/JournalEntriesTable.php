<?php

namespace App\Filament\Admin\Resources\JournalEntries\Tables;

use App\Models\JournalEntry;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_number')
                    ->label('رقم القيد')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('entry_date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('source_label')
                    ->label('المصدر')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'فاتورة'        => 'info',
                        'تكلفة مبيعات'  => 'warning',
                        'مصروف'         => 'danger',
                        'رواتب'         => 'warning',
                        'رصيد افتتاحي'  => 'gray',
                        'مرتجع بيع'     => 'warning',
                        'مشتريات'       => 'primary',
                        'حركة مالية'    => 'success',
                        default         => 'gray',
                    }),

                TextColumn::make('reference')
                    ->label('المرجع')
                    ->searchable()
                    ->default('-')
                    ->copyable(),

                TextColumn::make('description')
                    ->label('البيان')
                    ->limit(46)
                    ->searchable(),

                TextColumn::make('total_debit')
                    ->label('المدين')
                    ->money('SAR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('total_credit')
                    ->label('الدائن')
                    ->money('SAR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('balance_difference')
                    ->label('التوازن')
                    ->formatStateUsing(fn ($state) => abs((float) $state) < 0.01
                        ? 'متوازن'
                        : 'فرق ' . number_format(abs((float) $state), 2) . ' ر.س')
                    ->badge()
                    ->color(fn ($state) => abs((float) $state) < 0.01 ? 'success' : 'danger'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft'    => 'مسودة',
                        'posted'   => 'مرحّل',
                        'reversed' => 'معكوس',
                        default    => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'posted'   => 'success',
                        'draft'    => 'warning',
                        'reversed' => 'danger',
                        default    => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft'    => 'مسودة',
                        'posted'   => 'مرحّل',
                        'reversed' => 'معكوس',
                    ]),

                Filter::make('today')
                    ->label('قيود اليوم')
                    ->query(fn (Builder $query): Builder => $query->whereDate('entry_date', today())),

                Filter::make('unbalanced')
                    ->label('غير متوازن')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereRaw('ABS(COALESCE(total_debit, 0) - COALESCE(total_credit, 0)) >= 0.01')
                    ),

                Filter::make('invoices')
                    ->label('فواتير')
                    ->query(fn (Builder $query): Builder =>
                        $query->where(function (Builder $q): void {
                            $q->where('reference', 'like', 'INV-%')
                                ->orWhere('reference', 'like', 'SRT-%')
                                ->orWhere('reference', 'like', 'PUR-%');
                        })
                    ),

                Filter::make('expenses')
                    ->label('مصروفات')
                    ->query(fn (Builder $query): Builder => $query->where('reference', 'like', 'EXP-%')),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()
                    ->label('تعديل')
                    ->visible(fn (JournalEntry $record): bool => $record->status === 'draft'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد قيود بعد')
            ->emptyStateDescription('أنشئ قيداً يدوياً أو راجع القيود الناتجة من العمليات')
            ->emptyStateIcon('heroicon-o-book-open');
    }
}
