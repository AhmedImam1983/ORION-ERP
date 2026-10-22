<?php

namespace App\Filament\Admin\Resources\FinancialTransfers\Tables;

use App\Filament\Admin\Resources\FinancialTransfers\FinancialTransferResource;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinancialTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return match (request()->query('quick')) {
                    'today' => $query->whereDate('transfer_date', today()),

                    'posted' => $query->where('status', 'posted'),

                    'reversed' => $query->where('status', 'reversed'),

                    'treasury_treasury' => $query
                        ->where('from_type', 'treasury')
                        ->where('to_type', 'treasury'),

                    'treasury_bank' => $query->where(function (Builder $routeQuery): void {
                        $routeQuery
                            ->where(function (Builder $q): void {
                                $q->where('from_type', 'treasury')
                                    ->where('to_type', 'bank');
                            })
                            ->orWhere(function (Builder $q): void {
                                $q->where('from_type', 'bank')
                                    ->where('to_type', 'treasury');
                            });
                    }),

                    'bank_bank' => $query
                        ->where('from_type', 'bank')
                        ->where('to_type', 'bank'),

                    default => $query,
                };
            })
            ->columns([
                TextColumn::make('transfer_number')
                    ->label('رقم التحويل')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->copyable()
                    ->url(
                        fn ($record) => FinancialTransferResource::getUrl(
                            'view',
                            ['record' => $record]
                        )
                    ),

                TextColumn::make('transfer_date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('route_label')
                    ->label('المسار')
                    ->badge()
                    ->color('info'),

                TextColumn::make('from_label')
                    ->label('من')
                    ->wrap(),

                TextColumn::make('to_label')
                    ->label('إلى')
                    ->wrap(),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('journalEntry.entry_number')
                    ->label('القيد')
                    ->placeholder('—')
                    ->weight('bold')
                    ->color('info')
                    ->url(
                        fn ($record) => $record->journal_entry_id
                            ? url('/admin/journal-entries/' . $record->journal_entry_id)
                            : null
                    ),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'posted'   => 'مرحّل',
                        'reversed' => 'معكوس',
                        default    => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'posted'   => 'success',
                        'reversed' => 'danger',
                        default    => 'gray',
                    }),
            ])

            // Keep the existing read-only details workflow.
            ->recordUrl(
                fn ($record) => FinancialTransferResource::getUrl(
                    'view',
                    ['record' => $record]
                )
            )

            // Keep Step 6 reversal action exactly as-is.
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),

                ...FinancialTransferResource::getRecordActions(),
            ])

            ->defaultSort('id', 'desc');
    }
}
