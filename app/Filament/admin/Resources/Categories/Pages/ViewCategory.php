<?php

namespace App\Filament\Admin\Resources\Categories\Pages;

use App\Filament\Admin\Resources\Categories\CategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected string $view = 'filament.admin.resources.categories.pages.view-category';

    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    public function getBreadcrumb(): string
    {
        return 'عرض';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('تعديل المجموعة')
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}
