<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockLocation;
use App\Services\WarehouseInventoryService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.admin.resources.products.pages.edit-product';

    public function getSubheading(): ?string
    {
        return 'تحديث بيانات الصنف والتسعير والبيانات الأساسية — الرصيد يتغير من الحركات المخزنية';
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ تعديلات الصنف';
    }

    protected function afterSave(): void
    {
        $product = $this->getRecord();

        app(WarehouseInventoryService::class)->syncProductTotal($product);

        if (!$product->warehouse_id) {
            return;
        }

        $stock = WarehouseStock::query()
            ->where('product_id', $product->id)
            ->where('warehouse_id', $product->warehouse_id)
            ->first();

        if (!$stock) {
            return;
        }

        $locationCode = trim((string) ($this->data['location_code'] ?? ''));

        // The current UI exposes one primary location per warehouse stock.
        $stock->locations()->delete();

        if ($locationCode !== '') {
            WarehouseStockLocation::create([
                'warehouse_stock_id' => $stock->id,
                'location_code'      => $locationCode,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('عرض الصنف'),
            DeleteAction::make()->label('حذف'),
            ForceDeleteAction::make()->label('حذف نهائي'),
            RestoreAction::make()->label('استعادة'),
        ];
    }
}
