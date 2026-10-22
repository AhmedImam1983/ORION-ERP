<?php

namespace App\Filament\Admin\Resources\Categories\Pages;

use App\Filament\Admin\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected string $view = 'filament.admin.resources.categories.pages.create-category';

    public function getTitle(): string
    {
        return 'إضافة مجموعة جديدة';
    }

    public function getBreadcrumb(): string
    {
        return 'إضافة';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء المجموعة بنجاح';
    }
}
