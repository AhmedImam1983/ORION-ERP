<?php

namespace App\Filament\Admin\Resources\Branches\Pages;

use App\Filament\Admin\Resources\Branches\BranchResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    protected string $view = 'filament.admin.resources.branches.pages.create-branch';

    public function getTitle(): string
    {
        return 'إضافة فرع جديد';
    }

    public function getBreadcrumb(): string
    {
        return 'إضافة';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء الفرع بنجاح';
    }
}
