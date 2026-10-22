<?php

namespace App\Filament\Admin\Resources\ReceiptVouchers\Tables;

use App\Filament\Admin\Resources\ReceiptVouchers\ReceiptVoucherResource;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReceiptVouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('voucher_number')
                    ->label('رقم السند')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) =>
                        ReceiptVoucherResource::getUrl('view', ['record' => $record])
                    ),

                TextColumn::make('receipt_date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('purpose_label')
                    ->label('الغرض')
                    ->badge()
                    ->color(fn ($record) => $record->purpose === 'supplier_refund' ? 'warning' : 'success'),

                TextColumn::make('counterparty_label')
                    ->label('الطرف'),

                TextColumn::make('invoice.invoice_number')
                    ->label('الفاتورة')
                    ->searchable(),

                TextColumn::make('destination_label')
                    ->label('الخزينة / البنك'),

                TextColumn::make('payment_method_label')
                    ->label('طريقة التحصيل'),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR')
                    ->sortable(),

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

                TextColumn::make('journalEntry.entry_number')
                    ->label('القيد')
                    ->placeholder('—')
                    ->url(fn ($record) => $record->journal_entry_id
                        ? url('/admin/journal-entries/' . $record->journal_entry_id)
                        : null
                    ),
            ])
            ->recordUrl(fn ($record) =>
                ReceiptVoucherResource::getUrl('view', ['record' => $record])
            )
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),

                ...ReceiptVoucherResource::getRecordActions(),
            ])
            ->defaultSort('id', 'desc');
    }
}
