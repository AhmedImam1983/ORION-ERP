<?php

namespace App\Filament\Admin\Resources\StockMovements\Pages;

use App\Filament\Admin\Resources\StockMovements\StockMovementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    protected string $view = 'filament.admin.resources.stock-movements.pages.create-stock-movement';

    public function getHeading(): string
    {
        return 'تسوية مخزون يدوية';
    }

    public function getSubheading(): ?string
    {
        return 'استخدمها فقط للتسويات أو الرصيد الافتتاحي مع تسجيل مرجع واضح';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم تسجيل حركة المخزون';
    }
}
