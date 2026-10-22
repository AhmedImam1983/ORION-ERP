<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Installment;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ReceiptVoucher;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReceiptPostingService
{
    /**
     * Post a customer receipt.
     *
     * Accounting:
     * Dr Treasury / Bank
     *     Cr 1201 Accounts Receivable
     *
     * The method creates exactly ONE JournalEntry, then creates the operational
     * TreasuryTransaction / BankTransaction linked to that same journal_entry_id.
     *
     * IMPORTANT:
     * We deliberately DO NOT call BankAccount::deposit() because that method
     * creates its own JournalEntry and would cause double posting.
     */
    public function post(array $data): ReceiptVoucher
    {
        return DB::transaction(function () use ($data) {
            $amount = round((float) ($data['amount'] ?? 0), 2);

            if ($amount <= 0) {
                throw new RuntimeException('مبلغ سند القبض يجب أن يكون أكبر من صفر.');
            }

            $invoice = Invoice::query()
                ->lockForUpdate()
                ->findOrFail((int) $data['invoice_id']);

            if ($invoice->type !== 'sale') {
                throw new RuntimeException('سند القبض الحالي مخصص لفواتير البيع فقط.');
            }

            if (!in_array($invoice->status, ['confirmed', 'partial', 'paid'], true)) {
                throw new RuntimeException(
                    'لا يمكن التحصيل على فاتورة غير مرحلة/مؤكدة.'
                );
            }

            /*
             * Regular receipt vouchers still require a customer.
             * POS walk-in sales are the ONLY explicit exception:
             * allow_anonymous must be true and the invoice itself has no customer.
             *
             * The commercial invoice is posted to AR 1201 then settled immediately
             * by this receipt in the same outer POS DB transaction, so AR nets to zero.
             */
            $allowAnonymous = (bool) ($data['allow_anonymous'] ?? false);

            if (!$invoice->customer_id && !$allowAnonymous) {
                throw new RuntimeException('الفاتورة لا تحتوي على عميل مرتبط.');
            }

            $customerId = $invoice->customer_id
                ? (int) ($data['customer_id'] ?? 0)
                : null;

            if (
                $invoice->customer_id
                && $customerId !== (int) $invoice->customer_id
            ) {
                throw new RuntimeException('العميل المختار لا يطابق عميل الفاتورة.');
            }

            $currentPaid = round((float) $invoice->paid_amount, 2);
            $invoiceTotal = round((float) $invoice->total, 2);
            $remaining = round(max($invoiceTotal - $currentPaid, 0), 2);

            if ($remaining <= 0) {
                throw new RuntimeException('الفاتورة مسددة بالكامل بالفعل.');
            }

            if ($amount - $remaining > 0.009) {
                throw new RuntimeException(
                    'مبلغ سند القبض أكبر من المتبقي على الفاتورة (' .
                    number_format($remaining, 2) .
                    ' ر.س).'
                );
            }

            $destinationType = $data['destination_type'] ?? null;

            if (!in_array($destinationType, ['treasury', 'bank'], true)) {
                throw new RuntimeException('يجب اختيار وجهة التحصيل: خزينة أو بنك.');
            }

            $treasury = null;
            $bank = null;

            if ($destinationType === 'treasury') {
                if (empty($data['treasury_id'])) {
                    throw new RuntimeException('يجب اختيار الخزينة.');
                }

                $treasury = Treasury::query()
                    ->lockForUpdate()
                    ->findOrFail((int) $data['treasury_id']);

                if (!$treasury->is_active) {
                    throw new RuntimeException('الخزينة المختارة غير نشطة.');
                }

                $destinationAccount = $this->treasuryAccount($treasury);
            } else {
                if (empty($data['bank_account_id'])) {
                    throw new RuntimeException('يجب اختيار الحساب البنكي.');
                }

                $bank = BankAccount::query()
                    ->lockForUpdate()
                    ->findOrFail((int) $data['bank_account_id']);

                if (!$bank->is_active) {
                    throw new RuntimeException('الحساب البنكي المختار غير نشط.');
                }

                $destinationAccount = $this->bankAccount($bank);
            }

            $receivableAccount = $this->account('1201', 'ذمم العملاء');

            $voucher = ReceiptVoucher::create([
                'voucher_number'    => ReceiptVoucher::generateNumber(),
                'receipt_date'      => $data['receipt_date'] ?? today(),
                'branch_id'         => $invoice->branch_id ?? auth()->user()?->branch_id,
                'customer_id'       => $invoice->customer_id ?: null,
                'invoice_id'        => $invoice->id,
                'installment_id'    => $data['installment_id'] ?? null,
                'destination_type'  => $destinationType,
                'treasury_id'       => $treasury?->id,
                'bank_account_id'   => $bank?->id,
                'amount'            => $amount,
                'payment_method'    => $data['payment_method'] ?? 'cash',
                'reference'         => $data['reference'] ?? null,
                'description'       => $data['description'] ?? null,
                'status'            => 'posted',
                'created_by'        => auth()->id(),
            ]);

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'entry_date'   => $voucher->receipt_date,
                'reference'    => $voucher->voucher_number,
                'description'  => 'سند قبض ' . $voucher->voucher_number .
                    ' — ' . ($invoice->customer?->name ?? 'عميل نقدي') .
                    ' — ' . $invoice->invoice_number,
                'total_debit'  => $amount,
                'total_credit' => $amount,
                'status'       => 'posted',
                'created_by'   => auth()->id() ?? $invoice->created_by ?? 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $destinationAccount->id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => 'تحصيل ' . $voucher->voucher_number .
                    ' إلى ' . $voucher->destination_label,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $receivableAccount->id,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => 'تسوية ذمة العميل — ' . $invoice->invoice_number,
            ]);

            $voucher->update([
                'journal_entry_id' => $entry->id,
            ]);

            if ($treasury) {
                $newBalance = round((float) $treasury->current_balance + $amount, 2);

                $treasury->update([
                    'current_balance' => $newBalance,
                ]);

                TreasuryTransaction::create([
                    'treasury_id'      => $treasury->id,
                    'type'             => 'in',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $voucher->voucher_number,
                    'description'      => 'سند قبض — ' . $invoice->invoice_number,
                    'from_treasury_id' => null,
                    'to_treasury_id'   => $treasury->id,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $entry->id,
                    'transaction_date' => $voucher->receipt_date,
                ]);
            }

            if ($bank) {
                $newBalance = round((float) $bank->current_balance + $amount, 2);

                $bank->update([
                    'current_balance' => $newBalance,
                ]);

                BankTransaction::create([
                    'bank_account_id'  => $bank->id,
                    'type'             => 'deposit',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $voucher->voucher_number,
                    'description'      => 'سند قبض — ' . $invoice->invoice_number,
                    'from_account_id'  => null,
                    'to_account_id'    => $bank->id,
                    'transaction_date' => $voucher->receipt_date,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $entry->id,
                ]);
            }

            if (!empty($data['installment_id'])) {
                $this->applyToInstallment(
                    (int) $data['installment_id'],
                    $invoice,
                    $amount
                );
            }

            $newPaid = round($currentPaid + $amount, 2);
            $newRemaining = round(max($invoiceTotal - $newPaid, 0), 2);

            $invoice->update([
                'paid_amount'      => $newPaid,
                'remaining_amount' => $newRemaining,
                'status'           => $newRemaining <= 0.009 ? 'paid' : 'partial',
            ]);

            return $voucher->fresh([
                'customer',
                'invoice',
                'treasury',
                'bankAccount',
                'journalEntry',
            ]);
        });
    }

    private function applyToInstallment(
        int $installmentId,
        Invoice $invoice,
        float $amount
    ): void {
        $installment = Installment::query()
            ->lockForUpdate()
            ->findOrFail($installmentId);

        if ((int) $installment->invoice_id !== (int) $invoice->id) {
            throw new RuntimeException('القسط المختار لا يتبع الفاتورة المحددة.');
        }

        $installmentRemaining = round((float) $installment->remaining, 2);

        if ($amount - $installmentRemaining > 0.009) {
            throw new RuntimeException('المبلغ أكبر من المتبقي على القسط.');
        }

        $installment->paid_amount =
            round((float) $installment->paid_amount + $amount, 2);

        $installment->updateStatus();
    }

    /**
     * Existing treasuries have account_id = NULL in the current dataset.
     * Until explicit mappings are configured:
     * - main treasury -> 1102
     * - branch/POS treasury -> 1103
     *
     * We DO NOT update account_id automatically here.
     */
    private function treasuryAccount(Treasury $treasury): Account
    {
        if ($treasury->account_id) {
            $mapped = Account::query()
                ->whereKey($treasury->account_id)
                ->where('is_active', true)
                ->first();

            if ($mapped) {
                return $mapped;
            }
        }

        $fallbackCode = $treasury->type === 'main' ? '1102' : '1103';

        return $this->account(
            $fallbackCode,
            $treasury->type === 'main'
                ? 'نقدية بالخزينة الرئيسية'
                : 'نقدية بخزينة الفرع'
        );
    }

    /**
     * Existing bank rows may have account_id = NULL.
     * 1110 is used as the bank control account until explicit bank mappings exist.
     */
    private function bankAccount(BankAccount $bank): Account
    {
        if ($bank->account_id) {
            $mapped = Account::query()
                ->whereKey($bank->account_id)
                ->where('is_active', true)
                ->first();

            if ($mapped) {
                return $mapped;
            }
        }

        return $this->account('1110', 'حسابات بنكية');
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
