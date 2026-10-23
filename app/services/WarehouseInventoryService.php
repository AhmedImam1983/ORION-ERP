<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WarehouseInventoryService
{
    public function balance(Product|int $product, int $warehouseId): float
    {
        if ($product instanceof Product) {
            if ($product->isService()) {
                return 0.0;
            }

            $productId = $product->id;
        } else {
            $productId = $product;
            if (Product::query()->whereKey($productId)->value('item_type') === 'service') {
                return 0.0;
            }
        }

        return round((float) (WarehouseStock::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0), 3);
    }

    public function syncProductTotal(Product $product): float
    {
        $total = round((float) WarehouseStock::query()
            ->where('product_id', $product->id)
            ->sum('quantity'), 3);

        $product->quantity = $total;
        $product->save();

        return $total;
    }


    public function assignLegacyBalance(Product|int $product, int $warehouseId): void
    {
        DB::transaction(function () use ($product, $warehouseId) {
            $productId = $product instanceof Product ? $product->id : $product;

            $lockedProduct = Product::query()
                ->whereKey($productId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedProduct->isService()) {
                throw new RuntimeException('الخدمات لا تحتوي على أرصدة مخزنية.');
            }

            $warehouse = Warehouse::query()
                ->whereKey($warehouseId)
                ->where('is_active', true)
                ->first();

            if (!$warehouse) {
                throw new RuntimeException('المخزن المحدد غير موجود أو غير نشط.');
            }

            if (WarehouseStock::query()->where('product_id', $lockedProduct->id)->exists()) {
                throw new RuntimeException('للصنف أرصدة مخازن بالفعل؛ لا يمكن تنفيذ Cutover مرة أخرى.');
            }

            $quantity = round((float) ($lockedProduct->quantity ?? 0), 3);

            if ($quantity < 0) {
                throw new RuntimeException('الرصيد الحالي للصنف سالب؛ يجب مراجعة البيانات قبل تثبيت Cutover لهذا الصنف.');
            }

            WarehouseStock::create([
                'product_id'       => $lockedProduct->id,
                'warehouse_id'     => $warehouseId,
                'opening_quantity' => $quantity,
                'quantity'         => $quantity,
            ]);

            $lockedProduct->warehouse_id = $warehouseId;
            $lockedProduct->quantity = $quantity;
            $lockedProduct->save();
        });
    }

    public function transfer(
        Product|int $product,
        int $fromWarehouseId,
        int $toWarehouseId,
        float $quantity,
        ?string $notes = null
    ): string {
        $quantity = round($quantity, 3);

        if ($quantity <= 0) {
            throw new RuntimeException('كمية التحويل يجب أن تكون أكبر من صفر.');
        }

        if ($fromWarehouseId === $toWarehouseId) {
            throw new RuntimeException('مخزن المصدر والوجهة يجب أن يكونا مختلفين.');
        }

        return DB::transaction(function () use (
            $product,
            $fromWarehouseId,
            $toWarehouseId,
            $quantity,
            $notes
        ) {
            $productId = $product instanceof Product ? $product->id : $product;

            $lockedProduct = Product::query()
                ->whereKey($productId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedProduct->isService()) {
                throw new RuntimeException('لا يمكن تحويل خدمة بين المخازن لأنها لا تُدار كمخزون.');
            }

            $warehouses = Warehouse::query()
                ->whereIn('id', [$fromWarehouseId, $toWarehouseId])
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            if (!$warehouses->has($fromWarehouseId) || !$warehouses->has($toWarehouseId)) {
                throw new RuntimeException('مخزن المصدر أو الوجهة غير موجود/غير نشط.');
            }

            $stocks = WarehouseStock::query()
                ->where('product_id', $lockedProduct->id)
                ->whereIn('warehouse_id', [$fromWarehouseId, $toWarehouseId])
                ->orderBy('warehouse_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('warehouse_id');

            $source = $stocks->get($fromWarehouseId);

            if (!$source) {
                throw new RuntimeException('الصنف غير موجود في مخزن المصدر.');
            }

            $sourceBefore = round((float) $source->quantity, 3);

            if ($sourceBefore + 0.0009 < $quantity) {
                throw new RuntimeException(
                    'رصيد مخزن المصدر غير كافٍ. المتاح ' .
                    number_format($sourceBefore, 3) .
                    ' والمطلوب ' . number_format($quantity, 3)
                );
            }

            $destination = $stocks->get($toWarehouseId);

            if (!$destination) {
                $destination = WarehouseStock::create([
                    'product_id'       => $lockedProduct->id,
                    'warehouse_id'     => $toWarehouseId,
                    'opening_quantity' => 0,
                    'quantity'         => 0,
                ]);
            }

            $destinationBefore = round((float) $destination->quantity, 3);
            $sourceAfter = round($sourceBefore - $quantity, 3);
            $destinationAfter = round($destinationBefore + $quantity, 3);

            $source->update(['quantity' => $sourceAfter]);
            $destination->update(['quantity' => $destinationAfter]);

            $reference = 'WTR-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));

            StockMovement::create([
                'product_id'     => $lockedProduct->id,
                'warehouse_id'   => $fromWarehouseId,
                'branch_id'      => null,
                'invoice_id'     => null,
                'type'           => 'transfer_out',
                'quantity'       => $quantity,
                'unit_price'     => round((float) $lockedProduct->cost_price, 2),
                'total'          => round($quantity * (float) $lockedProduct->cost_price, 2),
                'balance_before' => $sourceBefore,
                'balance_after'  => $sourceAfter,
                'reference'      => $reference,
                'notes'          => $notes ?: 'تحويل إلى ' . $warehouses[$toWarehouseId]->name,
                'created_by'     => auth()->id() ?? 1,
            ]);

            StockMovement::create([
                'product_id'     => $lockedProduct->id,
                'warehouse_id'   => $toWarehouseId,
                'branch_id'      => null,
                'invoice_id'     => null,
                'type'           => 'transfer_in',
                'quantity'       => $quantity,
                'unit_price'     => round((float) $lockedProduct->cost_price, 2),
                'total'          => round($quantity * (float) $lockedProduct->cost_price, 2),
                'balance_before' => $destinationBefore,
                'balance_after'  => $destinationAfter,
                'reference'      => $reference,
                'notes'          => $notes ?: 'تحويل من ' . $warehouses[$fromWarehouseId]->name,
                'created_by'     => auth()->id() ?? 1,
            ]);

            $this->syncProductTotal($lockedProduct);

            return $reference;
        });
    }
}
