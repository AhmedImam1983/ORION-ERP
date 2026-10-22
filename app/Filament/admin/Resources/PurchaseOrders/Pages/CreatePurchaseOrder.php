<?php

namespace App\Filament\Admin\Resources\PurchaseOrders\Pages;

use App\Filament\Admin\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected string $view = 'filament.admin.resources.purchase-orders.pages.create-purchase-order';

    public function getSubheading(): ?string
    {
        return 'أنشئ طلب الشراء وحدد المورد والمخزن والأصناف والكميات والأسعار من شاشة واحدة';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء طلب الشراء بنجاح';
    }
}
