<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\FinancialTransfer;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinancialTransferPostingService
{
    public function post(array $data): FinancialTransfer
    {
        return DB::transaction(function () use ($data) {
            $amount = round((float) ($data['amount'] ?? 0), 2);

            if ($amount <= 0) {
                throw new RuntimeException('مبلغ التحويل يجب أن يكون أكبر من صفر.');
            }

            $fromType = $data['from_type'] ?? null;
            $toType = $data['to_type'] ?? null;

            if (!in_array($fromType, ['treasury', 'bank'], true)) {
                throw new RuntimeException('نوع مصدر التحويل غير صالح.');
            }

            if (!in_array($toType, ['treasury', 'bank'], true)) {
                throw new RuntimeException('نوع وجهة التحويل غير صالح.');
            }

            [$fromTreasury, $fromBank, $toTreasury, $toBank] =
                $this->lockEndpoints($data, $fromType, $toType);

            $source = $fromType === 'treasury' ? $fromTreasury : $fromBank;
            $destination = $toType === 'treasury' ? $toTreasury : $toBank;

            if (!$source || !$destination) {
                throw new RuntimeException('مصدر أو وجهة التحويل غير موجودة.');
            }

            if (!$source->is_active || !$destination->is_active) {
                throw new RuntimeException('مصدر أو وجهة التحويل غير نشطة.');
            }

            if ((float) $source->current_balance + 0.009 < $amount) {
                throw new RuntimeException(
                    'الرصيد غير كافٍ. الرصيد الحالي: ' .
                    number_format((float) $source->current_balance, 2) .
                    ' ر.س'
                );
            }

            $sourceAccount = $fromType === 'treasury'
                ? $this->treasuryAccount($fromTreasury)
                : $this->bankAccount($fromBank);

            $destinationAccount = $toType === 'treasury'
                ? $this->treasuryAccount($toTreasury)
                : $this->bankAccount($toBank);

            $fromLabel = $fromType === 'treasury'
                ? $fromTreasury->name
                : $this->bankLabel($fromBank);

            $toLabel = $toType === 'treasury'
                ? $toTreasury->name
                : $this->bankLabel($toBank);

            $transfer = FinancialTransfer::create([
                'transfer_number'      => FinancialTransfer::generateNumber(),
                'transfer_date'        => $data['transfer_date'] ?? today(),
                'from_type'            => $fromType,
                'from_treasury_id'     => $fromTreasury?->id,
                'from_bank_account_id' => $fromBank?->id,
                'to_type'              => $toType,
                'to_treasury_id'       => $toTreasury?->id,
                'to_bank_account_id'   => $toBank?->id,
                'amount'               => $amount,
                'external_reference'   => $data['external_reference'] ?? null,
                'description'          => $data['description'] ?? null,
                'status'               => 'posted',
                'created_by'           => auth()->id(),
            ]);

            $sameControlAccount = (int) $sourceAccount->id === (int) $destinationAccount->id;

            $entryDescription =
                'تحويل مالي ' . $transfer->transfer_number .
                ' — من ' . $fromLabel .
                ' إلى ' . $toLabel .
                ($sameControlAccount ? ' — تحويل داخلي تحت نفس الحساب الرقابي' : '');

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'entry_date'   => $transfer->transfer_date,
                'reference'    => $transfer->transfer_number,
                'description'  => $entryDescription,
                'total_debit'  => $amount,
                'total_credit' => $amount,
                'status'       => 'posted',
                'created_by'   => auth()->id() ?? 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $destinationAccount->id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => 'تحويل وارد إلى ' . $toLabel,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $sourceAccount->id,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => 'تحويل صادر من ' . $fromLabel,
            ]);

            $transfer->update(['journal_entry_id' => $entry->id]);

            $this->applyMovements(
                $transfer,
                $entry,
                $fromTreasury,
                $fromBank,
                $toTreasury,
                $toBank,
                $fromLabel,
                $toLabel,
                $amount
            );

            return $transfer->fresh([
                'fromTreasury',
                'fromBankAccount',
                'toTreasury',
                'toBankAccount',
                'journalEntry',
            ]);
        });
    }

    private function lockEndpoints(array $data, string $fromType, string $toType): array
    {
        if ($fromType === 'treasury' && $toType === 'treasury') {
            $fromId = (int) ($data['from_treasury_id'] ?? 0);
            $toId = (int) ($data['to_treasury_id'] ?? 0);

            if (!$fromId || !$toId) {
                throw new RuntimeException('اختر خزينة المصدر وخزينة الوجهة.');
            }

            if ($fromId === $toId) {
                throw new RuntimeException('لا يمكن التحويل إلى نفس الخزينة.');
            }

            $rows = Treasury::query()
                ->whereIn('id', [$fromId, $toId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            return [$rows->get($fromId), null, $rows->get($toId), null];
        }

        if ($fromType === 'bank' && $toType === 'bank') {
            $fromId = (int) ($data['from_bank_account_id'] ?? 0);
            $toId = (int) ($data['to_bank_account_id'] ?? 0);

            if (!$fromId || !$toId) {
                throw new RuntimeException('اختر الحساب البنكي المصدر والوجهة.');
            }

            if ($fromId === $toId) {
                throw new RuntimeException('لا يمكن التحويل إلى نفس الحساب البنكي.');
            }

            $rows = BankAccount::query()
                ->whereIn('id', [$fromId, $toId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            return [null, $rows->get($fromId), null, $rows->get($toId)];
        }

        $treasuryId = $fromType === 'treasury'
            ? (int) ($data['from_treasury_id'] ?? 0)
            : (int) ($data['to_treasury_id'] ?? 0);

        $bankId = $fromType === 'bank'
            ? (int) ($data['from_bank_account_id'] ?? 0)
            : (int) ($data['to_bank_account_id'] ?? 0);

        if (!$treasuryId || !$bankId) {
            throw new RuntimeException('اختر مصدر ووجهة التحويل بالكامل.');
        }

        // Deterministic cross-table lock order: Treasury then Bank.
        $treasury = Treasury::query()
            ->whereKey($treasuryId)
            ->lockForUpdate()
            ->first();

        $bank = BankAccount::query()
            ->whereKey($bankId)
            ->lockForUpdate()
            ->first();

        if ($fromType === 'treasury') {
            return [$treasury, null, null, $bank];
        }

        return [null, $bank, $treasury, null];
    }

    private function applyMovements(
        FinancialTransfer $transfer,
        JournalEntry $entry,
        ?Treasury $fromTreasury,
        ?BankAccount $fromBank,
        ?Treasury $toTreasury,
        ?BankAccount $toBank,
        string $fromLabel,
        string $toLabel,
        float $amount
    ): void {
        if ($fromTreasury) {
            $newBalance = round((float) $fromTreasury->current_balance - $amount, 2);
            $fromTreasury->update(['current_balance' => $newBalance]);

            TreasuryTransaction::create([
                'treasury_id'      => $fromTreasury->id,
                'type'             => 'transfer_out',
                'amount'           => $amount,
                'balance_after'    => $newBalance,
                'reference'        => $transfer->transfer_number,
                'description'      => 'تحويل صادر إلى ' . $toLabel,
                'from_treasury_id' => $fromTreasury->id,
                'to_treasury_id'   => $toTreasury?->id,
                'created_by'       => auth()->id(),
                'journal_entry_id' => $entry->id,
                'transaction_date' => $transfer->transfer_date,
            ]);
        }

        if ($fromBank) {
            $newBalance = round((float) $fromBank->current_balance - $amount, 2);
            $fromBank->update(['current_balance' => $newBalance]);

            BankTransaction::create([
                'bank_account_id'  => $fromBank->id,
                'type'             => 'transfer_out',
                'amount'           => $amount,
                'balance_after'    => $newBalance,
                'reference'        => $transfer->transfer_number,
                'description'      => 'تحويل صادر إلى ' . $toLabel,
                'from_account_id'  => $fromBank->id,
                'to_account_id'    => $toBank?->id,
                'transaction_date' => $transfer->transfer_date,
                'created_by'       => auth()->id(),
                'journal_entry_id' => $entry->id,
            ]);
        }

        if ($toTreasury) {
            $newBalance = round((float) $toTreasury->current_balance + $amount, 2);
            $toTreasury->update(['current_balance' => $newBalance]);

            TreasuryTransaction::create([
                'treasury_id'      => $toTreasury->id,
                'type'             => 'transfer_in',
                'amount'           => $amount,
                'balance_after'    => $newBalance,
                'reference'        => $transfer->transfer_number,
                'description'      => 'تحويل وارد من ' . $fromLabel,
                'from_treasury_id' => $fromTreasury?->id,
                'to_treasury_id'   => $toTreasury->id,
                'created_by'       => auth()->id(),
                'journal_entry_id' => $entry->id,
                'transaction_date' => $transfer->transfer_date,
            ]);
        }

        if ($toBank) {
            $newBalance = round((float) $toBank->current_balance + $amount, 2);
            $toBank->update(['current_balance' => $newBalance]);

            BankTransaction::create([
                'bank_account_id'  => $toBank->id,
                'type'             => 'transfer_in',
                'amount'           => $amount,
                'balance_after'    => $newBalance,
                'reference'        => $transfer->transfer_number,
                'description'      => 'تحويل وارد من ' . $fromLabel,
                'from_account_id'  => $fromBank?->id,
                'to_account_id'    => $toBank->id,
                'transaction_date' => $transfer->transfer_date,
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

        $isMain = $treasury->type === 'main' || (bool) $treasury->is_default;

        return $this->account(
            $isMain ? '1102' : '1103',
            $isMain ? 'نقدية بالخزينة الرئيسية' : 'نقدية بخزينة الفرع'
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

    private function bankLabel(BankAccount $bank): string
    {
        return trim($bank->bank_name . ' — ' . $bank->account_name);
    }
}
