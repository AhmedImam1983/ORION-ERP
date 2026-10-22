<?php

namespace App\Filament\Admin\Resources\Invoices\Pages;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use App\Services\PurchaseReturnService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class PurchaseReturn extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected string $view = 'filament.admin.resources.invoices.pages.purchase-return';

    public array $returnQuantities = [];
    public string $reason = '';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        foreach ($this->getReturnRows() as $row) {
            $this->returnQuantities[$row['item_id']] = 0;
        }
    }

    public function getTitle(): string
    {
        return 'مرتجع شراء — ' . $this->record->invoice_number;
    }

    public function getReturnRows(): array
    {
        return app(PurchaseReturnService::class)
            ->returnableLines($this->record);
    }

    public function getReturnEstimate(): array
    {
        $rows = collect($this->getReturnRows())->keyBy('item_id');

        $subtotal = 0.0;
        $discount = 0.0;
        $tax      = 0.0;

        foreach ($this->returnQuantities as $itemId => $rawQty) {
            $qty = round((float) $rawQty, 3);

            if ($qty <= 0 || !$rows->has((int) $itemId)) {
                continue;
            }

            $row = $rows->get((int) $itemId);
            $qty = min($qty, (float) $row['available_quantity']);

            $lineSubtotal = round($qty * (float) $row['unit_price'], 2);
            $lineDiscount = round($lineSubtotal * ((float) $row['discount_percent'] / 100), 2);
            $afterDiscount = round($lineSubtotal - $lineDiscount, 2);
            $lineTax = round($afterDiscount * ((float) $row['tax_percent'] / 100), 2);

            $subtotal += $lineSubtotal;
            $discount += $lineDiscount;
            $tax      += $lineTax;
        }

        $invoiceDiscountPercent = (float) ($this->record->discount_percent ?? 0);
        $invoiceDiscount = round($subtotal * ($invoiceDiscountPercent / 100), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount + $invoiceDiscount, 2),
            'tax'      => round($tax, 2),
            'total'    => round($subtotal - $discount - $invoiceDiscount + $tax, 2),
        ];
    }

    public function submitReturn(): void
    {
        try {
            $return = app(PurchaseReturnService::class)->createFromPurchase(
                $this->record,
                $this->returnQuantities,
                $this->reason
            );

            Notification::make()
                ->title('تم إنشاء مرتجع الشراء')
                ->body($return->invoice_number . ' — ' . number_format((float) $return->total, 2) . ' ر.س')
                ->success()
                ->send();

            $this->redirect(
                InvoiceResource::getUrl('view', ['record' => $return])
            );
        } catch (\Throwable $e) {
            Notification::make()
                ->title('تعذر إنشاء مرتجع الشراء')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('original')
                ->label('العودة للفـاتورة الأصلية')
                ->icon('heroicon-o-arrow-right')
                ->color('gray')
                ->url(fn () => InvoiceResource::getUrl('view', ['record' => $this->record])),
        ];
    }
}
