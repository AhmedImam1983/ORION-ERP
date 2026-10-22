<?php

namespace App\Filament\Admin\Resources\FinancialTransfers\Pages;

use App\Filament\Admin\Resources\FinancialTransfers\FinancialTransferResource;
use App\Services\FinancialTransferPostingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFinancialTransfer extends CreateRecord
{
    protected static string $resource = FinancialTransferResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(FinancialTransferPostingService::class)->post($data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء وترحيل التحويل المالي بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
