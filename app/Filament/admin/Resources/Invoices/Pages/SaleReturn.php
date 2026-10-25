<?php

namespace App\Filament\Admin\Resources\Invoices\Pages;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Services\SaleReturnService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class SaleReturn extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;
    protected string $view = 'filament.admin.Resources.invoices.pages.sale-return';

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
        return 'مرتجع بيع — ' . $this->record->invoice_number;
    }

    public function getReturnRows(): array
    {
        return app(SaleReturnService::class)->returnableLines($this->record);
    }

    public function getReturnEstimate(): array
    {
        return app(SaleReturnService::class)->estimateReturn($this->record, $this->returnQuantities);
    }

    public function submitReturn(): void
    {
        try {
            $return = app(SaleReturnService::class)->createFromSale(
                $this->record,
                $this->returnQuantities,
                $this->reason
            );

            Notification::make()
                ->title('تم إنشاء مرتجع البيع')
                ->body($return->invoice_number . ' — ' . number_format((float) $return->total, 2) . ' ر.س')
                ->success()
                ->send();

            $this->redirect(InvoiceResource::getUrl('view', ['record' => $return]));
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('تعذر إنشاء مرتجع البيع')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('original')
                ->label('العودة للفاتورة الأصلية')
                ->icon('heroicon-o-arrow-right')
                ->color('gray')
                ->url(fn () => InvoiceResource::getUrl('view', ['record' => $this->record])),
        ];
    }
}
