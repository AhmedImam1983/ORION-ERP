<?php

namespace App\Filament\Admin\Resources\FixedAssets\Pages;

use App\Filament\Admin\Resources\FixedAssets\FixedAssetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFixedAsset extends EditRecord
{
    protected static string $resource = FixedAssetResource::class;

    protected string $view = 'filament.admin.resources.fixed-assets.pages.edit-fixed-asset';

    public function getTitle(): string
    {
        return 'تعديل الأصل — ' . $this->getRecord()->name;
    }

    public function getBreadcrumb(): string
    {
        return 'تعديل';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('حذف الأصل')
                ->icon('heroicon-o-trash'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ بيانات الأصل';
    }
}
