<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Creates a controlled sale return from an already-posted sales invoice.
 * The original invoice is never edited or deleted. The return document posts
 * sales/VAT reversal, COGS reversal, and incoming stock atomically through the
 * existing InvoicePostingService. Cash/bank refund is a separate settlement.
 */
class SaleReturnService
{
    public function returnableLines(Invoice $original): array
    {
        $original = Invoice::withoutGlobalScopes()
            ->with('items.product')
            ->findOrFail($original->getKey());

        $this->assertReturnableSale($original);

        $returnedByLine = $this->returnedQuantities(
            $original->items->pluck('id')->map(fn ($id) => (int) $id)->all()
        );

        return $original->items
            ->map(function (InvoiceItem $item) use ($returnedByLine): array {
                $sold = round((float) $item->quantity, 3);
                $returned = round((float) ($returnedByLine[$item->id] ?? 0), 3);

                return [
                    'item_id' => (int) $item->id,
                    'product_id' => (int) $item->product_id,
                    'product_name' => $item->product_name ?: ($item->product?->name ?? ('#' . $item->product_id)),
                    'product_code' => $item->product_code,
                    'unit' => $item->unit ?: 'قطعة',
                    'sold_quantity' => $sold,
                    'returned_quantity' => $returned,
                    'available_quantity' => round(max($sold - $returned, 0), 3),
                    'unit_price' => round((float) $item->unit_price, 2),
                    'discount_percent' => round((float) ($item->discount_percent ?? 0), 2),
                    'tax_percent' => round((float) ($item->tax_percent ?? 0), 2),
                ];
            })
            ->values()
            ->all();
    }

