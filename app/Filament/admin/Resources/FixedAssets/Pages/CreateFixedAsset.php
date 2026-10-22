<?php

namespace App\Filament\Admin\Resources\FixedAssets\Pages;

use App\Filament\Admin\Resources\FixedAssets\FixedAssetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFixedAsset extends CreateRecord
{
    protected static string $resource = FixedAssetResource::class;

    protected string $view = 'filament.admin.resources.fixed-assets.pages.create-fixed-asset';

    public function getTitle(): string
    {
        return 'إضافة أصل ثابت جديد';
    }

    public function getBreadcrumb(): string
    {
        return 'إضافة';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء الأصل الثابت بنجاح';
    }
}
