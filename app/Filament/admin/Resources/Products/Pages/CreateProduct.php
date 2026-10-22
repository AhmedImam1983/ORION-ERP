<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockLocation;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.admin.resources.products.pages.create-product';

    public function getSubheading(): ?string
    {
        return 'أضف بيانات الصنف والمخزون والتسعير والبيانات الأساسية من شاشة واحدة منظمة';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة الصنف بنجاح';
    }

    protected function afterCreate(): void
    {
        $product = $this->getRecord();

        if (!$product->warehouse_id) {
            return;
        }

        $quantity = round((float) ($product->quantity ?? 0), 3);

        $stock = WarehouseStock::query()->firstOrCreate(
            [
                'product_id'   => $product->id,
                'warehouse_id' => $product->warehouse_id,
            ],
            [
                'opening_quantity' => $quantity,
                'quantity'         => $quantity,
            ]
        );

        $locationCode = trim((string) ($this->data['location_code'] ?? ''));

        if ($locationCode !== '') {
            WarehouseStockLocation::updateOrCreate(
                [
                    'warehouse_stock_id' => $stock->id,
                    'location_code'      => $locationCode,
                ],
                ['notes' => null]
            );
        }
    }
}
