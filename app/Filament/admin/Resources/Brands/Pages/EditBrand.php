<?php

namespace App\Filament\Admin\Resources\Brands\Pages;

use App\Filament\Admin\Resources\Brands\BrandResource;
use Filament\Resources\Pages\EditRecord;

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;

    public function getTitle(): string
    {
        return 'تعديل علامة تجارية';
    }

    public function getBreadcrumb(): string
    {
        return 'تعديل';
    }

    public function getCancelRedirectUrl(): string
    {
        return BrandResource::getUrl('index');
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('حفظ')
            ->submit('save');
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('إلغاء');
    }
}
