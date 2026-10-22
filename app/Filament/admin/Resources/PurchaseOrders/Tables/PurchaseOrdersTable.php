<?php

namespace App\Filament\Admin\Resources\PurchaseOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return match (request()->query('quick')) {
                    'draft' => $query->where('status', 'draft'),
                    'sent' => $query->where('status', 'sent'),
                    'confirmed' => $query->where('status', 'confirmed'),
                    'partial' => $query->where('status', 'partial'),
                    'received' => $query->where('status', 'received'),
                    'open' => $query->whereIn('status', ['draft', 'sent', 'confirmed', 'partial']),
                    'overdue' => $query
                        ->whereNotNull('expected_date')
                        ->whereDate('expected_date', '<', today())
                        ->whereNotIn('status', ['received', 'cancelled']),
                    default => $query,
                };
            })
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('supplier.name')
                    ->label('المورد')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('items_count')
                    ->label('الأصناف')
                    ->counts('items')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('order_date')
                    ->label('تاريخ الطلب')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('expected_date')
                    ->label('الاستلام المتوقع')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('-')
                    ->color(fn ($record) => self::isOverdue($record) ? 'danger' : 'gray')
                    ->weight(fn ($record) => self::isOverdue($record) ? 'bold' : null),

                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('SAR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft'     => 'مسودة',
                        'sent'      => 'مُرسل للمورد',
                        'confirmed' => 'مؤكد',
                        'partial'   => 'استلام جزئي',
                        'received'  => 'مستلم بالكامل',
                        'cancelled' => 'ملغي',
                        default     => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'received'  => 'success',
                        'confirmed' => 'info',
                        'sent'      => 'warning',
                        'partial'   => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('warehouse.name')
                    ->label('المخزن')
                    ->badge()
                    ->color('warning')
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft'     => 'مسودة',
                        'sent'      => 'مُرسل للمورد',
                        'confirmed' => 'مؤكد',
                        'partial'   => 'استلام جزئي',
                        'received'  => 'مستلم بالكامل',
                        'cancelled' => 'ملغي',
                    ]),

                SelectFilter::make('supplier_id')
                    ->label('المورد')
                    ->relationship('supplier', 'name'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('فتح')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->tooltip('فتح / تعديل الطلب'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد طلبات شراء بعد')
            ->emptyStateDescription('ابدأ بإنشاء طلب شراء جديد')
            ->emptyStateIcon('heroicon-o-shopping-cart');
    }

    private static function isOverdue($record): bool
    {
        if (!$record?->expected_date || in_array($record->status, ['received', 'cancelled'], true)) {
            return false;
        }

        try {
            return \Illuminate\Support\Carbon::parse($record->expected_date)->startOfDay()->lt(today());
        } catch (\Throwable) {
            return false;
        }
    }
}
