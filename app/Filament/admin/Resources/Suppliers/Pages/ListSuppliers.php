<?php

namespace App\Filament\Admin\Resources\Suppliers\Pages;

use App\Filament\Admin\Resources\Suppliers\SupplierResource;
use App\Models\Supplier;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected string $view = 'filament.admin.resources.suppliers.pages.list-suppliers';

    public function getSubheading(): ?string
    {
        return 'إدارة بيانات الموردين والأرصدة الافتتاحية ووسائل التواصل من شاشة تشغيلية واحدة';
    }

    public function getSupplierStats(): array
    {
        $base = Supplier::query();

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('is_active', true)->count(),
            'inactive' => (clone $base)->where('is_active', false)->count(),
            'credit_opening' => (float) (clone $base)->where('balance_type', 'credit')->sum('opening_balance'),
            'debit_opening' => (float) (clone $base)->where('balance_type', 'debit')->sum('opening_balance'),
            'without_bank' => (clone $base)
                ->where(function ($q) {
                    $q->whereNull('bank_account')->orWhere('bank_account', '');
                })
                ->count(),
            'without_tax' => (clone $base)
                ->where(function ($q) {
                    $q->whereNull('tax_number')->orWhere('tax_number', '');
                })
                ->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة مورد جديد')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
