<?php

namespace App\Filament\Admin\Resources\Branches\Pages;

use App\Filament\Admin\Resources\Branches\BranchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    protected string $view = 'filament.admin.resources.branches.pages.edit-branch';

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
            DeleteAction::make()
                ->label('حذف الفرع')
                ->icon('heroicon-o-trash'),
        ];
    }
}
