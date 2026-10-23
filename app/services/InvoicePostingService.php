<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Centralized accounting posting for invoices.
 *
 * IMPORTANT:
 * - This service posts the commercial invoice AND its stock movement atomically.
 * - It does NOT create TreasuryTransaction / BankTransaction.
 * - It does NOT treat "cash" as direct cash posting yet.
 *   Cash collection/payment will be handled by Receipt/Payment Voucher services.
 * - Existing historical journal entries / stock movements are not backfilled or touched.
 */
class InvoicePostingService
{
    /**
     * Post an invoice to the GL.
     *
     * Returns the main invoice journal entry. If a sale/sale return has COGS,
     * a second entry is created with reference "<invoice>-COGS" or "<invoice>-RTN".
     */
    public function post(Invoice $invoice): JournalEntry
    {
        return DB::transaction(function () use ($invoice) {
            /*
             * Serialize posting per invoice. This prevents two concurrent
             * requests from creating duplicate JEs or duplicate stock moves.
             */
            $invoice = Invoice::withoutGlobalScopes()
                ->whereKey($invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $invoice->load(['items', 'customer', 'supplier']);

            if (!in_array($invoice->status, ['confirmed', 'partial', 'paid'], true)) {
                throw new RuntimeException(
                    "لا يمكن ترحيل الفاتورة {$invoice->invoice_number} وهي بالحالة {$invoice->status}."
                );
            }

            /*
             * Historical safety / idempotency:
             * if the main commercial JE already exists, do not try to create
             * missing historical inventory movements retroactively.
             */
            $existing = JournalEntry::query()
                ->where('reference', $invoice->invoice_number)
                ->first();

            if ($existing) {
                return $existing;
            }

            $entry = match ($invoice->type) {
                'sale'            => $this->postSale($invoice),
                'purchase'        => $this->postPurchase($invoice),
                'sale_return'     => $this->postSaleReturn($invoice),
                'purchase_return' => $this->postPurchaseReturn($invoice),
                default           => throw new RuntimeException(
                    "نوع فاتورة غير مدعوم: {$invoice->type}"
                ),
            };

            /*
             * Same transaction as the GL entries above. If stock validation
             * fails, the new invoice JE / COGS JE are rolled back too.
             */
            app(InvoiceInventoryPostingService::class)->post($invoice);

            return $entry;
        });
    }

    private function postSale(Invoice $invoice): JournalEntry
    {
        $receivable = $this->account('1201', 'ذمم العملاء');
        $sales      = $this->account('4101', 'مبيعات بضاعة');
        $vat        = $this->account('2104', 'ضريبة القيمة المضافة');

        [$net, $tax, $total] = $this->amounts($invoice);

        $entry = $this->createEntry(
            $invoice,
            'قيد فاتورة بيع رقم ' . $invoice->invoice_number . ' — ' .
                ($invoice->customer?->name ?? 'عميل'),
            [
                [
                    'account_id'  => $receivable->id,
                    'debit'       => $total,
                    'credit'      => 0,
                    'description' => 'ذمة عميل — ' . ($invoice->customer?->name ?? 'عميل'),
                ],
                [
                    'account_id'  => $sales->id,
                    'debit'       => 0,
                    'credit'      => $net,
                    'description' => 'إيراد مبيعات — ' . $invoice->invoice_number,
                ],
                ...($tax > 0 ? [[
                    'account_id'  => $vat->id,
                    'debit'       => 0,
                    'credit'      => $tax,
                    'description' => 'ضريبة قيمة مضافة مستحقة — ' . $invoice->invoice_number,
                ]] : []),
            ]
        );

        $this->postCogs($invoice);

        return $entry;
    }

    private function postPurchase(Invoice $invoice): JournalEntry
    {
        $inventory = $this->account('1301', 'مخزون البضاعة');
        $payable   = $this->account('2101', 'ذمم الموردين');
        $vat       = $this->account('2104', 'ضريبة القيمة المضافة');

        [$net, $tax, $total] = $this->amounts($invoice);

        return $this->createEntry(
            $invoice,
            'قيد فاتورة شراء رقم ' . $invoice->invoice_number . ' — ' .
                ($invoice->supplier?->name ?? 'مورد'),
            [
                [
                    'account_id'  => $inventory->id,
                    'debit'       => $net,
                    'credit'      => 0,
                    'description' => 'استلام بضاعة — ' . $invoice->invoice_number,
                ],
                ...($tax > 0 ? [[
                    // Orion currently has one VAT control account (2104).
                    // Input VAT is debited; output VAT is credited.
                    'account_id'  => $vat->id,
                    'debit'       => $tax,
                    'credit'      => 0,
                    'description' => 'ضريبة قيمة مضافة مدخلات — ' . $invoice->invoice_number,
                ]] : []),
                [
                    'account_id'  => $payable->id,
                    'debit'       => 0,
                    'credit'      => $total,
                    'description' => 'ذمة مورد — ' . ($invoice->supplier?->name ?? 'مورد'),
                ],
            ]
        );
    }

    private function postSaleReturn(Invoice $invoice): JournalEntry
    {
        $receivable = $this->account('1201', 'ذمم العملاء');
        $sales      = $this->account('4101', 'مبيعات بضاعة');
        $vat        = $this->account('2104', 'ضريبة القيمة المضافة');

        [$net, $tax, $total] = $this->amounts($invoice);

        $entry = $this->createEntry(
            $invoice,
            'قيد مرتجع بيع رقم ' . $invoice->invoice_number . ' — ' .
                ($invoice->customer?->name ?? 'عميل'),
            [
                [
                    'account_id'  => $sales->id,
                    'debit'       => $net,
                    'credit'      => 0,
                    'description' => 'مرتجع مبيعات — ' . $invoice->invoice_number,
                ],
                ...($tax > 0 ? [[
                    'account_id'  => $vat->id,
                    'debit'       => $tax,
                    'credit'      => 0,
                    'description' => 'عكس ضريبة مخرجات — ' . $invoice->invoice_number,
                ]] : []),
                [
                    'account_id'  => $receivable->id,
                    'debit'       => 0,
                    'credit'      => $total,
                    'description' => 'تخفيض ذمة العميل — ' . $invoice->invoice_number,
                ],
            ]
        );

        $this->postSaleReturnCost($invoice);

        return $entry;
    }

    private function postPurchaseReturn(Invoice $invoice): JournalEntry
    {
        $inventory = $this->account('1301', 'مخزون البضاعة');
        $payable   = $this->account('2101', 'ذمم الموردين');
        $vat       = $this->account('2104', 'ضريبة القيمة المضافة');

        [$net, $tax, $total] = $this->amounts($invoice);

        return $this->createEntry(
            $invoice,
            'قيد مرتجع شراء رقم ' . $invoice->invoice_number . ' — ' .
                ($invoice->supplier?->name ?? 'مورد'),
            [
                [
                    'account_id'  => $payable->id,
                    'debit'       => $total,
                    'credit'      => 0,
                    'description' => 'تخفيض ذمة المورد — ' . ($invoice->supplier?->name ?? 'مورد'),
                ],
                [
                    'account_id'  => $inventory->id,
                    'debit'       => 0,
                    'credit'      => $net,
                    'description' => 'إرجاع بضاعة للمورد — ' . $invoice->invoice_number,
                ],
                ...($tax > 0 ? [[
                    'account_id'  => $vat->id,
                    'debit'       => 0,
                    'credit'      => $tax,
                    'description' => 'عكس ضريبة مدخلات — ' . $invoice->invoice_number,
                ]] : []),
            ]
        );
    }

    private function postCogs(Invoice $invoice): void
    {
        $invoice->loadMissing('items.product');
        $totalCost = round(
            (float) $invoice->items->sum(
                fn ($item) => $item->product?->isService()
                    ? 0
                    : (float) $item->quantity * (float) ($item->cost_price ?? 0)
            ),
            2
        );

        if ($totalCost <= 0) {
            return;
        }

        $reference = $invoice->invoice_number . '-COGS';

        if (JournalEntry::query()->where('reference', $reference)->exists()) {
            return;
        }

        $cogs      = $this->account('5101', 'تكلفة المبيعات');
        $inventory = $this->account('1301', 'مخزون البضاعة');

        $this->createEntryWithReference(
            $invoice,
            $reference,
            'تكلفة البضاعة المباعة — ' . $invoice->invoice_number,
            [
                [
                    'account_id'  => $cogs->id,
                    'debit'       => $totalCost,
                    'credit'      => 0,
                    'description' => 'تكلفة البضاعة المباعة',
                ],
                [
                    'account_id'  => $inventory->id,
                    'debit'       => 0,
                    'credit'      => $totalCost,
                    'description' => 'إخراج بضاعة من المخزون',
                ],
            ]
        );
    }

    private function postSaleReturnCost(Invoice $invoice): void
    {
        $invoice->loadMissing('items.product');
        $totalCost = round(
            (float) $invoice->items->sum(
                fn ($item) => $item->product?->isService()
                    ? 0
                    : (float) $item->quantity * (float) ($item->cost_price ?? 0)
            ),
            2
        );

        if ($totalCost <= 0) {
            return;
        }

        $reference = $invoice->invoice_number . '-RTN';

        if (JournalEntry::query()->where('reference', $reference)->exists()) {
            return;
        }

        $inventory = $this->account('1301', 'مخزون البضاعة');
        $cogs      = $this->account('5101', 'تكلفة المبيعات');

        $this->createEntryWithReference(
            $invoice,
            $reference,
            'إرجاع بضاعة للمخزون — ' . $invoice->invoice_number,
            [
                [
                    'account_id'  => $inventory->id,
                    'debit'       => $totalCost,
                    'credit'      => 0,
                    'description' => 'إرجاع بضاعة للمخزون',
                ],
                [
                    'account_id'  => $cogs->id,
                    'debit'       => 0,
                    'credit'      => $totalCost,
                    'description' => 'عكس تكلفة المبيعات',
                ],
            ]
        );
    }

    private function createEntry(
        Invoice $invoice,
        string $description,
        array $lines
    ): JournalEntry {
        return $this->createEntryWithReference(
            $invoice,
            $invoice->invoice_number,
            $description,
            $lines
        );
    }

    private function createEntryWithReference(
        Invoice $invoice,
        string $reference,
        string $description,
        array $lines
    ): JournalEntry {
        $totalDebit = round((float) collect($lines)->sum('debit'), 2);
        $totalCredit = round((float) collect($lines)->sum('credit'), 2);

        if (abs($totalDebit - $totalCredit) > 0.009) {
            throw new RuntimeException(
                "القيد غير متزن للمرجع {$reference}: مدين {$totalDebit} / دائن {$totalCredit}"
            );
        }

        $entry = JournalEntry::create([
            'entry_number' => JournalEntry::generateNumber(),
            'entry_date'   => $invoice->invoice_date,
            'reference'    => $reference,
            'description'  => $description,
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
            'status'       => 'posted',
            'created_by'   => auth()->id() ?? $invoice->created_by ?? 1,
        ]);

        foreach ($lines as $line) {
            if ((float) $line['debit'] == 0.0 && (float) $line['credit'] == 0.0) {
                continue;
            }

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $line['account_id'],
                'debit'            => round((float) $line['debit'], 2),
                'credit'           => round((float) $line['credit'], 2),
                'description'      => $line['description'] ?? null,
            ]);
        }

        return $entry;
    }

    /**
     * net = amount excluding VAT after all discounts.
     */
    private function amounts(Invoice $invoice): array
    {
        $total = round((float) $invoice->total, 2);
        $tax   = round((float) $invoice->tax_amount, 2);
        $net   = round($total - $tax, 2);

        if ($total < 0 || $tax < 0 || $net < 0) {
            throw new RuntimeException(
                "قيم مالية غير صالحة في الفاتورة {$invoice->invoice_number}."
            );
        }

        return [$net, $tax, $total];
    }

    private function account(string $code, string $label): Account
    {
        $account = Account::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$account) {
            throw new RuntimeException(
                "الحساب المطلوب غير موجود أو غير نشط: {$code} — {$label}"
            );
        }

        return $account;
    }
}
