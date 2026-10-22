<?php

namespace App\Services;

use App\Models\AccountingReversal;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\FinancialTransfer;
use App\Models\Installment;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PaymentVoucher;
use App\Models\ReceiptVoucher;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AccountingReversalService
{
    /**
     * Reverse a supported posted financial document.
     *
     * We NEVER delete the original document or original journal entry.
     * The workflow creates a separate REV-* journal entry with every
     * original journal line reversed one-for-one, then applies the
     * corresponding subledger/balance reversal atomically.
     */
    public function reverse(Model $source, string $reason): AccountingReversal
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw new RuntimeException('سبب الإلغاء / العكس مطلوب.');
        }

        return match (true) {
            $source instanceof Invoice =>
                $this->reverseInvoice((int) $source->getKey(), $reason),

            $source instanceof ReceiptVoucher =>
                $this->reverseReceiptVoucher((int) $source->getKey(), $reason),

            $source instanceof PaymentVoucher =>
                $this->reversePaymentVoucher((int) $source->getKey(), $reason),

            $source instanceof FinancialTransfer =>
                $this->reverseFinancialTransfer((int) $source->getKey(), $reason),

            default =>
                throw new RuntimeException('هذا النوع من المستندات غير مدعوم في Workflow العكس الحالي.'),
        };
    }

    private function reverseReceiptVoucher(int $id, string $reason): AccountingReversal
    {
        return DB::transaction(function () use ($id, $reason) {
            $voucher = ReceiptVoucher::query()
                ->lockForUpdate()
                ->findOrFail($id);

            $this->assertCanReverse(
                'receipt_voucher',
                $voucher->id,
                $voucher->status,
                $voucher->journal_entry_id,
                $voucher->voucher_number
            );

            $invoice = Invoice::withoutGlobalScopes()
                ->withTrashed()
                ->lockForUpdate()
                ->findOrFail($voucher->invoice_id);

            $amount = round((float) $voucher->amount, 2);
            $currentPaid = round((float) $invoice->paid_amount, 2);

            if ($currentPaid + 0.009 < $amount) {
                throw new RuntimeException(
                    'لا يمكن عكس سند القبض لأن المدفوع الحالي على الفاتورة أقل من قيمة السند.'
                );
            }

            $treasury = null;
            $bank = null;

            if ($voucher->destination_type === 'treasury') {
                $treasury = Treasury::query()
                    ->lockForUpdate()
                    ->findOrFail($voucher->treasury_id);

                if ((float) $treasury->current_balance + 0.009 < $amount) {
                    throw new RuntimeException(
                        'لا يمكن عكس سند القبض: رصيد الخزينة الحالي أقل من قيمة السند.'
                    );
                }
            } elseif ($voucher->destination_type === 'bank') {
                $bank = BankAccount::query()
                    ->lockForUpdate()
                    ->findOrFail($voucher->bank_account_id);

                if ((float) $bank->current_balance + 0.009 < $amount) {
                    throw new RuntimeException(
                        'لا يمكن عكس سند القبض: رصيد الحساب البنكي الحالي أقل من قيمة السند.'
                    );
                }
            } else {
                throw new RuntimeException('وجهة سند القبض غير صالحة للعكس.');
            }

            $reversal = $this->createReversalJournal(
                'receipt_voucher',
                $voucher->id,
                $voucher->voucher_number,
                $voucher->journal_entry_id,
                $reason
            );

            $reversalEntry = $reversal->reversalJournalEntry;

            if ($treasury) {
                $newBalance = round((float) $treasury->current_balance - $amount, 2);

                $treasury->update(['current_balance' => $newBalance]);

                TreasuryTransaction::create([
                    'treasury_id'      => $treasury->id,
                    'type'             => 'out',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $reversal->reversal_number,
                    'description'      => 'عكس سند قبض ' . $voucher->voucher_number,
                    'from_treasury_id' => $treasury->id,
                    'to_treasury_id'   => null,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $reversalEntry->id,
                    'transaction_date' => $reversal->reversal_date,
                ]);
            }

            if ($bank) {
                $newBalance = round((float) $bank->current_balance - $amount, 2);

                $bank->update(['current_balance' => $newBalance]);

                BankTransaction::create([
                    'bank_account_id'  => $bank->id,
                    'type'             => 'withdrawal',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $reversal->reversal_number,
                    'description'      => 'عكس سند قبض ' . $voucher->voucher_number,
                    'from_account_id'  => $bank->id,
                    'to_account_id'    => null,
                    'transaction_date' => $reversal->reversal_date,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $reversalEntry->id,
                ]);
            }

            if ($voucher->installment_id) {
                $installment = Installment::query()
                    ->lockForUpdate()
                    ->findOrFail($voucher->installment_id);

                if ((int) $installment->invoice_id !== (int) $invoice->id) {
                    throw new RuntimeException('القسط المرتبط بالسند لا يتبع نفس الفاتورة.');
                }

                $installmentPaid = round((float) $installment->paid_amount, 2);

                if ($installmentPaid + 0.009 < $amount) {
                    throw new RuntimeException('المدفوع الحالي على القسط أقل من قيمة سند القبض.');
                }

                $installment->paid_amount = round(max($installmentPaid - $amount, 0), 2);
                $installment->updateStatus();
            }

            $this->decreaseInvoicePaid($invoice, $amount);

            $voucher->update(['status' => 'reversed']);

            return $reversal->fresh([
                'originalJournalEntry',
                'reversalJournalEntry',
                'createdBy',
            ]);
        });
    }

    private function reversePaymentVoucher(int $id, string $reason): AccountingReversal
    {
        return DB::transaction(function () use ($id, $reason) {
            $voucher = PaymentVoucher::query()
                ->lockForUpdate()
                ->findOrFail($id);

            $this->assertCanReverse(
                'payment_voucher',
                $voucher->id,
                $voucher->status,
                $voucher->journal_entry_id,
                $voucher->voucher_number
            );

            $amount = round((float) $voucher->amount, 2);

            $invoice = null;

            if (in_array($voucher->purpose, ['supplier_payment', 'customer_refund'], true)) {
                $invoice = Invoice::withoutGlobalScopes()
                    ->withTrashed()
                    ->lockForUpdate()
                    ->findOrFail($voucher->invoice_id);

                if (
                    $voucher->purpose === 'supplier_payment'
                    && $invoice->type !== 'purchase'
                ) {
                    throw new RuntimeException('سند سداد المورد لا يرتبط بفاتورة شراء صالحة.');
                }

                if (
                    $voucher->purpose === 'customer_refund'
                    && $invoice->type !== 'sale_return'
                ) {
                    throw new RuntimeException('سند رد العميل لا يرتبط بفاتورة مرتجع بيع صالحة.');
                }

                $currentPaid = round((float) $invoice->paid_amount, 2);

                if ($currentPaid + 0.009 < $amount) {
                    throw new RuntimeException(
                        $voucher->purpose === 'customer_refund'
                            ? 'لا يمكن عكس رد العميل لأن المسدد الحالي على فاتورة المرتجع أقل من قيمة السند.'
                            : 'لا يمكن عكس سند الصرف لأن المدفوع الحالي على فاتورة الشراء أقل من قيمة السند.'
                    );
                }
            } elseif ($voucher->purpose !== 'direct_expense') {
                throw new RuntimeException('غرض سند الصرف غير مدعوم في Workflow العكس الحالي.');
            }

            $treasury = null;
            $bank = null;

            if ($voucher->source_type === 'treasury') {
                $treasury = Treasury::query()
                    ->lockForUpdate()
                    ->findOrFail($voucher->treasury_id);
            } elseif ($voucher->source_type === 'bank') {
                $bank = BankAccount::query()
                    ->lockForUpdate()
                    ->findOrFail($voucher->bank_account_id);
            } else {
                throw new RuntimeException('مصدر سند الصرف غير صالح للعكس.');
            }

            $reversal = $this->createReversalJournal(
                'payment_voucher',
                $voucher->id,
                $voucher->voucher_number,
                $voucher->journal_entry_id,
                $reason
            );

            $reversalEntry = $reversal->reversalJournalEntry;

            if ($treasury) {
                $newBalance = round((float) $treasury->current_balance + $amount, 2);

                $treasury->update(['current_balance' => $newBalance]);

                TreasuryTransaction::create([
                    'treasury_id'      => $treasury->id,
                    'type'             => 'in',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $reversal->reversal_number,
                    'description'      => 'عكس سند صرف ' . $voucher->voucher_number,
                    'from_treasury_id' => null,
                    'to_treasury_id'   => $treasury->id,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $reversalEntry->id,
                    'transaction_date' => $reversal->reversal_date,
                ]);
            }

            if ($bank) {
                $newBalance = round((float) $bank->current_balance + $amount, 2);

                $bank->update(['current_balance' => $newBalance]);

                BankTransaction::create([
                    'bank_account_id'  => $bank->id,
                    'type'             => 'deposit',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $reversal->reversal_number,
                    'description'      => 'عكس سند صرف ' . $voucher->voucher_number,
                    'from_account_id'  => null,
                    'to_account_id'    => $bank->id,
                    'transaction_date' => $reversal->reversal_date,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $reversalEntry->id,
                ]);
            }

            if ($invoice) {
                $this->decreaseInvoicePaid($invoice, $amount);
            }

            $voucher->update(['status' => 'reversed']);

            return $reversal->fresh([
                'originalJournalEntry',
                'reversalJournalEntry',
                'createdBy',
            ]);
        });
    }

    private function reverseFinancialTransfer(int $id, string $reason): AccountingReversal
    {
        return DB::transaction(function () use ($id, $reason) {
            $transfer = FinancialTransfer::query()
                ->lockForUpdate()
                ->findOrFail($id);

            $this->assertCanReverse(
                'financial_transfer',
                $transfer->id,
                $transfer->status,
                $transfer->journal_entry_id,
                $transfer->transfer_number
            );

            [
                $fromTreasury,
                $fromBank,
                $toTreasury,
                $toBank,
            ] = $this->lockTransferEndpoints($transfer);

            $amount = round((float) $transfer->amount, 2);

            // Reversal sends the funds BACK from original destination to original source.
            $originalDestination = $transfer->to_type === 'treasury'
                ? $toTreasury
                : $toBank;

            if (!$originalDestination) {
                throw new RuntimeException('وجهة التحويل الأصلية غير موجودة.');
            }

            if ((float) $originalDestination->current_balance + 0.009 < $amount) {
                throw new RuntimeException(
                    'لا يمكن عكس التحويل لأن رصيد الوجهة الأصلية الحالي أقل من مبلغ التحويل.'
                );
            }

            $fromLabel = $transfer->from_type === 'treasury'
                ? $fromTreasury->name
                : $this->bankLabel($fromBank);

            $toLabel = $transfer->to_type === 'treasury'
                ? $toTreasury->name
                : $this->bankLabel($toBank);

            $reversal = $this->createReversalJournal(
                'financial_transfer',
                $transfer->id,
                $transfer->transfer_number,
                $transfer->journal_entry_id,
                $reason
            );

            $reversalEntry = $reversal->reversalJournalEntry;

            // Original source receives the money back.
            if ($fromTreasury) {
                $newBalance = round((float) $fromTreasury->current_balance + $amount, 2);

                $fromTreasury->update(['current_balance' => $newBalance]);

                TreasuryTransaction::create([
                    'treasury_id'      => $fromTreasury->id,
                    'type'             => 'transfer_in',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $reversal->reversal_number,
                    'description'      => 'عكس تحويل — وارد من ' . $toLabel,
                    'from_treasury_id' => $toTreasury?->id,
                    'to_treasury_id'   => $fromTreasury->id,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $reversalEntry->id,
                    'transaction_date' => $reversal->reversal_date,
                ]);
            }

            if ($fromBank) {
                $newBalance = round((float) $fromBank->current_balance + $amount, 2);

                $fromBank->update(['current_balance' => $newBalance]);

                BankTransaction::create([
                    'bank_account_id'  => $fromBank->id,
                    'type'             => 'transfer_in',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $reversal->reversal_number,
                    'description'      => 'عكس تحويل — وارد من ' . $toLabel,
                    'from_account_id'  => $toBank?->id,
                    'to_account_id'    => $fromBank->id,
                    'transaction_date' => $reversal->reversal_date,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $reversalEntry->id,
                ]);
            }

            // Original destination sends the money back.
            if ($toTreasury) {
                $newBalance = round((float) $toTreasury->current_balance - $amount, 2);

                $toTreasury->update(['current_balance' => $newBalance]);

                TreasuryTransaction::create([
                    'treasury_id'      => $toTreasury->id,
                    'type'             => 'transfer_out',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $reversal->reversal_number,
                    'description'      => 'عكس تحويل — صادر إلى ' . $fromLabel,
                    'from_treasury_id' => $toTreasury->id,
                    'to_treasury_id'   => $fromTreasury?->id,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $reversalEntry->id,
                    'transaction_date' => $reversal->reversal_date,
                ]);
            }

            if ($toBank) {
                $newBalance = round((float) $toBank->current_balance - $amount, 2);

                $toBank->update(['current_balance' => $newBalance]);

                BankTransaction::create([
                    'bank_account_id'  => $toBank->id,
                    'type'             => 'transfer_out',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $reversal->reversal_number,
                    'description'      => 'عكس تحويل — صادر إلى ' . $fromLabel,
                    'from_account_id'  => $toBank->id,
                    'to_account_id'    => $fromBank?->id,
                    'transaction_date' => $reversal->reversal_date,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $reversalEntry->id,
                ]);
            }

            $transfer->update(['status' => 'reversed']);

            return $reversal->fresh([
                'originalJournalEntry',
                'reversalJournalEntry',
                'createdBy',
            ]);
        });
    }


    /**
     * Reverse a posted commercial invoice.
     *
     * Rules:
     * - The invoice itself is never deleted.
     * - All active receipt/payment settlements must be reversed first.
     * - The original commercial JE is reversed exactly line-for-line.
     * - If a sale / sale return has a separate COGS/RTN JE, that JE is
     *   included in the SAME REV journal entry.
     * - Stock is reversed only from documented original StockMovement rows.
     *   We never invent a historical stock movement.
     * - The invoice ends as "cancelled", paid=0, remaining=0.
     */
    private function reverseInvoice(int $id, string $reason): AccountingReversal
    {
        return DB::transaction(function () use ($id, $reason) {
            $invoice = Invoice::withoutGlobalScopes()
                ->withTrashed()
                ->lockForUpdate()
                ->findOrFail($id);

            if (method_exists($invoice, 'trashed') && $invoice->trashed()) {
                throw new RuntimeException('لا يمكن عكس فاتورة محذوفة.');
            }

            if ($invoice->status === 'cancelled') {
                throw new RuntimeException('تم إلغاء هذه الفاتورة بالفعل.');
            }

            if (!in_array($invoice->status, ['confirmed', 'partial', 'paid'], true)) {
                throw new RuntimeException(
                    'لا يمكن عكس فاتورة غير مرحّلة تجارياً. الحالة الحالية: ' . $invoice->status
                );
            }

            $alreadyReversed = AccountingReversal::query()
                ->where('source_type', 'invoice')
                ->where('source_id', $invoice->id)
                ->exists();

            if ($alreadyReversed) {
                throw new RuntimeException('يوجد قيد عكسي مسجل لهذه الفاتورة بالفعل.');
            }

            /*
             * ZATCA safety:
             * a reported/cleared e-invoice should be handled with the proper
             * credit-note workflow instead of an internal void.
             */
            if ((bool) ($invoice->getAttribute('zatca_sent') ?? false)) {
                throw new RuntimeException(
                    'الفاتورة مرسلة إلى زاتكا. لا تستخدم الإلغاء الداخلي؛ يلزم مسار إشعار دائن / Credit Note.'
                );
            }

            $this->assertInvoiceHasNoActiveSettlement($invoice);

            $invoiceNumber = (string) $invoice->invoice_number;

            $mainEntries = JournalEntry::withTrashed()
                ->where('reference', $invoiceNumber)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($mainEntries->isEmpty()) {
                throw new RuntimeException(
                    'الفاتورة غير مرتبطة بقيد تجاري أصلي بالمرجع ' . $invoiceNumber . '.'
                );
            }

            if ($mainEntries->count() !== 1) {
                throw new RuntimeException(
                    'تم العثور على أكثر من قيد تجاري أصلي بنفس رقم الفاتورة. تم إيقاف العكس للمراجعة.'
                );
            }

            $entries = collect([$mainEntries->first()]);

            $secondaryReference = match ($invoice->type) {
                'sale'        => $invoiceNumber . '-COGS',
                'sale_return' => $invoiceNumber . '-RTN',
                default       => null,
            };

            if ($secondaryReference) {
                $secondaryEntries = JournalEntry::withTrashed()
                    ->where('reference', $secondaryReference)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($secondaryEntries->count() > 1) {
                    throw new RuntimeException(
                        'يوجد أكثر من قيد تكلفة/مخزون للفاتورة. تم إيقاف العكس للمراجعة.'
                    );
                }

                if ($secondaryEntries->count() === 1) {
                    $entries->push($secondaryEntries->first());
                }
            }

            foreach ($entries as $entry) {
                if ($entry->status !== 'posted') {
                    throw new RuntimeException(
                        'أحد القيود الأصلية للفاتورة ليس بحالة مرحّل: ' . $entry->entry_number
                    );
                }
            }

            $reversal = $this->createInvoiceReversalJournal(
                $invoice,
                $entries,
                $reason
            );

            /*
             * Reverse the product subledger only when an original stock
             * movement exists for this exact invoice/reference.
             */
            $this->reverseInvoiceStock($invoice, $reversal);

            $invoice->update([
                'status'           => 'cancelled',
                'paid_amount'      => 0,
                'remaining_amount' => 0,
            ]);

            return $reversal->fresh([
                'originalJournalEntry',
                'reversalJournalEntry',
                'createdBy',
            ]);
        });
    }

    private function assertInvoiceHasNoActiveSettlement(Invoice $invoice): void
    {
        $paidAmount = round((float) $invoice->paid_amount, 2);

        if ($paidAmount > 0.009) {
            throw new RuntimeException(
                'لا يمكن عكس الفاتورة وفيها مبلغ مدفوع. اعكس سندات القبض/الصرف المرتبطة أولاً.'
            );
        }

        $hasReceipt = ReceiptVoucher::query()
            ->withoutGlobalScopes()
            ->where('invoice_id', $invoice->id)
            ->where('status', 'posted')
            ->exists();

        if ($hasReceipt) {
            throw new RuntimeException(
                'يوجد سند قبض مرحّل مرتبط بالفاتورة. يجب عكس سند القبض أولاً.'
            );
        }

        $hasPayment = PaymentVoucher::query()
            ->withoutGlobalScopes()
            ->where('invoice_id', $invoice->id)
            ->where('status', 'posted')
            ->exists();

        if ($hasPayment) {
            throw new RuntimeException(
                'يوجد سند صرف مرحّل مرتبط بالفاتورة. يجب عكس سند الصرف أولاً.'
            );
        }

        if ($invoice->type === 'sale') {
            $hasActiveLinkedReturn = Invoice::withoutGlobalScopes()
                ->where('original_invoice_id', $invoice->id)
                ->where('type', 'sale_return')
                ->whereIn('status', ['confirmed', 'partial', 'paid'])
                ->exists();

            if ($hasActiveLinkedReturn) {
                throw new RuntimeException(
                    'توجد فاتورة مرتجع نشطة مرتبطة بهذه الفاتورة. اعكس سند رد العميل ثم اعكس فاتورة المرتجع أولاً.'
                );
            }
        }

        /*
         * We do not silently delete/cancel installment schedules because
         * their lifecycle is a separate operational workflow.
         */
        if ($invoice->installments()->exists()) {
            throw new RuntimeException(
                'الفاتورة مرتبطة بجدول أقساط. عالج جدول الأقساط أولاً قبل عكس الفاتورة.'
            );
        }
    }

    /**
     * Create ONE REV journal entry that reverses all original invoice JEs.
     * AccountingReversal.original_journal_entry_id points to the main
     * commercial JE; the optional COGS/RTN JE is still reversed line-by-line
     * inside the same REV entry and identified in every line description.
     */
    private function createInvoiceReversalJournal(
        Invoice $invoice,
        $entries,
        string $reason
    ): AccountingReversal {
        $mainEntry = $entries->first();

        $entryIds = $entries
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $lines = JournalEntryLine::query()
            ->whereIn('journal_entry_id', $entryIds)
            ->orderBy('journal_entry_id')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($lines->isEmpty()) {
            throw new RuntimeException('القيود الأصلية للفاتورة لا تحتوي على سطور يمكن عكسها.');
        }

        $linesByEntry = $lines->groupBy('journal_entry_id');

        foreach ($entries as $entry) {
            $entryLines = $linesByEntry->get($entry->id, collect());

            if ($entryLines->isEmpty()) {
                throw new RuntimeException(
                    'القيد الأصلي ' . $entry->entry_number . ' لا يحتوي على سطور.'
                );
            }

            $debit = round((float) $entryLines->sum('debit'), 2);
            $credit = round((float) $entryLines->sum('credit'), 2);

            if (abs($debit - $credit) >= 0.01) {
                throw new RuntimeException(
                    'القيد الأصلي ' . $entry->entry_number . ' غير متوازن.'
                );
            }
        }

        $reversal = AccountingReversal::create([
            'reversal_number'           => AccountingReversal::generateNumber(),
            'reversal_date'             => today(),
            'source_type'               => 'invoice',
            'source_id'                 => $invoice->id,
            'source_number'             => $invoice->invoice_number,
            'original_journal_entry_id' => $mainEntry->id,
            'reason'                    => $reason,
            'created_by'                => auth()->id(),
        ]);

        $totalDebit = round((float) $lines->sum('credit'), 2);
        $totalCredit = round((float) $lines->sum('debit'), 2);

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            throw new RuntimeException('إجمالي القيود الأصلية غير متوازن ولا يمكن عكس الفاتورة.');
        }

        $reversalEntry = JournalEntry::create([
            'entry_number' => JournalEntry::generateNumber(),
            'entry_date'   => $reversal->reversal_date,
            'reference'    => $reversal->reversal_number,
            'description'  => 'قيد عكسي لفاتورة ' . $invoice->invoice_number . ' — ' . $reason,
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
            'status'       => 'posted',
            'created_by'   => auth()->id() ?? $invoice->created_by ?? 1,
        ]);

        $entryReferences = $entries
            ->keyBy('id')
            ->map(fn ($entry) => (string) $entry->reference);

        foreach ($lines as $line) {
            $originalReference = $entryReferences->get($line->journal_entry_id)
                ?? $invoice->invoice_number;

            JournalEntryLine::create([
                'journal_entry_id' => $reversalEntry->id,
                'account_id'       => $line->account_id,
                'debit'            => round((float) $line->credit, 2),
                'credit'           => round((float) $line->debit, 2),
                'description'      => 'عكس [' . $originalReference . ']: ' .
                    ($line->description ?: $invoice->invoice_number),
            ]);
        }

        $reversal->update([
            'reversal_journal_entry_id' => $reversalEntry->id,
        ]);

        return $reversal->fresh('reversalJournalEntry');
    }

    /**
     * Reverse only documented stock movements.
     *
     * This is intentionally evidence-based: if an old invoice has no
     * StockMovement row, we do not manufacture a stock history entry or
     * change product quantity retroactively.
     */
    private function reverseInvoiceStock(
        Invoice $invoice,
        AccountingReversal $reversal
    ): void {
        $movements = StockMovement::query()
            ->where('invoice_id', $invoice->id)
            ->where('reference', $invoice->invoice_number)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($movements->isEmpty()) {
            return;
        }

        $productIds = $movements
            ->pluck('product_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($movements as $movement) {
            $product = $products->get((int) $movement->product_id);

            if (!$product) {
                throw new RuntimeException(
                    'تعذر عكس حركة المخزون لأن أحد الأصناف الأصلية غير موجود.'
                );
            }

            $qty = round((float) $movement->quantity, 3);

            if ($qty <= 0) {
                throw new RuntimeException('كمية غير صالحة في حركة المخزون الأصلية.');
            }

            $reverseType = match ($movement->type) {
                'sale'            => 'sale_return',
                'purchase'        => 'purchase_return',
                'sale_return'     => 'sale',
                'purchase_return' => 'purchase',
                default           => throw new RuntimeException(
                    'نوع حركة مخزون غير مدعوم للعكس: ' . $movement->type
                ),
            };

            $balanceBefore = round((float) $product->quantity, 3);

            $originalWasOut = in_array(
                $movement->type,
                ['sale', 'purchase_return'],
                true
            );

            if ($originalWasOut) {
                $balanceAfter = round($balanceBefore + $qty, 3);
            } else {
                if ($balanceBefore + 0.0009 < $qty) {
                    throw new RuntimeException(
                        'لا يمكن عكس الفاتورة لأن رصيد الصنف الحالي أقل من الكمية المطلوب إخراجها.'
                    );
                }

                $balanceAfter = round($balanceBefore - $qty, 3);
            }

            $product->update([
                'quantity' => $balanceAfter,
            ]);

            StockMovement::create([
                'product_id'     => $product->id,
                'warehouse_id'   => $movement->warehouse_id,
                'branch_id'      => $movement->branch_id,
                'invoice_id'     => $invoice->id,
                'type'           => $reverseType,
                'quantity'       => $qty,
                'unit_price'     => $movement->unit_price,
                'total'          => $movement->total,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'reference'      => $reversal->reversal_number,
                'created_by'     => auth()->id() ?? $invoice->created_by ?? 1,
            ]);
        }
    }

    private function assertCanReverse(
        string $sourceType,
        int $sourceId,
        string $status,
        ?int $journalEntryId,
        string $sourceNumber
    ): void {
        if ($status !== 'posted') {
            throw new RuntimeException(
                $status === 'reversed'
                    ? 'تم عكس هذا المستند بالفعل.'
                    : 'لا يمكن عكس مستند غير مرحّل.'
            );
        }

        if (!$journalEntryId) {
            throw new RuntimeException(
                'المستند ' . $sourceNumber . ' غير مرتبط بقيد يومية، لذلك تم إيقاف العكس.'
            );
        }

        $exists = AccountingReversal::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->exists();

        if ($exists) {
            throw new RuntimeException('يوجد قيد عكسي مسجل لهذا المستند بالفعل.');
        }
    }

    private function createReversalJournal(
        string $sourceType,
        int $sourceId,
        string $sourceNumber,
        int $originalJournalEntryId,
        string $reason
    ): AccountingReversal {
        $originalEntry = JournalEntry::withTrashed()
            ->lockForUpdate()
            ->findOrFail($originalJournalEntryId);

        if ($originalEntry->status !== 'posted') {
            throw new RuntimeException('القيد الأصلي ليس بحالة مرحّل.');
        }

        $originalLines = JournalEntryLine::query()
            ->where('journal_entry_id', $originalEntry->id)
            ->orderBy('id')
            ->get();

        if ($originalLines->isEmpty()) {
            throw new RuntimeException('القيد الأصلي لا يحتوي على سطور يمكن عكسها.');
        }

        $reversal = AccountingReversal::create([
            'reversal_number'          => AccountingReversal::generateNumber(),
            'reversal_date'            => today(),
            'source_type'              => $sourceType,
            'source_id'                => $sourceId,
            'source_number'            => $sourceNumber,
            'original_journal_entry_id'=> $originalEntry->id,
            'reason'                   => $reason,
            'created_by'               => auth()->id(),
        ]);

        $totalDebit = round((float) $originalLines->sum('credit'), 2);
        $totalCredit = round((float) $originalLines->sum('debit'), 2);

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            throw new RuntimeException('القيد الأصلي غير متوازن ولا يمكن إنشاء قيد عكسي آمن.');
        }

        $reversalEntry = JournalEntry::create([
            'entry_number' => JournalEntry::generateNumber(),
            'entry_date'   => $reversal->reversal_date,
            'reference'    => $reversal->reversal_number,
            'description'  => 'قيد عكسي لـ ' . $sourceNumber . ' — ' . $reason,
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
            'status'       => 'posted',
            'created_by'   => auth()->id() ?? $originalEntry->created_by ?? 1,
        ]);

        foreach ($originalLines as $line) {
            JournalEntryLine::create([
                'journal_entry_id' => $reversalEntry->id,
                'account_id'       => $line->account_id,
                'debit'            => round((float) $line->credit, 2),
                'credit'           => round((float) $line->debit, 2),
                'description'      => 'عكس: ' . ($line->description ?: $sourceNumber),
            ]);
        }

        $reversal->update([
            'reversal_journal_entry_id' => $reversalEntry->id,
        ]);

        return $reversal->fresh('reversalJournalEntry');
    }

    private function decreaseInvoicePaid(Invoice $invoice, float $amount): void
    {
        $currentPaid = round((float) $invoice->paid_amount, 2);

        if ($currentPaid + 0.009 < $amount) {
            throw new RuntimeException('المدفوع الحالي على الفاتورة أقل من قيمة المستند المراد عكسه.');
        }

        $invoiceTotal = round((float) $invoice->total, 2);
        $newPaid = round(max($currentPaid - $amount, 0), 2);
        $newRemaining = round(max($invoiceTotal - $newPaid, 0), 2);

        $newStatus = $newRemaining <= 0.009
            ? 'paid'
            : ($newPaid > 0.009 ? 'partial' : 'confirmed');

        $invoice->update([
            'paid_amount'      => $newPaid,
            'remaining_amount' => $newRemaining,
            'status'           => $newStatus,
        ]);
    }

    private function lockTransferEndpoints(FinancialTransfer $transfer): array
    {
        if ($transfer->from_type === 'treasury' && $transfer->to_type === 'treasury') {
            $fromId = (int) $transfer->from_treasury_id;
            $toId = (int) $transfer->to_treasury_id;

            $rows = Treasury::query()
                ->whereIn('id', [$fromId, $toId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            return [$rows->get($fromId), null, $rows->get($toId), null];
        }

        if ($transfer->from_type === 'bank' && $transfer->to_type === 'bank') {
            $fromId = (int) $transfer->from_bank_account_id;
            $toId = (int) $transfer->to_bank_account_id;

            $rows = BankAccount::query()
                ->whereIn('id', [$fromId, $toId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            return [null, $rows->get($fromId), null, $rows->get($toId)];
        }

        $treasuryId = $transfer->from_type === 'treasury'
            ? (int) $transfer->from_treasury_id
            : (int) $transfer->to_treasury_id;

        $bankId = $transfer->from_type === 'bank'
            ? (int) $transfer->from_bank_account_id
            : (int) $transfer->to_bank_account_id;

        $treasury = Treasury::query()
            ->whereKey($treasuryId)
            ->lockForUpdate()
            ->first();

        $bank = BankAccount::query()
            ->whereKey($bankId)
            ->lockForUpdate()
            ->first();

        if ($transfer->from_type === 'treasury') {
            return [$treasury, null, null, $bank];
        }

        return [null, $bank, $treasury, null];
    }

    private function bankLabel(?BankAccount $bank): string
    {
        if (!$bank) {
            return 'حساب بنكي';
        }

        return trim($bank->bank_name . ' — ' . $bank->account_name);
    }
}
