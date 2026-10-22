<?php

namespace App\Filament\Admin\Resources\ReceiptVouchers\Pages;

use App\Filament\Admin\Resources\ReceiptVouchers\ReceiptVoucherResource;
use Filament\Resources\Pages\ViewRecord;

class ViewReceiptVoucher extends ViewRecord
{
    protected static string $resource = ReceiptVoucherResource::class;

    protected string $view =
        'filament.admin.resources.receipt-vouchers.pages.view-receipt-voucher';

    public function getTitle(): string
    {
        return 'عرض ' . $this->getRecord()->voucher_number;
    }

    public function getSubheading(): ?string
    {
        return 'تفاصيل سند القبض والقيد المحاسبي وسجل العكس إن وجد';
    }
}
