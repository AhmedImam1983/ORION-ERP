<?php

namespace App\Filament\Admin\Resources\StockMovements\Pages;

use App\Filament\Admin\Resources\StockMovements\StockMovementResource;
use App\Models\StockMovement;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    protected string $view = 'filament.admin.resources.stock-movements.pages.list-stock-movements';

    public function getSubheading(): ?string
    {
        return 'سجل تشغيلي يوضح كل حركة وأثرها على رصيد المخزون';
    }

    public function getMovementStats(): array
    {
        $incomingTypes = ['purchase', 'sale_return', 'transfer_in', 'adjustment_in', 'opening'];
        $outgoingTypes = ['sale', 'purchase_return', 'transfer_out', 'adjustment_out'];

        $base = StockMovement::query();

        $incomingQty = (float) (clone $base)
            ->whereIn('type', $incomingTypes)
            ->sum('quantity');

        $outgoingQty = (float) (clone $base)
            ->whereIn('type', $outgoingTypes)
            ->sum('quantity');

        return [
            'total' => (clone $base)->count(),
            'today' => (clone $base)->whereDate('created_at', today())->count(),
            'incoming_qty' => $incomingQty,
            'outgoing_qty' => $outgoingQty,
            'net_qty' => $incomingQty - $outgoingQty,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('تسوية مخزون يدوية')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('primary'),
        ];
    }
}
