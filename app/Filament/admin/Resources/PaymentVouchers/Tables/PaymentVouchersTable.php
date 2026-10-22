<?php

namespace App\Filament\Admin\Resources\PaymentVouchers\Tables;

use App\Filament\Admin\Resources\PaymentVouchers\PaymentVoucherResource;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentVouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return match (request()->query('quick')) {
                    'today' => $query->whereDate('payment_date', today()),
                    'posted' => $query->where('status', 'posted'),
                    'reversed' => $query->where('status', 'reversed'),
                    'supplier' => $query->where('purpose', 'supplier_payment'),
                    'expense' => $query->where('purpose', 'direct_expense'),
                    'refund' => $query->where('purpose', 'customer_refund'),
                    default => $query,
                };
            })
            ->columns([
                TextColumn::make('voucher_number')
                    ->label('رقم السند')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary')
                    ->url(fn ($record) =>
                        PaymentVoucherResource::getUrl('view', ['record' => $record])
                    ),

                TextColumn::make('payment_date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('purpose_label')
                    ->label('الغرض')
                    ->badge()
                    ->color(fn ($record) => match ($record->purpose) {
                        'supplier_payment' => 'info',
                        'direct_expense' => 'warning',
                        'customer_refund' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('supplier.name')
                    ->label('المورد')
                    ->placeholder('—'),

                TextColumn::make('invoice.invoice_number')
                    ->label('الفاتورة')
                    ->placeholder('—'),

                TextColumn::make('expenseAccount.name')
                    ->label('حساب المصروف')
                    ->placeholder('—'),

                TextColumn::make('source_label')
                    ->label('الخزينة / البنك')
                    ->wrap(),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR')
                    ->sortable()
                    ->weight('bold')
                    ->color('danger'),

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
                    ->weight('bold')
                    ->color('info')
                    ->url(fn ($record) => $record->journal_entry_id
                        ? url('/admin/journal-entries/' . $record->journal_entry_id)
                        : null
                    ),
            ])
            ->recordUrl(fn ($record) =>
                PaymentVoucherResource::getUrl('view', ['record' => $record])
            )
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),

                // Step 6 reversal/void action remains exactly delegated to the Resource.
                ...PaymentVoucherResource::getRecordActions(),
            ])
            ->defaultSort('id', 'desc');
    }
}
