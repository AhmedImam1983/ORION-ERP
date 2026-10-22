<?php

namespace App\Filament\Admin\Resources\PaymentVouchers\Pages;

use App\Filament\Admin\Resources\PaymentVouchers\PaymentVoucherResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentVoucher extends ViewRecord
{
    protected static string $resource = PaymentVoucherResource::class;

    protected string $view =
        'filament.admin.resources.payment-vouchers.pages.view-payment-voucher';

    public function getTitle(): string
    {
        return 'عرض ' . $this->getRecord()->voucher_number;
    }

    public function getSubheading(): ?string
    {
        return 'تفاصيل سند الصرف والقيد المحاسبي وسجل العكس إن وجد';
    }
}
