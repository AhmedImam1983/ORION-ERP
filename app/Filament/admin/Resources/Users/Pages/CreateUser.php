<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.admin.resources.users.pages.create-user';

    public function getTitle(): string
    {
        return 'إضافة مستخدم جديد';
    }

    public function getBreadcrumb(): string
    {
        return 'إضافة';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء المستخدم بنجاح';
    }
}
