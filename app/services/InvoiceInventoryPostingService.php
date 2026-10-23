<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use RuntimeException;

/**
 * Posts invoice quantities to the warehouse stock subledger.
 *
 * IMPORTANT:
 * - Called by InvoicePostingService while the invoice row is locked.
 * - No historical backfill is attempted.
 * - If an original StockMovement already exists for this invoice/reference,
 *   the inventory side is treated as already posted and is not touched.
 * - Product rows are locked in deterministic ID order.
 * - Product.quantity remains a compatibility TOTAL across all warehouses.
 */
class InvoiceInventoryPostingService
{
    public function post(Invoice $invoice): void
    {
        if (!in_array($invoice->status, ['confirmed', 'partial', 'paid'], true)) {
            throw new RuntimeException(
                "لا يمكن ترحيل مخزون الفاتورة {$invoice->invoice_number} وهي بالحالة {$invoice->status}."
            );
        }

        $isOutgoing = in_array($invoice->type, ['sale', 'purchase_return'], true);
        $isIncoming = in_array($invoice->type, ['purchase', 'sale_return'], true);

        if (!$isOutgoing && !$isIncoming) {
            throw new RuntimeException(
                "نوع فاتورة غير مدعوم في حركة المخزون: {$invoice->type}"
            );
        }

        $invoice->loadMissing('items.product');

        if ($invoice->items->isEmpty()) {
            throw new RuntimeException(
                "لا يمكن ترحيل الفاتورة {$invoice->invoice_number} بدون أصناف."
            );
        }

        // Validate references, then isolate stockable lines. Services are billed
        // normally but never create WarehouseStock or StockMovement records.
        $linkedItems = $invoice->items->filter(fn ($item) => (bool) $item->product_id)->values();
        if ($linkedItems->isEmpty()) {
            throw new RuntimeException(
                "أصناف الفاتورة {$invoice->invoice_number} غير مرتبطة بمنتجات صالحة."
            );
        }

        foreach ($linkedItems as $item) {
            if (!$item->product) {
                throw new RuntimeException(
                    "تعذر العثور على الصنف المرتبط بسطر الفاتورة {$invoice->invoice_number}."
                );
            }

            if ($item->product->isService() && in_array($invoice->type, ['purchase', 'purchase_return'], true)) {
                throw new RuntimeException('لا يمكن ترحيل خدمة كشراء مخزني أو مرتجع شراء. استخدم مسار المصروفات المناسب للخدمات المشتراة.');
            }
        }

        $stockItems = $linkedItems
            ->filter(fn ($item) => $item->product_id && $item->product?->isStockItem())
            ->values();

        if ($stockItems->isEmpty()) {
            return;
        }

        if (!$invoice->warehouse_id) {
            throw new RuntimeException(
                "لا يمكن ترحيل مخزون الفاتورة {$invoice->invoice_number} بدون مخزن محدد."
            );
        }

        $warehouse = Warehouse::query()
            ->whereKey($invoice->warehouse_id)
            ->where('is_active', true)
            ->first();

        if (!$warehouse) {
            throw new RuntimeException(
                "المخزن المرتبط بالفاتورة {$invoice->invoice_number} غير موجود أو غير نشط."
            );
        }

        // Idempotency + historical safety: never duplicate or backfill partially.
        $alreadyPosted = StockMovement::query()
            ->where('invoice_id', $invoice->id)
            ->where('reference', $invoice->invoice_number)
            ->exists();

        if ($alreadyPosted) {
            return;
        }

        $productIds = $stockItems
            ->pluck('product_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values();

        // Product lock is the global per-product serialization lock used by all
        // warehouse-stock mutations in this patch.
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($products->count() !== $productIds->count()) {
            throw new RuntimeException(
                "تعذر ترحيل المخزون لأن أحد أصناف الفاتورة {$invoice->invoice_number} غير موجود."
            );
        }

        $stocks = WarehouseStock::query()
            ->where('warehouse_id', $invoice->warehouse_id)
            ->whereIn('product_id', $productIds)
            ->orderBy('product_id')
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        // Incoming operations may introduce an existing stock product into a
        // second warehouse. Outgoing operations are never allowed to invent stock.
        if ($isIncoming) {
            foreach ($productIds as $productId) {
                if (!$stocks->has($productId)) {
                    WarehouseStock::create([
                        'product_id'       => $productId,
                        'warehouse_id'     => $invoice->warehouse_id,
                        'opening_quantity' => 0,
                        'quantity'         => 0,
                    ]);
                }
            }

            $stocks = WarehouseStock::query()
                ->where('warehouse_id', $invoice->warehouse_id)
                ->whereIn('product_id', $productIds)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');
        }

        foreach ($stockItems as $item) {
            $product = $products->get((int) $item->product_id);
            $stock = $stocks->get((int) $item->product_id);

            if (!$product) {
                throw new RuntimeException(
                    "تعذر العثور على الصنف المرتبط بسطر الفاتورة {$invoice->invoice_number}."
                );
            }

            if (!$stock) {
                throw new RuntimeException(
                    'الصنف ' . ($product->name ?? ('#' . $product->id)) .
                    ' غير موجود في المخزن ' . $warehouse->name . '.'
                );
            }

            $quantity = round((float) $item->quantity, 3);

            if ($quantity <= 0) {
                throw new RuntimeException(
                    "كمية غير صالحة في الفاتورة {$invoice->invoice_number}."
                );
            }

            $balanceBefore = round((float) $stock->quantity, 3);

            if ($isOutgoing) {
                if ($balanceBefore + 0.0009 < $quantity) {
                    throw new RuntimeException(
                        'رصيد الصنف غير كافٍ في مخزن ' . $warehouse->name . ': ' .
                        ($product->name ?? ('#' . $product->id)) .
                        ' — المتاح ' . number_format($balanceBefore, 3) .
                        ' والمطلوب ' . number_format($quantity, 3)
                    );
                }

                $balanceAfter = round($balanceBefore - $quantity, 3);
            } else {
                $balanceAfter = round($balanceBefore + $quantity, 3);
            }

            $stock->quantity = $balanceAfter;
            $stock->save();

            // Compatibility total for legacy screens/readers.
            $product->quantity = round((float) WarehouseStock::query()
                ->where('product_id', $product->id)
                ->sum('quantity'), 3);
            $product->save();

            StockMovement::create([
                'product_id'     => $product->id,
                'warehouse_id'   => $invoice->warehouse_id,
                'branch_id'      => $invoice->branch_id,
                'invoice_id'     => $invoice->id,
                'type'           => $invoice->type,
                'quantity'       => $quantity,
                'unit_price'     => round((float) $item->unit_price, 2),
                'total'          => round((float) $item->total, 2),
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'reference'      => $invoice->invoice_number,
                'created_by'     => auth()->id() ?? $invoice->created_by ?? 1,
            ]);
        }
    }
}
