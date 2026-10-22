<?php

namespace App\Filament\Admin\Resources\Employees\Pages;

use App\Filament\Admin\Resources\Employees\EmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected string $view = 'filament.admin.resources.employees.pages.edit-employee';

    public function getTitle(): string
    {
        return 'تعديل الموظف — ' . $this->getRecord()->name;
    }

    public function getBreadcrumb(): string
    {
        return 'تعديل';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('عرض الموظف')
                ->icon('heroicon-o-eye'),
            DeleteAction::make()
                ->label('حذف الموظف')
                ->icon('heroicon-o-trash'),
            ForceDeleteAction::make()
                ->label('حذف نهائي'),
            RestoreAction::make()
                ->label('استعادة'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ بيانات الموظف';
    }
}
