<?php

namespace App\Filament\Admin\Resources\FinancialTransfers\Pages;

use App\Filament\Admin\Resources\FinancialTransfers\FinancialTransferResource;
use Filament\Resources\Pages\ViewRecord;

class ViewFinancialTransfer extends ViewRecord
{
    protected static string $resource = FinancialTransferResource::class;

    protected string $view =
        'filament.admin.resources.financial-transfers.pages.view-financial-transfer';

    public function getTitle(): string
    {
        return 'عرض ' . $this->getRecord()->transfer_number;
    }

    public function getSubheading(): ?string
    {
        return 'تفاصيل التحويل والقيد المحاسبي المرتبط وسجل العكس إن وجد';
    }
}
