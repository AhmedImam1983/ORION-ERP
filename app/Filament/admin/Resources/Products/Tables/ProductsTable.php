<?php

namespace App\Filament\Admin\Resources\Products\Tables;

use App\Models\Warehouse;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return match (request()->query('quick')) {
                    'active' => $query->where('is_active', true),
                    'inactive' => $query->where('is_active', false),
                    'low_stock' => $query
                        ->where('is_active', true)
                        ->whereColumn('quantity', '<=', 'min_quantity'),
                    'unassigned' => $query
                        ->where('is_active', true)
                        ->whereDoesntHave('warehouseStocks'),
                    default => $query,
                };
            })
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular(),
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('اسم الصنف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('part_number')
                    ->label('رقم المرجع')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('brand.name')
                    ->label('العلامة التجارية')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('category.name')
                    ->label('المجموعة')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('warehouse.name')
                    ->label('المخزن الافتراضي')
                    ->searchable()
                    ->badge()
                    ->color('warning')
                    ->default('غير محدد'),
                TextColumn::make('automotive_location')
                    ->label('الموقع')
                    ->state(function ($record): string {
                        return $record->warehouseStocks
                            ->flatMap(fn ($stock) => $stock->locations)
                            ->pluck('location_code')
                            ->filter()
                            ->unique()
                            ->implode(', ');
                    })
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('warehouse_stocks_count')
                    ->label('عدد المخازن')
                    ->counts('warehouseStocks')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('الوحدة')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('إجمالي الرصيد')
                    ->numeric()
                    ->sortable()
                    ->color(fn($record) => $record->quantity <= $record->min_quantity ? 'danger' : 'success')
                    ->weight('bold'),
                TextColumn::make('min_quantity')
                    ->label('الحد الأدنى')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cost_price')
                    ->label('التكلفة')
                    ->money('SAR')
                    ->sortable(),
                TextColumn::make('sale_price')
                    ->label('سعر البيع')
                    ->money('SAR')
                    ->sortable(),
                TextColumn::make('barcode')
                    ->label('باركود')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('المجموعة')
                    ->relationship('category', 'name'),
                SelectFilter::make('stock_warehouse_id')
                    ->label('المخزن')
                    ->options(fn () => Warehouse::query()
                        ->where('is_active', true)
                        ->orderByDesc('is_default')
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        $warehouseId = $data['value'] ?? null;

                        return $query->when(
                            $warehouseId,
                            fn (Builder $q) => $q->whereHas(
                                'warehouseStocks',
                                fn (Builder $sq) => $sq->where('warehouse_id', $warehouseId)
                            )
                        );
                    }),
                TernaryFilter::make('is_active')
                    ->label('الحالة'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->color('gray')
                    ->extraAttributes(['title' => 'عرض الصنف']),
                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->color('primary')
                    ->extraAttributes(['title' => 'تعديل الصنف']),
                Action::make('print_barcode')
                    ->label('باركود')
                    ->icon('heroicon-o-qr-code')
                    ->iconButton()
                    ->color('warning')
                    ->extraAttributes(['title' => 'طباعة باركود'])
                    ->form([
                        \Filament\Forms\Components\TextInput::make('copies')
                            ->label('عدد النسخ')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(500)
                            ->required(),
                        \Filament\Forms\Components\Select::make('size')
                            ->label('حجم الاستيكر')
                            ->options([
                                'small'  => 'صغير — 4 في الصف',
                                'medium' => 'متوسط — 3 في الصف',
                                'large'  => 'كبير — 2 في الصف',
                            ])
                            ->default('medium')
                            ->required(),
                    ])
                    ->action(function ($record, array $data, $livewire) {
                        $url = route('barcode.print', [
                            'ids'    => $record->id,
                            'copies' => $data['copies'],
                            'size'   => $data['size'],
                        ]);
                        $livewire->dispatch('open-barcode-tab', url: $url);
                    })
                    ->modalHeading('طباعة باركود')
                    ->modalSubmitActionLabel('طباعة')
                    ->modalWidth('sm'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // طباعة باركود
                    BulkAction::make('print_barcodes')
                        ->label('طباعة باركود')
                        ->color('warning')
                        ->icon('heroicon-o-qr-code')
                        ->form([
                            \Filament\Forms\Components\TextInput::make('copies')
                                ->label('عدد النسخ لكل صنف')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->maxValue(500)
                                ->required(),
                            \Filament\Forms\Components\Select::make('size')
                                ->label('حجم الاستيكر')
                                ->options([
                                    'small'  => 'صغير — 4 في الصف',
                                    'medium' => 'متوسط — 3 في الصف',
                                    'large'  => 'كبير — 2 في الصف',
                                ])
                                ->default('medium')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data, $livewire) {
                            $ids = $records->pluck('id')->implode(',');
                            $url = route('barcode.print', [
                                'ids'    => $ids,
                                'copies' => $data['copies'],
                                'size'   => $data['size'],
                            ]);
                            $livewire->dispatch('open-barcode-tab', url: $url);
                        })
                        ->deselectRecordsAfterCompletion(),

                    // تعديل سعر البيع
                    BulkAction::make('bulk_update_price')
                        ->label('تعديل سعر البيع')
                        ->color('success')
                        ->icon('heroicon-o-currency-dollar')
                        ->form([
                            Select::make('price_type')
                                ->label('طريقة التعديل')
                                ->options([
                                    'fixed'      => 'سعر ثابت',
                                    'increase_p' => 'زيادة بنسبة %',
                                    'decrease_p' => 'تخفيض بنسبة %',
                                    'increase_v' => 'زيادة بمبلغ ثابت',
                                    'decrease_v' => 'تخفيض بمبلغ ثابت',
                                ])
                                ->required()
                                ->default('fixed')
                                ->live(),
                            TextInput::make('price_value')
                                ->label('القيمة')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->suffix(fn($get) => str_contains($get('price_type') ?? '', '_p') ? '%' : 'ر.س'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $type  = $data['price_type'];
                            $value = floatval($data['price_value']);
                            $count = 0;

                            foreach ($records as $product) {
                                $newPrice = match($type) {
                                    'fixed'      => $value,
                                    'increase_p' => $product->sale_price * (1 + $value / 100),
                                    'decrease_p' => $product->sale_price * (1 - $value / 100),
                                    'increase_v' => $product->sale_price + $value,
                                    'decrease_v' => max(0, $product->sale_price - $value),
                                    default      => $product->sale_price,
                                };

                                $product->update(['sale_price' => round($newPrice, 2)]);
                                $count++;
                            }

                            Notification::make()
                                ->title("تم تعديل سعر {$count} صنف")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // تعديل سعر التكلفة
                    BulkAction::make('bulk_update_cost')
                        ->label('تعديل سعر التكلفة')
                        ->color('info')
                        ->icon('heroicon-o-calculator')
                        ->form([
                            Select::make('cost_type')
                                ->label('طريقة التعديل')
                                ->options([
                                    'fixed'      => 'سعر ثابت',
                                    'increase_p' => 'زيادة بنسبة %',
                                    'decrease_p' => 'تخفيض بنسبة %',
                                    'increase_v' => 'زيادة بمبلغ ثابت',
                                    'decrease_v' => 'تخفيض بمبلغ ثابت',
                                ])
                                ->required()
                                ->default('fixed')
                                ->live(),
                            TextInput::make('cost_value')
                                ->label('القيمة')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->suffix(fn($get) => str_contains($get('cost_type') ?? '', '_p') ? '%' : 'ر.س'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $type  = $data['cost_type'];
                            $value = floatval($data['cost_value']);
                            $count = 0;

                            foreach ($records as $product) {
                                $newCost = match($type) {
                                    'fixed'      => $value,
                                    'increase_p' => $product->cost_price * (1 + $value / 100),
                                    'decrease_p' => $product->cost_price * (1 - $value / 100),
                                    'increase_v' => $product->cost_price + $value,
                                    'decrease_v' => max(0, $product->cost_price - $value),
                                    default      => $product->cost_price,
                                };

                                $product->update(['cost_price' => round($newCost, 2)]);
                                $count++;
                            }

                            Notification::make()
                                ->title("تم تعديل تكلفة {$count} صنف")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // تفعيل/تعطيل
                    BulkAction::make('bulk_activate')
                        ->label('تفعيل المحدد')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()->title('تم تفعيل ' . $records->count() . ' صنف')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulk_deactivate')
                        ->label('تعطيل المحدد')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()->title('تم تعطيل ' . $records->count() . ' صنف')->warning()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()->label('حذف المحدد'),
                    ForceDeleteBulkAction::make()->label('حذف نهائي'),
                    RestoreBulkAction::make()->label('استعادة'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد أصناف بعد')
            ->emptyStateDescription('ابدأ بإضافة صنف جديد')
            ->emptyStateIcon('heroicon-o-archive-box');
    }
}