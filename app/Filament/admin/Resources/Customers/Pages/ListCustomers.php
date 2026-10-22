<?php

namespace App\Filament\Admin\Resources\Customers\Pages;

use App\Filament\Admin\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected string $view = 'filament.admin.resources.customers.pages.list-customers';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getCustomerStats(): array
    {
        $query = Customer::query();

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('is_active', true)->count(),
            'business' => (clone $query)->whereIn('type', ['company', 'government'])->count(),
            'credit_limit' => (float) (clone $query)->sum('credit_limit'),
        ];
    }
}
