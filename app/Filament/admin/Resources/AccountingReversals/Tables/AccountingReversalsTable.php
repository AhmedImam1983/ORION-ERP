<?php

namespace App\Filament\Admin\Resources\AccountingReversals\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccountingReversalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return match (request()->query('quick')) {
                    'today' => $query->whereDate('reversal_date', today()),
                    'receipt' => $query->where('source_type', 'receipt_voucher'),
                    'payment' => $query->where('source_type', 'payment_voucher'),
                    'transfer' => $query->where('source_type', 'financial_transfer'),
                    default => $query,
                };
            })
            ->columns([
                TextColumn::make('reversal_number')
                    ->label('رقم العكس')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('danger'),

                TextColumn::make('reversal_date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('source_type_label')
                    ->label('نوع المستند')
                    ->badge()
                    ->color(fn ($record) => match ($record->source_type) {
                        'receipt_voucher' => 'success',
                        'payment_voucher' => 'warning',
                        'financial_transfer' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('source_number')
                    ->label('المستند الأصلي')
                    ->searchable()
                    ->weight('bold')
                    ->color('primary')
                    ->url(function ($record): ?string {
                        return match ($record->source_type) {
                            'receipt_voucher' =>
                                $record->source_id
                                    ? url('/admin/receipt-vouchers/' . $record->source_id)
                                    : null,

                            'payment_voucher' =>
                                $record->source_id
                                    ? url('/admin/payment-vouchers/' . $record->source_id)
                                    : null,

                            'financial_transfer' =>
                                $record->source_id
                                    ? url('/admin/financial-transfers/' . $record->source_id)
                                    : null,

                            default => null,
                        };
                    }),

                TextColumn::make('originalJournalEntry.entry_number')
                    ->label('القيد الأصلي')
                    ->placeholder('—')
                    ->weight('bold')
                    ->color('info')
                    ->url(fn ($record) =>
                        $record->original_journal_entry_id
                            ? url('/admin/journal-entries/' . $record->original_journal_entry_id)
                            : null
                    ),

                TextColumn::make('reversalJournalEntry.entry_number')
                    ->label('القيد العكسي')
                    ->placeholder('—')
                    ->weight('bold')
                    ->color('danger')
                    ->url(fn ($record) =>
                        $record->reversal_journal_entry_id
                            ? url('/admin/journal-entries/' . $record->reversal_journal_entry_id)
                            : null
                    ),

                TextColumn::make('reason')
                    ->label('سبب العكس')
                    ->limit(48)
                    ->tooltip(fn ($record) => $record->reason)
                    ->wrap(),

                TextColumn::make('createdBy.name')
                    ->label('بواسطة')
                    ->placeholder('System'),
            ])
            ->defaultSort('id', 'desc');
    }
}
