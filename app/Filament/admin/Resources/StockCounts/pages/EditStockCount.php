<?php

namespace App\Filament\Admin\Resources\StockCounts\Pages;

use App\Filament\Admin\Resources\StockCounts\StockCountResource;
use App\Models\StockCountItem;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditStockCount extends EditRecord
{
    protected static string $resource = StockCountResource::class;
    protected string $view = 'filament.admin.pages.edit-stock-count';

    public array $quantities = [];
    public bool $countConfirmed = false;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        foreach ($this->record->items()->orderBy('id')->get() as $item) {
            // وضع إدخال الفروقات: الكمية تبدأ برصيد النظام، والمستخدم يغير الاستثناءات فقط.
            $this->quantities[$item->id] = $item->actual_quantity !== null
                ? (float) $item->actual_quantity
                : (float) $item->system_quantity;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save_quantities')
                ->label('حفظ الجرد')
                ->color('success')
                ->visible(fn() => in_array($this->record->status, ['draft', 'in_progress'], true))
                ->action('saveQuantities'),

            Action::make('approve')
                ->label('اعتماد الجرد')
                ->color('warning')
                ->visible(fn() => $this->record->status === 'in_progress')
                ->requiresConfirmation()
                ->modalHeading('اعتماد الجرد')
                ->modalDescription('سيتم اعتماد الكميات الظاهرة وتطبيق الفروقات على المخزون. يجب تأكيد أن الأصناف غير المعدلة تم عدها فعليًا وكانت مطابقة للنظام.')
                ->modalSubmitActionLabel('اعتماد')
                ->action(function () {
                    if (!$this->countConfirmed) {
                        Notification::make()
                            ->title('تأكيد العد مطلوب')
                            ->body('فعّل مربع التأكيد أسفل شاشة الجرد قبل الاعتماد.')
                            ->danger()
                            ->persistent()
                            ->send();
                        return;
                    }

                    if (!$this->saveQuantities(false)) {
                        return;
                    }

                    $this->record->refresh();

                    if ($this->record->items()->count() === 0) {
                        Notification::make()->title('لا يمكن اعتماد جرد بدون أصناف')->danger()->send();
                        return;
                    }

                    try {
                        $this->record->refresh()->approve();
                        Notification::make()->title('تم اعتماد الجرد')->success()->send();
                        $this->redirect(StockCountResource::getUrl('view', ['record' => $this->record]));
                    } catch (\Exception $e) {
                        Notification::make()->title('خطأ: ' . $e->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    public function resetToSystemQuantities(): void
    {
        foreach ($this->record->items()->get() as $item) {
            $this->quantities[$item->id] = (float) $item->system_quantity;
        }

        $this->countConfirmed = false;

        Notification::make()
            ->title('تمت إعادة القيم لرصيد النظام')
            ->body('لن يتم الحفظ في قاعدة البيانات إلا عند الضغط على حفظ الجرد.')
            ->info()
            ->send();
    }

    public function saveQuantities(bool $notify = true): bool
    {
        foreach ($this->quantities as $itemId => $qty) {
            if ($qty === '' || $qty === null || !is_numeric($qty) || (float) $qty < 0) {
                Notification::make()
                    ->title('خطأ في الكميات')
                    ->body('كل كمية فعلية يجب أن تكون رقمًا أكبر من أو يساوي صفر.')
                    ->danger()
                    ->send();
                return false;
            }

            $item = StockCountItem::find($itemId);
            if (!$item || $item->stock_count_id !== $this->record->id) {
                continue;
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

        $this->record->refresh();

        if ($notify) {
            $total = $this->record->items()->count();
            $varianceCount = $this->record->items()->where('difference', '!=', 0)->count();
            $matchedCount = max(0, $total - $varianceCount);

            Notification::make()
                ->title('تم حفظ الجرد')
                ->body('مطابق: ' . $matchedCount . ' صنف · فروقات: ' . $varianceCount . ' صنف.')
                ->success()
                ->send();
        }

        return true;
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
