<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Creates a controlled Purchase Return from an already-posted purchase invoice.
 *
 * Design goals:
 * - Return is linked to the exact original invoice and exact original lines.
 * - Supplier / branch / warehouse / prices / discounts / VAT snapshots come
 *   from the original purchase; the user only chooses return quantities + reason.
 * - Cumulative returned quantity can never exceed the original purchased quantity.
 * - The original purchase is NEVER edited or deleted.
 * - Commercial GL + inventory posting is delegated to InvoicePostingService.
 * - No supplier cash/bank refund is created automatically.
 *
 * For a fully-paid purchase, the return creates a debit balance on the supplier
 * (vendor credit / amount receivable from supplier). A later dedicated supplier
 * refund/settlement workflow can clear that balance.
 */
class PurchaseReturnService
{
    /**
     * Rows shown to the UI.
     */
    public function returnableLines(Invoice $original): array
    {
        $original = Invoice::withoutGlobalScopes()
            ->with('items')
            ->findOrFail($original->getKey());

        $this->assertReturnablePurchase($original);

        $returnedByLine = $this->returnedQuantities(
            $original->items->pluck('id')->map(fn ($id) => (int) $id)->all()
        );

        return $original->items
            ->map(function (InvoiceItem $item) use ($returnedByLine): array {
                $purchased = round((float) $item->quantity, 3);
                $returned  = round((float) ($returnedByLine[$item->id] ?? 0), 3);
                $available = round(max($purchased - $returned, 0), 3);

                return [
                    'item_id'              => (int) $item->id,
                    'product_id'           => (int) $item->product_id,
                    'product_name'         => $item->product_name ?: ($item->product?->name ?? ('#' . $item->product_id)),
                    'product_code'         => $item->product_code,
                    'unit'                 => $item->unit ?: 'قطعة',
                    'purchased_quantity'   => $purchased,
                    'returned_quantity'    => $returned,
                    'available_quantity'   => $available,
                    'unit_price'           => round((float) $item->unit_price, 2),
                    'discount_percent'     => round((float) ($item->discount_percent ?? 0), 2),
                    'tax_percent'          => round((float) ($item->tax_percent ?? 0), 2),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param array<int|string, mixed> $quantities keyed by ORIGINAL invoice_item_id
     */
    public function createFromPurchase(
        Invoice $original,
        array $quantities,
        string $reason
    ): Invoice {
        $reason = trim($reason);

        if ($reason === '') {
            throw new RuntimeException('سبب مرتجع الشراء مطلوب.');
        }

        return DB::transaction(function () use ($original, $quantities, $reason): Invoice {
            /*
             * One purchase can only calculate/create a return at a time.
             * This makes the cumulative quantity guard concurrency-safe.
             */
            $original = Invoice::withoutGlobalScopes()
                ->whereKey($original->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertReturnablePurchase($original);

            if (!JournalEntry::query()
                ->where('reference', $original->invoice_number)
                ->where('status', 'posted')
                ->exists()) {
                throw new RuntimeException(
                    "الفاتورة {$original->invoice_number} ليست فاتورة شراء مُرحلة محاسبياً."
                );
            }

            $originalItems = InvoiceItem::query()
                ->where('invoice_id', $original->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($originalItems->isEmpty()) {
                throw new RuntimeException('الفاتورة الأصلية لا تحتوي على أصناف قابلة للإرجاع.');
            }

            $returnedByLine = $this->returnedQuantities(
                $originalItems->keys()->map(fn ($id) => (int) $id)->all()
            );

            $selected = [];

            foreach ($quantities as $rawItemId => $rawQty) {
                $itemId = (int) $rawItemId;
                $qty    = round((float) $rawQty, 3);

                if ($qty <= 0) {
                    continue;
                }

                /** @var InvoiceItem|null $item */
                $item = $originalItems->get($itemId);

                if (!$item) {
                    throw new RuntimeException('أحد أسطر المرتجع لا يتبع فاتورة الشراء الأصلية.');
                }

                $purchased = round((float) $item->quantity, 3);
                $returned  = round((float) ($returnedByLine[$itemId] ?? 0), 3);
                $available = round(max($purchased - $returned, 0), 3);

                if ($qty - $available > 0.0009) {
                    throw new RuntimeException(
                        'الكمية المرتجعة أكبر من المتاح للصنف ' .
                        ($item->product_name ?: ('#' . $item->product_id)) .
                        ' — المتاح: ' . number_format($available, 3)
                    );
                }

                $selected[] = [$item, $qty];
            }

            if (empty($selected)) {
                throw new RuntimeException('اختر كمية مرتجع أكبر من صفر لصنف واحد على الأقل.');
            }

            $subtotal       = 0.0;
            $itemDiscount   = 0.0;
            $taxAmount      = 0.0;
            $returnItemData = [];

            foreach ($selected as [$item, $qty]) {
                $unitPrice       = round((float) $item->unit_price, 2);
                $discountPercent = round((float) ($item->discount_percent ?? 0), 2);
                $taxPercent      = round((float) ($item->tax_percent ?? 0), 2);

                /*
                 * Mirror Orion's current invoice calculation rules using the
                 * ORIGINAL line snapshots (not today's product master values).
                 */
                $lineSubtotal = round($qty * $unitPrice, 2);
                $lineDiscount = round($lineSubtotal * ($discountPercent / 100), 2);
                $afterDiscount = round($lineSubtotal - $lineDiscount, 2);
                $lineTax      = round($afterDiscount * ($taxPercent / 100), 2);
                $lineTotal    = round($afterDiscount + $lineTax, 2);

                $subtotal     += $lineSubtotal;
                $itemDiscount += $lineDiscount;
                $taxAmount    += $lineTax;

                $returnItemData[] = [
                    'original_invoice_item_id'     => $item->id,
                    'product_id'                   => $item->product_id,
                    'product_name'                 => $item->product_name,
                    'product_code'                 => $item->product_code,
                    'unit'                         => $item->unit,
                    'quantity'                     => $qty,
                    'unit_price'                   => $unitPrice,
                    'cost_price'                   => round((float) ($item->cost_price ?? 0), 2),
                    'discount_percent'             => $discountPercent,
                    'discount_amount'              => $lineDiscount,
                    'tax_percent'                  => $taxPercent,
                    'tax_amount'                   => $lineTax,
                    'total'                        => $lineTotal,
                    'notes'                        => 'مرتجع من السطر #' . $item->id .
                                                      ' — ' . $original->invoice_number,
                    'vat_type'                     => $item->vat_type,
                    'vat_exemption_reason_code'    => $item->vat_exemption_reason_code,
                    'vat_exemption_reason'         => $item->vat_exemption_reason,
                ];
            }

            $invoiceDiscountPercent = round((float) ($original->discount_percent ?? 0), 2);
            $invoiceLevelDiscount   = round($subtotal * ($invoiceDiscountPercent / 100), 2);
            $totalDiscount          = round($itemDiscount + $invoiceLevelDiscount, 2);
            $subtotal               = round($subtotal, 2);
            $taxAmount              = round($taxAmount, 2);
            $total                  = round($subtotal - $totalDiscount + $taxAmount, 2);

            if ($total <= 0) {
                throw new RuntimeException('إجمالي مرتجع الشراء غير صالح.');
            }

            $return = Invoice::create([
                'invoice_number'     => Invoice::generateNumber('purchase_return'),
                'type'               => 'purchase_return',
                'payment_type'       => $original->payment_type,
                'customer_id'        => null,
                'supplier_id'        => $original->supplier_id,
                'branch_id'          => $original->branch_id,
                'warehouse_id'       => $original->warehouse_id,
                'original_invoice_id'=> $original->id,
                'invoice_date'       => today(),
                'due_date'           => null,
                'subtotal'           => $subtotal,
                'discount_amount'    => $totalDiscount,
                'discount_percent'   => $invoiceDiscountPercent,
                'tax_amount'         => $taxAmount,
                'tax_percent'        => $original->tax_percent,
                'total'              => $total,

                // Return document is effective immediately; it is NOT a new payable.
                'paid_amount'        => 0,
                'remaining_amount'   => 0,
                'status'             => 'confirmed',

                'notes'              => 'مرتجع شراء من ' . $original->invoice_number .
                                        ' — السبب: ' . $reason,
                'created_by'         => auth()->id() ?? $original->created_by,
            ]);

            foreach ($returnItemData as $data) {
                $data['invoice_id'] = $return->id;
                InvoiceItem::create($data);
            }

            /*
             * Unified posting:
             * Dr AP / Cr Inventory + VAT reversal
             * AND inventory OUT
             * in the same transaction.
             */
            app(InvoicePostingService::class)->post($return);

            return $return->fresh([
                'items',
                'supplier',
                'originalInvoice',
            ]);
        });
    }

    private function assertReturnablePurchase(Invoice $invoice): void
    {
        if ($invoice->type !== 'purchase') {
            throw new RuntimeException('يمكن إنشاء مرتجع شراء فقط من فاتورة شراء أصلية.');
        }

        if (!in_array($invoice->status, ['confirmed', 'partial', 'paid'], true)) {
            throw new RuntimeException(
                "حالة الفاتورة {$invoice->status} لا تسمح بإنشاء مرتجع شراء."
            );
        }

        if (!$invoice->supplier_id) {
            throw new RuntimeException('فاتورة الشراء الأصلية غير مرتبطة بمورد.');
        }
    }

    /**
     * Quantities already returned from each original line.
     *
     * Uses a direct join so historical/global BranchScope settings do not hide
     * a linked return from the cumulative guard.
     *
     * @return array<int, float>
     */
    private function returnedQuantities(array $originalItemIds): array
    {
        if (empty($originalItemIds)) {
            return [];
        }

        return DB::table('invoice_items as ri')
            ->join('invoices as r', 'r.id', '=', 'ri.invoice_id')
            ->whereIn('ri.original_invoice_item_id', $originalItemIds)
            ->where('r.type', 'purchase_return')
            ->where('r.status', '!=', 'cancelled')
            ->whereNull('r.deleted_at')
            ->groupBy('ri.original_invoice_item_id')
            ->selectRaw('ri.original_invoice_item_id, SUM(ri.quantity) as returned_qty')
            ->pluck('returned_qty', 'ri.original_invoice_item_id')
            ->mapWithKeys(fn ($qty, $id) => [(int) $id => round((float) $qty, 3)])
            ->all();
    }
}