    /** Calculate the current selected return value using the original invoice snapshots. */
    public function estimateReturn(Invoice $original, array $quantities): array
    {
        $rows = collect($this->returnableLines($original))->keyBy('item_id');
        $items = InvoiceItem::query()->where('invoice_id', $original->id)->get()->keyBy('id');
        $alreadyReturned = $this->returnedSummary($items->keys()->map(fn ($id) => (int) $id)->all());
        $subtotal = 0.0;
        $itemDiscount = 0.0;
        $tax = 0.0;

        foreach ($quantities as $itemId => $rawQty) {
            $itemId = (int) $itemId;
            $qty = round((float) $rawQty, 3);
            if ($qty <= 0 || !$rows->has($itemId) || !$items->has($itemId)) {
                continue;
            }

            $row = $rows->get($itemId);
            $item = $items->get($itemId);
            $qty = min($qty, (float) $row['available_quantity']);
            $soldQty = round((float) $item->quantity, 3);
            if ($qty <= 0 || $soldQty <= 0) {
                continue;
            }

            $available = (float) $row['available_quantity'];
            $already = $alreadyReturned[$itemId] ?? [];
            $finalRemainder = abs($qty - $available) <= 0.0009;
            $ratio = $qty / $soldQty;
            $lineSubtotal = round($qty * (float) $item->unit_price, 2);
            $lineDiscount = $finalRemainder
                ? round(max((float) ($item->discount_amount ?? 0) - (float) ($already['discount'] ?? 0), 0), 2)
                : round((float) ($item->discount_amount ?? 0) * $ratio, 2);
            $lineTax = $finalRemainder
                ? round(max((float) ($item->tax_amount ?? 0) - (float) ($already['tax'] ?? 0), 0), 2)
                : round((float) ($item->tax_amount ?? 0) * $ratio, 2);

            $subtotal += $lineSubtotal;
            $itemDiscount += $lineDiscount;
            $tax += $lineTax;
        }

        $subtotal = round($subtotal, 2);
        $discount = round($itemDiscount + $subtotal * ((float) ($original->discount_percent ?? 0) / 100), 2);
        $tax = round($tax, 2);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => round($subtotal - $discount + $tax, 2),
        ];
    }

    /** @param array<int|string,mixed> $quantities keyed by original invoice_item_id */
    public function createFromSale(Invoice $original, array $quantities, string $reason): Invoice
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('سبب مرتجع البيع مطلوب.');
        }

        return DB::transaction(function () use ($original, $quantities, $reason): Invoice {
            $original = Invoice::withoutGlobalScopes()
                ->whereKey($original->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertReturnableSale($original);

            if (!JournalEntry::query()
                ->where('reference', $original->invoice_number)
                ->where('status', 'posted')
                ->exists()) {
                throw new RuntimeException("الفاتورة {$original->invoice_number} ليست فاتورة بيع مرحلة محاسبيًا.");
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

            $returnedByLine = $this->returnedSummary($originalItems->keys()->map(fn ($id) => (int) $id)->all());
            $selected = [];

            foreach ($quantities as $rawItemId => $rawQty) {
                $itemId = (int) $rawItemId;
                $qty = round((float) $rawQty, 3);
                if ($qty <= 0) {
                    continue;
                }

                /** @var InvoiceItem|null $item */
                $item = $originalItems->get($itemId);
                if (!$item) {
                    throw new RuntimeException('أحد أسطر المرتجع لا يتبع فاتورة البيع الأصلية.');
                }

                $sold = round((float) $item->quantity, 3);
                $already = round((float) ($returnedByLine[$itemId]['quantity'] ?? 0), 3);
                $available = round(max($sold - $already, 0), 3);

                if ($qty - $available > 0.0009) {
                    throw new RuntimeException(
                        'الكمية المرتجعة أكبر من المتاح للصنف ' .
                        ($item->product_name ?: ('#' . $item->product_id)) .
                        ' — المتاح: ' . number_format($available, 3)
                    );
                }

                $selected[] = [$item, $qty, $available, $returnedByLine[$itemId] ?? []];
            }

            if (!$selected) {
                throw new RuntimeException('اختر كمية مرتجع أكبر من صفر لصنف واحد على الأقل.');
            }

            $subtotal = 0.0;
            $itemDiscount = 0.0;
            $taxAmount = 0.0;
            $returnItemData = [];

            foreach ($selected as [$item, $qty, $available, $alreadySummary]) {
                $soldQty = round((float) $item->quantity, 3);
                $isFinalRemainder = abs($qty - $available) <= 0.0009;
                $ratio = $soldQty > 0 ? $qty / $soldQty : 0;

                $lineSubtotal = round($qty * (float) $item->unit_price, 2);
                $lineDiscount = $isFinalRemainder
                    ? round(max((float) ($item->discount_amount ?? 0) - (float) ($alreadySummary['discount'] ?? 0), 0), 2)
                    : round((float) ($item->discount_amount ?? 0) * $ratio, 2);
                $lineTax = $isFinalRemainder
                    ? round(max((float) ($item->tax_amount ?? 0) - (float) ($alreadySummary['tax'] ?? 0), 0), 2)
                    : round((float) ($item->tax_amount ?? 0) * $ratio, 2);

                $lineTotal = round($lineSubtotal - $lineDiscount + $lineTax, 2);
                if ($lineTotal <= 0) {
                    throw new RuntimeException('قيمة المرتجع غير صالحة للصنف: ' . ($item->product_name ?: $item->product_id));
                }

                $subtotal += $lineSubtotal;
                $itemDiscount += $lineDiscount;
                $taxAmount += $lineTax;

                $returnItemData[] = [
                    'original_invoice_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_code' => $item->product_code,
                    'unit' => $item->unit,
                    'quantity' => $qty,
                    'unit_price' => round((float) $item->unit_price, 2),
                    'cost_price' => round((float) ($item->cost_price ?? 0), 2),
                    'discount_percent' => round((float) ($item->discount_percent ?? 0), 2),
                    'discount_amount' => $lineDiscount,
                    'tax_percent' => round((float) ($item->tax_percent ?? 0), 2),
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal,
                    'notes' => 'مرتجع من السطر #' . $item->id . ' — ' . $original->invoice_number,
                    'vat_type' => $item->vat_type,
                    'vat_exemption_reason_code' => $item->vat_exemption_reason_code,
                    'vat_exemption_reason' => $item->vat_exemption_reason,
                ];
            }

            $subtotal = round($subtotal, 2);
            $itemDiscount = round($itemDiscount, 2);
            $taxAmount = round($taxAmount, 2);
            $invoiceDiscountPercent = round((float) ($original->discount_percent ?? 0), 2);
            // The original invoice calculation applies this additional header-level discount to the gross subtotal.
            $invoiceLevelDiscount = round($subtotal * ($invoiceDiscountPercent / 100), 2);
            $totalDiscount = round($itemDiscount + $invoiceLevelDiscount, 2);
            $total = round($subtotal - $totalDiscount + $taxAmount, 2);

            if ($total <= 0) {
                throw new RuntimeException('إجمالي مرتجع البيع غير صالح.');
            }

            $return = Invoice::create([
                'invoice_number' => Invoice::generateNumber('sale_return'),
                'type' => 'sale_return',
                'payment_type' => $original->payment_type,
                'customer_id' => $original->customer_id,
                'supplier_id' => null,
                'branch_id' => $original->branch_id,
                'warehouse_id' => $original->warehouse_id,
                'original_invoice_id' => $original->id,
                'invoice_date' => today(),
                'due_date' => null,
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'discount_percent' => $invoiceDiscountPercent,
                'tax_amount' => $taxAmount,
                'tax_percent' => $original->tax_percent,
                'total' => $total,
                // This represents the unsettled customer credit until the refund voucher posts.
                'paid_amount' => 0,
                'remaining_amount' => $total,
                'status' => 'confirmed',
                'notes' => 'مرتجع بيع من ' . $original->invoice_number . ' — السبب: ' . $reason,
                'created_by' => auth()->id() ?? $original->created_by,
            ]);

            foreach ($returnItemData as $data) {
                $data['invoice_id'] = $return->id;
                InvoiceItem::create($data);
            }

            // Reuses the same commercial, COGS and stock posting path used by POS returns.
            app(InvoicePostingService::class)->post($return);

            return $return->fresh(['items', 'customer', 'originalInvoice']);
        });
    }

    private function assertReturnableSale(Invoice $invoice): void
    {
        if ($invoice->type !== 'sale') {
            throw new RuntimeException('يمكن إنشاء مرتجع بيع فقط من فاتورة بيع أصلية.');
        }
        if (!in_array($invoice->status, ['confirmed', 'partial', 'paid'], true)) {
            throw new RuntimeException("حالة الفاتورة {$invoice->status} لا تسمح بإنشاء مرتجع بيع.");
        }
        if (DB::table('pos_transactions')->where('invoice_id', $invoice->id)->where('type', 'sale')->exists()) {
            throw new RuntimeException('هذه الفاتورة مرتبطة بعملية POS؛ استخدم شاشة الكاشير لتسجيل مرتجعها.');
        }
    }

    private function returnedSummary(array $originalItemIds): array
    {
        if (!$originalItemIds) {
            return [];
        }

        $rows = DB::table('invoice_items as ri')
            ->join('invoices as r', 'r.id', '=', 'ri.invoice_id')
            ->whereIn('ri.original_invoice_item_id', $originalItemIds)
            ->where('r.type', 'sale_return')
            ->where('r.status', '!=', 'cancelled')
            ->whereNull('r.deleted_at')
            ->groupBy('ri.original_invoice_item_id')
            ->selectRaw('ri.original_invoice_item_id, SUM(ri.quantity) as qty, SUM(ri.discount_amount) as discount, SUM(ri.tax_amount) as tax')
            ->get();

        $summary = [];
        foreach ($rows as $row) {
            $summary[(int) $row->original_invoice_item_id] = [
                'quantity' => round((float) $row->qty, 3),
                'discount' => round((float) $row->discount, 2),
                'tax' => round((float) $row->tax, 2),
            ];
        }
        return $summary;
    }

    private function returnedQuantities(array $originalItemIds): array
    {
        return array_map(
            fn ($row) => (float) ($row['quantity'] ?? 0),
            $this->returnedSummary($originalItemIds)
        );
    }
}
