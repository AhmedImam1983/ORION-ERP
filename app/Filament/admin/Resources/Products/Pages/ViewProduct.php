<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use App\Models\Warehouse;
use App\Services\WarehouseInventoryService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            Action::make('assign_cutover_stock')
                ->label('تحديد مخزن الرصيد الحالي')
                ->icon('heroicon-o-map-pin')
                ->color('info')
                ->visible(fn () => !$record->warehouseStocks()->exists())
                ->form([
                    Select::make('warehouse_id')
                        ->label('المخزن')
                        ->options(fn () => Warehouse::query()
                            ->where('is_active', true)
                            ->orderByDesc('is_default')
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                ])
                ->requiresConfirmation()
                ->modalDescription('سيتم تثبيت الرصيد الحالي للصنف كرصيد Cutover في المخزن الذي تختاره، بدون إنشاء حركة تاريخية أو تغيير إجمالي الكمية.')
                ->action(function (array $data) use ($record) {
                    app(WarehouseInventoryService::class)->assignLegacyBalance(
                        $record,
                        (int) $data['warehouse_id'],
                    );

                    Notification::make()
                        ->title('تم تثبيت رصيد الصنف في المخزن المحدد')
                        ->success()
                        ->send();

                    $record->refresh();
                }),

            Action::make('warehouse_transfer')
                ->label('تحويل بين المخازن')
                ->icon('heroicon-o-arrows-right-left')
                ->color('warning')
                ->visible(fn () => $record->warehouseStocks()->where('quantity', '>', 0)->exists())
                ->form([
                    Select::make('from_warehouse_id')
                        ->label('من مخزن')
                        ->options(fn () => $record->warehouseStocks()
                            ->with('warehouse')
                            ->where('quantity', '>', 0)
                            ->get()
                            ->mapWithKeys(fn ($stock) => [
                                $stock->warehouse_id => ($stock->warehouse?->name ?? ('مخزن #' . $stock->warehouse_id)) .
                                    ' — رصيد ' . number_format((float) $stock->quantity, 3),
                            ]))
                        ->required()
                        ->searchable(),
                    Select::make('to_warehouse_id')
                        ->label('إلى مخزن')
                        ->options(fn () => Warehouse::query()
                            ->where('is_active', true)
                            ->orderByDesc('is_default')
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    TextInput::make('quantity')
                        ->label('الكمية')
                        ->numeric()
                        ->minValue(0.001)
                        ->step(0.001)
                        ->required(),
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(2),
                ])
                ->action(function (array $data) use ($record) {
                    $reference = app(WarehouseInventoryService::class)->transfer(
                        $record,
                        (int) $data['from_warehouse_id'],
                        (int) $data['to_warehouse_id'],
                        (float) $data['quantity'],
                        $data['notes'] ?? null,
                    );

                    Notification::make()
                        ->title('تم التحويل المخزني بنجاح')
                        ->body('المرجع: ' . $reference)
                        ->success()
                        ->send();

                    $record->refresh();
                }),
            EditAction::make(),
        ];
    }
}
