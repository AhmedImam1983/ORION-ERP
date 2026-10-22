<?php

namespace App\Filament\Admin\Resources\ReceiptVouchers\Pages;

use App\Filament\Admin\Resources\ReceiptVouchers\ReceiptVoucherResource;
use App\Services\ReceiptPostingService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateReceiptVoucher extends CreateRecord
{
    protected static string $resource = ReceiptVoucherResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(ReceiptPostingService::class)->post($data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء وترحيل سند القبض بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
