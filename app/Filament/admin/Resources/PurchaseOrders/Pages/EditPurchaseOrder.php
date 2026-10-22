<?php

namespace App\Filament\Admin\Resources\PurchaseOrders\Pages;

use App\Filament\Admin\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected string $view = 'filament.admin.resources.purchase-orders.pages.edit-purchase-order';

    public function getSubheading(): ?string
    {
        return 'راجع بيانات الطلب والأصناف وحالة التوريد والاستلام';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => in_array($this->record->status, ['draft', 'cancelled'], true)),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث طلب الشراء بنجاح';
    }
}
