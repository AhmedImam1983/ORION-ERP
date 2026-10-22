<?php

namespace App\Filament\Admin\Resources\PaymentVouchers\Pages;

use App\Filament\Admin\Resources\PaymentVouchers\PaymentVoucherResource;
use App\Services\PaymentPostingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePaymentVoucher extends CreateRecord
{
    protected static string $resource = PaymentVoucherResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(PaymentPostingService::class)->post($data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء وترحيل سند الصرف بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
