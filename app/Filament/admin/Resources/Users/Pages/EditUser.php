<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.admin.resources.users.pages.edit-user';

    public function getTitle(): string
    {
        return 'تعديل المستخدم — ' . $this->getRecord()->name;
    }

    public function getBreadcrumb(): string
    {
        return 'تعديل';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('عرض المستخدم')
                ->icon('heroicon-o-eye'),
            DeleteAction::make()
                ->label('حذف المستخدم')
                ->icon('heroicon-o-trash'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ بيانات المستخدم';
    }
}
