<?php

namespace App\Filament\Admin\Resources\Categories\Pages;

use App\Filament\Admin\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected string $view = 'filament.admin.resources.categories.pages.edit-category';

    public function getTitle(): string
    {
        return 'تعديل: ' . $this->getRecord()->name;
    }

    public function getBreadcrumb(): string
    {
        return 'تعديل';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('عرض')
                ->icon('heroicon-o-eye'),
            DeleteAction::make()
                ->label('حذف')
                ->icon('heroicon-o-trash'),
            ForceDeleteAction::make()
                ->label('حذف نهائي'),
            RestoreAction::make()
                ->label('استعادة'),
        ];
    }
}
