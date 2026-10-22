<?php

namespace App\Filament\Admin\Resources\PurchaseOrders\Pages;

use App\Filament\Admin\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected string $view = 'filament.admin.resources.purchase-orders.pages.list-purchase-orders';

    public function getSubheading(): ?string
    {
        return 'إدارة دورة طلب الشراء من المسودة وحتى الاستلام ومتابعة الطلبات المتأخرة';
    }

    public function getPurchaseOrderStats(): array
    {
        $base = PurchaseOrder::query();

        return [
            'total' => (clone $base)->count(),
            'open' => (clone $base)->whereIn('status', ['draft', 'sent', 'confirmed', 'partial'])->count(),
            'received' => (clone $base)->where('status', 'received')->count(),
            'overdue' => (clone $base)
                ->whereNotNull('expected_date')
                ->whereDate('expected_date', '<', today())
                ->whereNotIn('status', ['received', 'cancelled'])
                ->count(),
            'value' => (float) (clone $base)->where('status', '!=', 'cancelled')->sum('total'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('طلب شراء جديد')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
