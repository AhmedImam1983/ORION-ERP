<?php

namespace App\Filament\Admin\Resources\Customers\Pages;

use App\Filament\Admin\Resources\Customers\CustomerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected string $view = 'filament.admin.resources.customers.pages.view-customer';

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
                ->label('تعديل العميل')
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}
