<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ReceiptVoucher;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SupplierRefundPostingService
{
    /**
     * Receive money back from a supplier against a posted purchase return.
     *
     * Accounting:
     *   Dr Treasury / Bank
     *       Cr 2101 Accounts Payable
     *
     * This settles only the supplier debit balance that actually exists after
     * considering the original purchase payment and all linked purchase returns.
     * It never changes inventory and never changes purchase-return paid/remaining fields.
     */
    public function post(array $data): ReceiptVoucher
    {
        return DB::transaction(function () use ($data) {
            $amount = round((float) ($data['amount'] ?? 0), 2);

            if ($amount <= 0) {
                throw new RuntimeException('مبلغ الاسترداد يجب أن يكون أكبر من صفر.');
            }

            $return = Invoice::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail((int) ($data['invoice_id'] ?? 0));

            $this->assertValidReturn($return);

            $original = Invoice::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail((int) $return->original_invoice_id);

            if ($original->type !== 'purchase') {
                throw new RuntimeException('الفاتورة الأصلية المرتبطة ليست فاتورة شراء.');
            }

            if ((int) $original->supplier_id !== (int) $return->supplier_id) {
                throw new RuntimeException('مورد المرتجع لا يطابق مورد فاتورة الشراء الأصلية.');
            }

            // Lock all posted/active linked purchase returns before calculating credit.
            $linkedReturns = Invoice::withoutGlobalScopes()
                ->where('original_invoice_id', $original->id)
                ->where('type', 'purchase_return')
                ->whereIn('status', ['confirmed', 'partial', 'paid'])
                ->lockForUpdate()
                ->get(['id', 'total']);

            $returnIds = $linkedReturns->pluck('id')->map(fn ($id) => (int) $id)->all();
            $cumulativeReturns = round((float) $linkedReturns->sum('total'), 2);

            $alreadyRefundedOverall = $returnIds
                ? round((float) ReceiptVoucher::query()
                    ->where('purpose', 'supplier_refund')
                    ->where('status', 'posted')
                    ->whereIn('invoice_id', $returnIds)
                    ->sum('amount'), 2)
                : 0.0;

            $alreadyRefundedThisReturn = round((float) ReceiptVoucher::query()
                ->where('purpose', 'supplier_refund')
                ->where('status', 'posted')
                ->where('invoice_id', $return->id)
                ->sum('amount'), 2);

            // Supplier debit/credit actually available to collect:
            // paid + returns - original purchase total.
            $supplierCredit = round(max(
                (float) $original->paid_amount + $cumulativeReturns - (float) $original->total,
                0
            ), 2);

            $availableOverall = round(max($supplierCredit - $alreadyRefundedOverall, 0), 2);
            $availableOnThisReturn = round(max((float) $return->total - $alreadyRefundedThisReturn, 0), 2);
            $available = round(min($availableOverall, $availableOnThisReturn), 2);

            if ($available <= 0.009) {
                throw new RuntimeException('لا يوجد رصيد مستحق للاسترداد من المورد على هذا المرتجع.');
            }

            if ($amount - $available > 0.009) {
                throw new RuntimeException(
                    'مبلغ الاسترداد أكبر من الرصيد المتاح (' .
                    number_format($available, 2) . ' ر.س).'
                );
            }

            [$treasury, $bank, $destinationAccount] = $this->resolveDestination($data);
            $payableAccount = $this->account('2101', 'ذمم الموردين');

            $voucher = ReceiptVoucher::create([
                'voucher_number'   => ReceiptVoucher::generateNumber(),
                'receipt_date'     => $data['receipt_date'] ?? today(),
                'branch_id'        => $return->branch_id ?? $original->branch_id ?? auth()->user()?->branch_id,
                'purpose'          => 'supplier_refund',
                'customer_id'      => null,
                'supplier_id'      => $return->supplier_id,
                'invoice_id'       => $return->id,
                'installment_id'   => null,
                'destination_type' => $data['destination_type'],
                'treasury_id'      => $treasury?->id,
                'bank_account_id'  => $bank?->id,
                'amount'           => $amount,
                'payment_method'   => $data['destination_type'] === 'bank' ? 'bank_transfer' : 'cash',
                'reference'        => $data['reference'] ?? null,
                'description'      => $data['description']
                    ?? ('استرداد من المورد عن مرتجع الشراء ' . $return->invoice_number),
                'status'           => 'posted',
                'created_by'       => auth()->id(),
            ]);

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'entry_date'   => $voucher->receipt_date,
                'reference'    => $voucher->voucher_number,
                'description'  => 'استرداد من المورد — ' .
                    ($return->supplier?->name ?? 'مورد') . ' — ' .
                    $return->invoice_number,
                'total_debit'  => $amount,
                'total_credit' => $amount,
                'status'       => 'posted',
                'created_by'   => auth()->id() ?? $return->created_by ?? 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $destinationAccount->id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => 'استلام استرداد المورد إلى ' . $voucher->destination_label,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $payableAccount->id,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => 'تسوية الرصيد المدين للمورد — ' . $return->invoice_number,
            ]);

            $voucher->update(['journal_entry_id' => $entry->id]);

            if ($treasury) {
                $newBalance = round((float) $treasury->current_balance + $amount, 2);
                $treasury->update(['current_balance' => $newBalance]);

                TreasuryTransaction::create([
                    'treasury_id'      => $treasury->id,
                    'type'             => 'in',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $voucher->voucher_number,
                    'description'      => 'استرداد من مورد — ' . $return->invoice_number,
                    'from_treasury_id' => null,
                    'to_treasury_id'   => $treasury->id,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $entry->id,
                    'transaction_date' => $voucher->receipt_date,
                ]);
            } else {
                $newBalance = round((float) $bank->current_balance + $amount, 2);
                $bank->update(['current_balance' => $newBalance]);

                BankTransaction::create([
                    'bank_account_id'  => $bank->id,
                    'type'             => 'deposit',
                    'amount'           => $amount,
                    'balance_after'    => $newBalance,
                    'reference'        => $voucher->voucher_number,
                    'description'      => 'استرداد من مورد — ' . $return->invoice_number,
                    'from_account_id'  => null,
                    'to_account_id'    => $bank->id,
                    'transaction_date' => $voucher->receipt_date,
                    'created_by'       => auth()->id(),
                    'journal_entry_id' => $entry->id,
                ]);
            }

            // Intentionally DO NOT update purchase_return paid_amount / remaining_amount / status.
            // The return is a supplier credit document, not a payable invoice.

            return $voucher->fresh([
                'supplier', 'invoice', 'treasury', 'bankAccount', 'journalEntry.lines.account',
            ]);
        });
    }

    public function availableForReturn(Invoice $return): float
    {
        if ($return->type !== 'purchase_return' || !$return->original_invoice_id) {
            return 0.0;
        }

        $original = Invoice::withoutGlobalScopes()->find($return->original_invoice_id);
        if (!$original || $original->type !== 'purchase') {
            return 0.0;
        }

        $linkedReturns = Invoice::withoutGlobalScopes()
            ->where('original_invoice_id', $original->id)
            ->where('type', 'purchase_return')
            ->whereIn('status', ['confirmed', 'partial', 'paid'])
            ->get(['id', 'total']);

        $returnIds = $linkedReturns->pluck('id')->all();
        $cumulativeReturns = round((float) $linkedReturns->sum('total'), 2);

        $refundedOverall = $returnIds
            ? round((float) ReceiptVoucher::query()
                ->where('purpose', 'supplier_refund')
                ->where('status', 'posted')
                ->whereIn('invoice_id', $returnIds)
                ->sum('amount'), 2)
            : 0.0;

        $refundedThis = round((float) ReceiptVoucher::query()
            ->where('purpose', 'supplier_refund')
            ->where('status', 'posted')
            ->where('invoice_id', $return->id)
            ->sum('amount'), 2);

        $supplierCredit = round(max(
            (float) $original->paid_amount + $cumulativeReturns - (float) $original->total,
            0
        ), 2);

        return round(min(
            max($supplierCredit - $refundedOverall, 0),
            max((float) $return->total - $refundedThis, 0)
        ), 2);
    }

    private function assertValidReturn(Invoice $return): void
    {
        if ($return->type !== 'purchase_return') {
            throw new RuntimeException('استرداد المورد يجب أن يرتبط بمرتجع شراء.');
        }

        if (!in_array($return->status, ['confirmed', 'partial', 'paid'], true)) {
            throw new RuntimeException('مرتجع الشراء غير مرحّل/مؤكد.');
        }

        if (!$return->supplier_id) {
            throw new RuntimeException('مرتجع الشراء لا يحتوي على مورد مرتبط.');
        }

        if (!$return->original_invoice_id) {
            throw new RuntimeException('مرتجع الشراء غير مرتبط بفاتورة شراء أصلية.');
        }

        if (!JournalEntry::query()->where('reference', $return->invoice_number)->where('status', 'posted')->exists()) {
            throw new RuntimeException('لا يوجد قيد محاسبي مرحّل لمرتجع الشراء.');
        }
    }

    private function resolveDestination(array $data): array
    {
        $destinationType = $data['destination_type'] ?? null;
        if (!in_array($destinationType, ['treasury', 'bank'], true)) {
            throw new RuntimeException('يجب اختيار وجهة الاسترداد: خزينة أو بنك.');
        }

        if ($destinationType === 'treasury') {
            if (empty($data['treasury_id'])) {
                throw new RuntimeException('يجب اختيار الخزينة المستلمة.');
            }

            $treasury = Treasury::query()->lockForUpdate()->findOrFail((int) $data['treasury_id']);
            if (!$treasury->is_active) {
                throw new RuntimeException('الخزينة المختارة غير نشطة.');
            }

            return [$treasury, null, $this->treasuryAccount($treasury)];
        }

        if (empty($data['bank_account_id'])) {
            throw new RuntimeException('يجب اختيار الحساب البنكي المستلم.');
        }

        $bank = BankAccount::query()->lockForUpdate()->findOrFail((int) $data['bank_account_id']);
        if (!$bank->is_active) {
            throw new RuntimeException('الحساب البنكي المختار غير نشط.');
        }

        return [null, $bank, $this->bankAccount($bank)];
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
            $treasury->type === 'main' ? 'نقدية بالخزينة الرئيسية' : 'نقدية بخزينة الفرع'
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
            throw new RuntimeException("الحساب المطلوب غير موجود أو غير نشط: {$code} — {$label}");
        }

        return $account;
    }
}
