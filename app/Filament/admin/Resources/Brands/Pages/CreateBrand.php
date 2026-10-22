<?php

namespace App\Filament\Admin\Resources\Brands\Pages;

use App\Filament\Admin\Resources\Brands\BrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;

    public function getTitle(): string
    {
        return 'إضافة علامة تجارية';
    }

    public function getBreadcrumb(): string
    {
        return 'إضافة';
    }

    public function getCancelRedirectUrl(): string
    {
        return BrandResource::getUrl('index');
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('حفظ')
            ->submit('create');
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('حفظ وإضافة أخرى')
            ->submit('createAnother');
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('إلغاء');
    }
}
