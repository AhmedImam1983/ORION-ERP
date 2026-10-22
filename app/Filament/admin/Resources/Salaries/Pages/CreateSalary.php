<?php

namespace App\Filament\Admin\Resources\Salaries\Pages;

use App\Filament\Admin\Resources\Salaries\SalaryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalary extends CreateRecord
{
    protected static string $resource = SalaryResource::class;

    protected string $view = 'filament.admin.resources.salaries.pages.create-salary';

    public function getTitle(): string
    {
        return 'إضافة راتب جديد';
    }

    public function getBreadcrumb(): string
    {
        return 'إضافة';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء سجل الراتب بنجاح';
    }
}
