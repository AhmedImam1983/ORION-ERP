<?php

namespace App\Filament\Admin\Resources\StockCounts\Pages;

use App\Filament\Admin\Resources\StockCounts\StockCountResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewStockCount extends ViewRecord
{
    protected static string $resource = StockCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('load_products')
                ->label('تحميل الأصناف')
                ->color('info')
                ->visible(fn() => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading('تحميل أصناف المخزون')
                ->modalDescription('سيتم تحميل كل الأصناف النشطة من المخزن المحدد، ثم الانتقال لإدخال الكميات الفعلية.')
                ->action(function () {
                    $this->record->loadProducts();
                    $this->record->refresh();

                    $count = $this->record->items()->count();

                    if ($count === 0) {
                        Notification::make()
                            ->title('لم يتم العثور على أصناف للجرد')
                            ->danger()
                            ->send();
                        return;
                    }

                    Notification::make()
                        ->title('تم تحميل ' . $count . ' صنف. أدخل الكميات الفعلية قبل الاعتماد.')
                        ->success()
                        ->send();

                    $this->redirect(StockCountResource::getUrl('edit', ['record' => $this->record]));
                }),

            Action::make('approve')
                ->label('اعتماد الجرد')
                ->color('success')
                ->visible(fn() => $this->record->status === 'in_progress')
                ->requiresConfirmation()
                ->modalHeading('اعتماد الجرد المخزني')
                ->modalDescription('لن يتم الاعتماد إلا بعد إدخال الكمية الفعلية لكل صنف. بعد الاعتماد سيتم تطبيق الفروقات على المخزون.')
                ->modalSubmitActionLabel('اعتماد')
                ->action(function () {
                    $this->record->refresh();

                    $itemsCount = $this->record->items()->count();
                    $uncountedCount = $this->record->items()
                        ->whereNull('actual_quantity')
                        ->count();

                    if ($itemsCount === 0) {
                        Notification::make()
                            ->title('لا يمكن اعتماد جرد بدون أصناف')
                            ->danger()
                            ->send();
                        return;
                    }

                    if ($uncountedCount > 0) {
                        Notification::make()
                            ->title('لا يمكن اعتماد الجرد')
                            ->body('متبقي ' . $uncountedCount . ' صنف لم يتم إدخال كميته الفعلية.')
                            ->danger()
                            ->persistent()
                            ->send();
                        return;
                    }

                    try {
                        $this->record->approve();
                        Notification::make()->title('تم اعتماد الجرد')->success()->send();
                        $this->record->refresh();
                    } catch (\Exception $e) {
                        Notification::make()->title('خطأ: ' . $e->getMessage())->danger()->send();
                    }
                }),

            EditAction::make()
                ->label(fn() => $this->record->status === 'in_progress' ? 'إدخال الكميات الفعلية' : 'تعديل')
                ->visible(fn() => in_array($this->record->status, ['draft', 'in_progress'], true)),
        ];
    }

    public function updateActualQuantity(int $itemId, ?string $qty): void
    {
        $item = $this->record->items()->find($itemId);

        if (!$item) {
            return;
        }

        // القيمة الفارغة تعني "لم يُجرد"، وليست صفراً.
        if ($qty === null || trim($qty) === '') {
            $item->update([
                'actual_quantity'  => null,
                'difference'       => null,
                'difference_value' => null,
            ]);
            return;
        }

        if (!is_numeric($qty) || (float) $qty < 0) {
            Notification::make()
                ->title('الكمية الفعلية يجب أن تكون رقماً أكبر من أو يساوي صفر')
                ->danger()
                ->send();
            return;
        }

        $actualQty = (float) $qty;
        $diff = $actualQty - (float) $item->system_quantity;
        $diffValue = $diff * (float) $item->cost_price;

        $item->update([
            'actual_quantity'  => $actualQty,
            'difference'       => $diff,
            'difference_value' => $diffValue,
        ]);
    }
}