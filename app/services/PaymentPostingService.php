<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PaymentVoucher;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentPostingService
{
    /**
     * Post a payment voucher.
     *
     * supplier_payment:
     *   Dr 2101 Accounts Payable
     *       Cr Treasury / Bank
     *
     * direct_expense:
     *   Dr Selected expense account
     *       Cr Treasury / Bank
     *
     * Exactly ONE JournalEntry is created, then the operational
     * TreasuryTransaction / BankTransaction is linked to that entry.
     */
    public function post(array $data): PaymentVoucher
    {
        return DB::transaction(function () use ($data) {
            $amount = round((float) ($data['amount'] ?? 0), 2);

            if ($amount <= 0) {
                throw new RuntimeException('مبلغ سند الصرف يجب أن يكون أكبر من صفر.');
            }

            $purpose = $data['purpose'] ?? null;

            if (!in_array($purpose, ['supplier_payment', 'direct_expense', 'customer_refund'], true)) {
                throw new RuntimeException('غرض سند الصرف غير صالح.');
            }

            [$treasury, $bank, $sourceAccount] = $this->resolveSource($data, $amount);

            $invoice = null;
            $supplierId = null;
            $expenseAccount = null;
            $debitAccount = null;

            if ($purpose === 'supplier_payment') {
                [$invoice, $supplierId, $debitAccount] =
                    $this->prepareSupplierPayment($data, $amount);
            } elseif ($purpose === 'customer_refund') {
                [$invoice, $debitAccount] =
                    $this->prepareCustomerRefund($data, $amount);
            } else {
                $expenseAccount = $this->prepareDirectExpense($data);
                $debitAccount = $expenseAccount;
            }

            $branchId =
                $invoice?->branch_id
                ?? $treasury?->branch_id
                ?? $bank?->branch_id
                ?? auth()->user()?->branch_id;

            $voucher = PaymentVoucher::create([
                'voucher_number'    => PaymentVoucher::generateNumber(),
                'payment_date'      => $data['payment_date'] ?? today(),
                'branch_id'         => $branchId,
                'purpose'           => $purpose,
                'supplier_id'       => $supplierId,
                'invoice_id'        => $invoice?->id,
                'expense_account_id'=> $expenseAccount?->id,
                'source_type'       => $data['source_type'],
                'treasury_id'       => $treasury?->id,
                'bank_account_id'   => $bank?->id,
                'amount'            => $amount,
                'payment_method'    => $data['payment_method'] ?? 'cash',
                'beneficiary_name'  => $data['beneficiary_name'] ?? null,
                'reference'         => $data['reference'] ?? null,
                'description'       => $data['description'] ?? null,
                'status'            => 'posted',
                'created_by'        => auth()->id(),
            ]);

            $counterparty = match ($purpose) {
                'supplier_payment' => $invoice?->supplier?->name ?? 'مورد',
                'customer_refund'  => $invoice?->customer?->name
                    ?? $voucher->beneficiary_name
                    ?? 'عميل نقدي',
                default            => $voucher->beneficiary_name ?: $debitAccount->name,
            };

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'entry_date'   => $voucher->payment_date,
                'reference'    => $voucher->voucher_number,
                'description'  => 'سند صرف ' . $voucher->voucher_number .
                    ' — ' . $counterparty .
                    ($invoice ? ' — ' . $invoice->invoice_number : ''),
                'total_debit'  => $amount,
                'total_credit' => $amount,
                'status'       => 'posted',
                'created_by'   => auth()->id() ?? $invoice?->created_by ?? 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $debitAccount->id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => match ($purpose) {
                    'supplier_payment' => 'تسوية ذمة المورد — ' . $invoice->invoice_number,
                    'customer_refund'  => 'تسوية رصيد مرتجع العميل — ' . $invoice->invoice_number,
                    default            => 'مصروف مباشر — ' . $debitAccount->name,
                },
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $sourceAccount->id,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => 'صرف ' . $voucher->voucher_number .
                    ' من ' . $voucher->source_label,
            ]);

            $voucher->update([
                'journal_entry_id' => $entry->id,
            ]);

            $this->recordOperationalMovement(
                $voucher,
                $treasury,
                $bank,
                $amount,
                $entry
            );

            if ($invoice) {
                $currentPaid = round((float) $invoice->paid_amount, 2);
                $invoiceTotal = round((float) $invoice->total, 2);

                $newPaid = round($currentPaid + $amount, 2);
                $newRemaining = round(max($invoiceTotal - $newPaid, 0), 2);

                $invoice->update([
                    'paid_amount'      => $newPaid,
                    'remaining_amount' => $newRemaining,
                    'status'           => $newRemaining <= 0.009 ? 'paid' : 'partial',
                ]);
            }

            return $voucher->fresh([
                'supplier',
                'invoice',
                'expenseAccount',
                'treasury',
                'bankAccount',
                'journalEntry',
            ]);
        });
    }

    private function prepareSupplierPayment(array $data, float $amount): array
    {
        if (empty($data['invoice_id'])) {
            throw new RuntimeException('يجب اختيار فاتورة شراء لسداد المورد.');
        }

        $invoice = Invoice::query()
            ->lockForUpdate()
            ->findOrFail((int) $data['invoice_id']);

        if ($invoice->type !== 'purchase') {
            throw new RuntimeException('السداد يجب أن يكون مرتبطًا بفاتورة شراء.');
        }

        if (!in_array($invoice->status, ['confirmed', 'partial', 'paid'], true)) {
            throw new RuntimeException('لا يمكن سداد فاتورة شراء غير مرحلة/مؤكدة.');
        }

        if (!$invoice->supplier_id) {
            throw new RuntimeException('فاتورة الشراء لا تحتوي على مورد مرتبط.');
        }

        if (!empty($data['supplier_id'])
            && (int) $data['supplier_id'] !== (int) $invoice->supplier_id) {
            throw new RuntimeException('المورد المختار لا يطابق مورد الفاتورة.');
        }

        $currentPaid = round((float) $invoice->paid_amount, 2);
        $invoiceTotal = round((float) $invoice->total, 2);
        $remaining = round(max($invoiceTotal - $currentPaid, 0), 2);

        if ($remaining <= 0) {
            throw new RuntimeException('فاتورة الشراء مسددة بالكامل بالفعل.');
        }

        if ($amount - $remaining > 0.009) {
            throw new RuntimeException(
                'مبلغ سند الصرف أكبر من المتبقي على فاتورة الشراء (' .
                number_format($remaining, 2) .
                ' ر.س).'
            );
        }

        $payable = $this->account('2101', 'ذمم الموردين');

        return [$invoice, (int) $invoice->supplier_id, $payable];
    }

    /**
     * Refundable cash/bank amount for a sale return after offsetting the original unpaid receivable.
     */
    public function availableCustomerRefundAmount(Invoice $return): float
    {
        $remaining = round(max((float) $return->total - (float) $return->paid_amount, 0), 2);
        if ($remaining <= 0.009 || !$return->original_invoice_id) {
            return $remaining;
        }

        $original = Invoice::withoutGlobalScopes()->find($return->original_invoice_id);
        if (!$original || $original->type !== 'sale') {
            return 0.0;
        }

        return min($remaining, $this->customerRefundCapacity($original));
    }

    private function customerRefundCapacity(Invoice $original): float
    {
        $returns = Invoice::withoutGlobalScopes()
            ->where('type', 'sale_return')
            ->where('original_invoice_id', $original->id)
            ->whereIn('status', ['confirmed', 'partial', 'paid'])
            ->get(['total', 'paid_amount']);

        $totalReturnCredits = round((float) $returns->sum('total'), 2);
        $alreadyRefunded = round((float) $returns->sum('paid_amount'), 2);
        $originalOutstanding = round(max((float) $original->total - (float) $original->paid_amount, 0), 2);

        return round(max($totalReturnCredits - $originalOutstanding - $alreadyRefunded, 0), 2);
    }
    private function prepareCustomerRefund(array $data, float $amount): array
    {
        if (empty($data['invoice_id'])) {
            throw new RuntimeException('يجب اختيار فاتورة مرتجع بيع لرد المبلغ للعميل.');
        }

        $invoice = Invoice::withoutGlobalScopes()
            ->lockForUpdate()
            ->findOrFail((int) $data['invoice_id']);

        if ($invoice->type !== 'sale_return') {
            throw new RuntimeException('رد مبلغ العميل يجب أن يكون مرتبطًا بفاتورة مرتجع بيع.');
        }

        if (!in_array($invoice->status, ['confirmed', 'partial', 'paid'], true)) {
            throw new RuntimeException('لا يمكن رد مبلغ لمرتجع غير مرحّل/مؤكد.');
        }

        $currentPaid = round((float) $invoice->paid_amount, 2);
        $invoiceTotal = round((float) $invoice->total, 2);
        $remaining = round(max($invoiceTotal - $currentPaid, 0), 2);

        if ($remaining <= 0.009) {
            throw new RuntimeException('تم رد قيمة فاتورة المرتجع بالكامل بالفعل.');
        }

        if ($amount - $remaining > 0.009) {
            throw new RuntimeException(
                'مبلغ رد العميل أكبر من المتبقي على فاتورة المرتجع (' .
                number_format($remaining, 2) .
                ' ر.س).'
            );
        }

        if ($invoice->original_invoice_id) {
            // Lock the original invoice to serialize refund-capacity checks across its returns.
            $original = Invoice::withoutGlobalScopes()
                ->whereKey($invoice->original_invoice_id)
                ->lockForUpdate()
                ->first();

            if (!$original || $original->type !== 'sale') {
                throw new RuntimeException('تعذر تحديد فاتورة البيع الأصلية المرتبطة بهذا المرتجع.');
            }

            {
                $availableRefund = min($remaining, $this->customerRefundCapacity($original));
                if ($amount - $availableRefund > 0.009) {
                    throw new RuntimeException(
                        'مبلغ الرد أكبر من الرصيد القابل للاسترداد (' .
                        number_format($availableRefund, 2) .
                        ' ر.س). الجزء المقابل لمديونية غير محصلة يُسوّى مع الفاتورة الأصلية ولا يُصرف نقدًا.'
                    );
                }
            }
        }

        return [$invoice, $this->account('1201', 'ذمم العملاء')];
    }

    private function prepareDirectExpense(array $data): Account
    {
        if (empty($data['expense_account_id'])) {
            throw new RuntimeException('يجب اختيار حساب المصروف.');
        }

        $account = Account::query()
            ->whereKey((int) $data['expense_account_id'])
            ->where('is_active', true)
            ->first();

        if (!$account) {
            throw new RuntimeException('حساب المصروف غير موجود أو غير نشط.');
        }

        if (!str_starts_with((string) $account->code, '5')) {
            throw new RuntimeException('الحساب المختار ليس حساب مصروف.');
        }

        if ((string) $account->level !== 'detail') {
            throw new RuntimeException('يجب اختيار حساب مصروف تفصيلي وليس حسابًا تجميعيًا.');
        }

        return $account;
    }

    /**
     * Resolve and lock the payment source.
     *
     * Existing mappings are respected first.
     * Fallbacks are conservative and DO NOT write account_id:
     * - main treasury -> 1102
     * - branch/POS treasury -> 1103
     * - bank -> 1110
     */
    private function resolveSource(array $data, float $amount): array
    {
        $sourceType = $data['source_type'] ?? null;

        if (!in_array($sourceType, ['treasury', 'bank'], true)) {
            throw new RuntimeException('يجب اختيار مصدر الصرف: خزينة أو بنك.');
        }

        if ($sourceType === 'treasury') {
            if (empty($data['treasury_id'])) {
                throw new RuntimeException('يجب اختيار الخزينة.');
            }

            $treasury = Treasury::query()
                ->lockForUpdate()
                ->findOrFail((int) $data['treasury_id']);

            if (!$treasury->is_active) {
                throw new RuntimeException('الخزينة المختارة غير نشطة.');
            }

            if ((float) $treasury->current_balance + 0.009 < $amount) {
                throw new RuntimeException('رصيد الخزينة غير كافٍ.');
            }

            return [$treasury, null, $this->treasuryAccount($treasury)];
        }

        if (empty($data['bank_account_id'])) {
            throw new RuntimeException('يجب اختيار الحساب البنكي.');
        }

        $bank = BankAccount::query()
            ->lockForUpdate()
            ->findOrFail((int) $data['bank_account_id']);

        if (!$bank->is_active) {
            throw new RuntimeException('الحساب البنكي المختار غير نشط.');
        }

        if ((float) $bank->current_balance + 0.009 < $amount) {
            throw new RuntimeException('رصيد الحساب البنكي غير كافٍ.');
        }

        return [null, $bank, $this->bankAccount($bank)];
    }

    private function recordOperationalMovement(
        PaymentVoucher $voucher,
        ?Treasury $treasury,
        ?BankAccount $bank,
        float $amount,
        JournalEntry $entry
    ): void {
        $description = match ($voucher->purpose) {
            'supplier_payment' => 'سند صرف — ' . ($voucher->invoice?->invoice_number ?? 'سداد مورد'),
            'customer_refund'  => 'رد مبلغ لعميل — ' . ($voucher->invoice?->invoice_number ?? 'مرتجع بيع'),
            default            => 'سند صرف — مصروف مباشر',
        };

        if ($treasury) {
            $newBalance = round((float) $treasury->current_balance - $amount, 2);

            $treasury->update([
                'current_balance' => $newBalance,
            ]);

            TreasuryTransaction::create([
                'treasury_id'      => $treasury->id,
                'type'             => 'out',
                'amount'           => $amount,
                'balance_after'    => $newBalance,
                'reference'        => $voucher->voucher_number,
                'description'      => $description,
                'from_treasury_id' => $treasury->id,
                'to_treasury_id'   => null,
                'created_by'       => auth()->id(),
                'journal_entry_id' => $entry->id,
                'transaction_date' => $voucher->payment_date,
            ]);

            return;
        }

        if ($bank) {
            $newBalance = round((float) $bank->current_balance - $amount, 2);

            $bank->update([
                'current_balance' => $newBalance,
            ]);

            BankTransaction::create([
                'bank_account_id'  => $bank->id,
                'type'             => 'withdrawal',
                'amount'           => $amount,
                'balance_after'    => $newBalance,
                'reference'        => $voucher->voucher_number,
                'description'      => $description,
                'from_account_id'  => $bank->id,
                'to_account_id'    => null,
                'transaction_date' => $voucher->payment_date,
                'created_by'       => auth()->id(),
                'journal_entry_id' => $entry->id,
            ]);
        }
    }

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
