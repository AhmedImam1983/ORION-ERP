<?php

namespace App\Filament\Admin\Resources\Invoices\Tables;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return match (request()->query('quick')) {
                    'sale' => $query->where('type', 'sale'),
                    'purchase' => $query->where('type', 'purchase'),
                    'sale_return' => $query->where('type', 'sale_return'),
                    'purchase_return' => $query->where('type', 'purchase_return'),
                    'credit' => $query
                        ->whereIn('payment_type', ['credit', 'installment'])
                        ->where('remaining_amount', '>', 0)
                        ->where('status', '!=', 'cancelled'),
                    'paid' => $query
                        // Return documents are settlements/credits, not paid invoices.
                        ->whereNotIn('type', ['sale_return', 'purchase_return'])
                        ->where('status', '!=', 'cancelled')
                        ->where(function ($q) {
                            $q->where('status', 'paid')
                              ->orWhere('remaining_amount', '<=', 0);
                        }),
                    'overdue' => $query
                        ->whereNotNull('due_date')
                        ->whereDate('due_date', '<', today())
                        ->where('remaining_amount', '>', 0)
                        ->whereNotIn('status', ['paid', 'cancelled']),
                    default => $query,
                };
            })
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'sale'            => 'بيع',
                        'purchase'        => 'شراء',
                        'sale_return'     => 'مرتجع بيع',
                        'purchase_return' => 'مرتجع شراء',
                        default           => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'sale'            => 'success',
                        'purchase'        => 'primary',
                        'sale_return'     => 'warning',
                        'purchase_return' => 'danger',
                        default           => 'gray',
                    }),

                TextColumn::make('customer.name')
                    ->label('العميل')
                    ->searchable()
                    ->default('-'),

                TextColumn::make('supplier.name')
                    ->label('المورد')
                    ->searchable()
                    ->default('-'),

                TextColumn::make('invoice_date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('الاستحقاق')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->sortable()
                    ->color(fn($record) => self::isOverdue($record) ? 'danger' : 'gray')
                    ->weight(fn($record) => self::isOverdue($record) ? 'bold' : null),

                TextColumn::make('payment_type')
                    ->label('الدفع / التسوية')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        if (in_array($record?->type, ['sale_return', 'purchase_return'], true)) {
                            return 'تسوية مرتجع';
                        }

                        return match($state) {
                            'cash'        => 'نقدي',
                            'credit'      => 'آجل',
                            'installment' => 'تقسيط',
                            default       => $state,
                        };
                    })
                    ->color(function ($state, $record) {
                        if (in_array($record?->type, ['sale_return', 'purchase_return'], true)) {
                            return 'warning';
                        }

                        return match($state) {
                            'cash'        => 'success',
                            'credit'      => 'warning',
                            'installment' => 'info',
                            default       => 'gray',
                        };
                    }),

                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('SAR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('paid_amount')
                    ->label('المدفوع')
                    ->money('SAR')
                    ->sortable()
                    ->color('success'),

                TextColumn::make('remaining_amount')
                    ->label('المتبقي')
                    ->money('SAR')
                    ->sortable()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'draft'     => 'مسودة',
                        'confirmed' => 'مؤكدة',
                        'paid'      => 'مدفوعة',
                        'partial'   => 'جزئي',
                        'cancelled' => 'ملغية',
                        default     => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'paid'      => 'success',
                        'confirmed' => 'primary',
                        'partial'   => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'sale'            => 'بيع',
                        'purchase'        => 'شراء',
                        'sale_return'     => 'مرتجع بيع',
                        'purchase_return' => 'مرتجع شراء',
                    ]),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft'     => 'مسودة',
                        'confirmed' => 'مؤكدة',
                        'paid'      => 'مدفوعة',
                        'partial'   => 'جزئي',
                        'cancelled' => 'ملغية',
                    ]),
                SelectFilter::make('payment_type')
                    ->label('طريقة الدفع')
                    ->options([
                        'cash'        => 'نقدي',
                        'credit'      => 'آجل',
                        'installment' => 'تقسيط',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('عرض الفاتورة'),
                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->tooltip('تعديل الفاتورة'),

                /*
                 * Step 8 — Invoice Reversal
                 * نعرض فقط Action عكس الفاتورة المعرفة مركزياً داخل
                 * InvoiceResource حتى لا نكرر منطق العكس داخل الجدول.
                 */
                ...array_values(array_filter(
                    InvoiceResource::getRecordActions(),
                    fn ($action) => in_array($action->getName(), ['purchase_return', 'sale_return', 'reverse_invoice'], true)
                )),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                    ForceDeleteBulkAction::make()->label('حذف نهائي'),
                    RestoreBulkAction::make()->label('استعادة'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد فواتير بعد')
            ->emptyStateDescription('ابدأ بإنشاء فاتورة جديدة')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    private static function isOverdue($record): bool
    {
        if (!$record?->due_date || (float) $record->remaining_amount <= 0 || in_array($record->status, ['paid', 'cancelled'], true)) {
            return false;
        }

        try {
            return \Illuminate\Support\Carbon::parse($record->due_date)->startOfDay()->lt(today());
        } catch (\Throwable) {
            return false;
        }
    }
}
