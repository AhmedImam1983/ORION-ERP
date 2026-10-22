<?php

namespace App\Filament\Admin\Resources\Salaries\Pages;

use App\Filament\Admin\Resources\Salaries\SalaryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalary extends EditRecord
{
    protected static string $resource = SalaryResource::class;

    protected string $view = 'filament.admin.resources.salaries.pages.edit-salary';

    public function getTitle(): string
    {
        $name = $this->getRecord()->employee?->name ?? 'الموظف';
        return 'تعديل راتب — ' . $name;
    }

    public function getBreadcrumb(): string
    {
        return 'تعديل';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('حذف سجل الراتب')
                ->icon('heroicon-o-trash'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ بيانات الراتب';
    }
}
