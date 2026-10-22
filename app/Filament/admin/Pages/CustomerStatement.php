<?php

namespace App\Filament\Admin\Pages;

use App\Models\Account;
use App\Models\AccountingReversal;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\PaymentVoucher;
use App\Models\ReceiptVoucher;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CustomerStatement extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'accountant']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'كشف حساب العميل';
    protected static ?string $title = 'كشف حساب العميل';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.admin.pages.customer-statement';

    public static function getNavigationGroup(): ?string
    {
        return 'العملاء والموردين · CRM';
    }

    public ?int $customerId = null;
    public string $dateFrom = '';
    public string $dateTo = '';
    public array $statement = [];

    public float $openingBalance = 0;
    public float $totalDebit = 0;
    public float $totalCredit = 0;
    public float $closingBalance = 0;

    public float $totalSales = 0;
    public float $totalPayments = 0;
    public float $totalReturns = 0;
    public float $totalCustomerRefunds = 0;
    public float $netMovement = 0;
    public int $transactionCount = 0;

    public ?Customer $customer = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');

        $customerId = request()->integer('customer');

        if ($customerId > 0 && Customer::whereKey($customerId)->exists()) {
            $this->customerId = $customerId;
            $this->loadStatement();
        }
    }

    public function loadStatement(): void
    {
        if (!$this->customerId) {
            Notification::make()
                ->title('اختر عميلاً أولاً')
                ->warning()
                ->send();

            return;
        }

        if (!$this->dateFrom || !$this->dateTo || $this->dateFrom > $this->dateTo) {
            Notification::make()
                ->title('راجع نطاق التاريخ')
                ->body('تاريخ البداية يجب أن يكون قبل أو مساوياً لتاريخ النهاية.')
                ->warning()
                ->send();

            return;
        }

        $this->customer = Customer::find($this->customerId);

        if (!$this->customer) {
            Notification::make()->title('العميل غير موجود')->danger()->send();
            return;
        }

        $receivableAccount = Account::where('code', '1201')->first();

        if (!$receivableAccount) {
            Notification::make()
                ->title('حساب ذمم العملاء 1201 غير موجود')
                ->danger()
                ->send();

            return;
        }

        /*
         * Preserve the existing Invoice branch scope. Every receipt/refund below is
         * restricted to one of these customer invoices, so the statement cannot leak
         * another branch's commercial documents.
         *
         * The ledger is built from POSTED GL movements on 1201. We do not infer cash
         * movements from invoice.paid_amount.
         */
        $allInvoices = Invoice::query()
            ->where('customer_id', $this->customerId)
            ->whereIn('type', ['sale', 'sale_return'])
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get();

        $invoiceIds = $allInvoices->pluck('id')->map(fn ($id) => (int) $id)->values();
        $invoiceNumbers = $allInvoices->pluck('invoice_number')->filter()->values();
        $saleInvoices = $allInvoices->where('type', 'sale')->values();
        $saleInvoiceIds = $saleInvoices->pluck('id')->map(fn ($id) => (int) $id)->values();
        $returnInvoiceIds = $allInvoices->where('type', 'sale_return')
            ->pluck('id')->map(fn ($id) => (int) $id)->values();

        $invoiceEntries = $invoiceNumbers->isEmpty()
            ? collect()
            : JournalEntry::with('lines')
                ->whereIn('reference', $invoiceNumbers)
                ->where('status', 'posted')
                ->get()
                ->keyBy('reference');

        /* Modern customer receipts: use the voucher and its actual posted JE/date. */
        $receipts = $saleInvoiceIds->isEmpty()
            ? collect()
            : ReceiptVoucher::with(['journalEntry.lines', 'invoice:id,invoice_number'])
                ->where('customer_id', $this->customerId)
                ->whereIn('invoice_id', $saleInvoiceIds)
                ->where(function ($query) {
                    $query->where('purpose', 'customer_receipt')
                        ->orWhereNull('purpose');
                })
                ->get();

        /* POS / sale-return refunds: Dr 1201, Cr treasury/bank. */
        $customerRefunds = $returnInvoiceIds->isEmpty()
            ? collect()
            : PaymentVoucher::with(['journalEntry.lines', 'invoice:id,invoice_number,customer_id'])
                ->where('purpose', 'customer_refund')
                ->whereIn('invoice_id', $returnInvoiceIds)
                ->get()
                ->filter(fn ($voucher) => (int) ($voucher->invoice?->customer_id ?? 0) === (int) $this->customerId)
                ->values();

        /*
         * Legacy compatibility only: before ReceiptVoucher existed, older customer
         * collections were posted as <invoice>-PMT journal entries. Read the exact GL
         * entry if it exists; never fabricate a payment from paid_amount.
         */
        $legacyRefMap = [];
        foreach ($saleInvoices as $invoice) {
            $legacyRefMap[$invoice->invoice_number . '-PMT'] = $invoice;
        }

        $legacyPaymentEntries = empty($legacyRefMap)
            ? collect()
            : JournalEntry::with('lines')
                ->whereIn('reference', array_keys($legacyRefMap))
                ->where('status', 'posted')
                ->get();

        $events = [];
        $receivableAccountId = (int) $receivableAccount->id;

        /* Sale invoices and sale returns: actual posted 1201 movement only. */
        foreach ($allInvoices as $invoice) {
            $entry = $invoiceEntries->get($invoice->invoice_number);
            [$debit, $credit] = $this->receivableAmounts($entry, $receivableAccountId);

            if ($debit == 0.0 && $credit == 0.0) {
                continue;
            }

            $entryDate = $entry?->entry_date?->format('Y-m-d')
                ?? $invoice->invoice_date->format('Y-m-d');

            $events[] = [
                'sort_date'   => $entryDate,
                'sort_id'     => (int) ($entry?->id ?? 0),
                'description' => $invoice->type === 'sale'
                    ? 'فاتورة بيع — ' . $invoice->invoice_number
                    : 'مرتجع بيع — ' . $invoice->invoice_number,
                'reference'   => $invoice->invoice_number,
                'invoice_id'  => (int) $invoice->id,
                'debit'       => $debit,
                'credit'      => $credit,
                'type'        => $invoice->type,
                'type_label'  => $invoice->type === 'sale' ? 'فاتورة بيع' : 'مرتجع بيع',
                'status'      => $invoice->status,
                'source_type' => 'invoice',
                'source_id'   => (int) $invoice->id,
            ];
        }

        foreach ($receipts as $voucher) {
            $entry = $voucher->journalEntry;
            [$debit, $credit] = $this->receivableAmounts($entry, $receivableAccountId);

            if ($debit == 0.0 && $credit == 0.0) {
                continue;
            }

            $date = $entry?->entry_date?->format('Y-m-d')
                ?? $voucher->receipt_date?->format('Y-m-d')
                ?? $voucher->created_at?->format('Y-m-d');

            if (!$date) {
                continue;
            }

            $events[] = [
                'sort_date'   => $date,
                'sort_id'     => (int) ($entry?->id ?? 0),
                'description' => 'تحصيل على الفاتورة — ' . ($voucher->invoice?->invoice_number ?? $voucher->voucher_number),
                'reference'   => $voucher->voucher_number,
                'invoice_id'  => (int) ($voucher->invoice_id ?? 0) ?: null,
                'debit'       => $debit,
                'credit'      => $credit,
                'type'        => 'payment',
                'type_label'  => 'تحصيل',
                'status'      => null,
                'source_type' => 'receipt_voucher',
                'source_id'   => (int) $voucher->id,
            ];
        }

        foreach ($legacyPaymentEntries as $entry) {
            $invoice = $legacyRefMap[$entry->reference] ?? null;
            if (!$invoice) {
                continue;
            }

            [$debit, $credit] = $this->receivableAmounts($entry, $receivableAccountId);
            if ($debit == 0.0 && $credit == 0.0) {
                continue;
            }

            $date = $entry->entry_date?->format('Y-m-d');
            if (!$date) {
                continue;
            }

            $events[] = [
                'sort_date'   => $date,
                'sort_id'     => (int) $entry->id,
                'description' => 'تحصيل على الفاتورة — ' . $invoice->invoice_number,
                'reference'   => $entry->reference,
                'invoice_id'  => (int) $invoice->id,
                'debit'       => $debit,
                'credit'      => $credit,
                'type'        => 'payment',
                'type_label'  => 'تحصيل',
                'status'      => null,
                'source_type' => 'legacy_journal',
                'source_id'   => (int) $entry->id,
            ];
        }

        foreach ($customerRefunds as $voucher) {
            $entry = $voucher->journalEntry;
            [$debit, $credit] = $this->receivableAmounts($entry, $receivableAccountId);

            if ($debit == 0.0 && $credit == 0.0) {
                continue;
            }

            $date = $entry?->entry_date?->format('Y-m-d')
                ?? $voucher->payment_date?->format('Y-m-d')
                ?? $voucher->created_at?->format('Y-m-d');

            if (!$date) {
                continue;
            }

            $events[] = [
                'sort_date'   => $date,
                'sort_id'     => (int) ($entry?->id ?? 0),
                'description' => 'رد مبلغ للعميل — ' . ($voucher->invoice?->invoice_number ?? $voucher->voucher_number),
                'reference'   => $voucher->voucher_number,
                'invoice_id'  => (int) ($voucher->invoice_id ?? 0) ?: null,
                'debit'       => $debit,
                'credit'      => $credit,
                'type'        => 'refund',
                'type_label'  => 'رد للعميل',
                'status'      => null,
                'source_type' => 'payment_voucher',
                'source_id'   => (int) $voucher->id,
            ];
        }

        /* Posted reversal JEs remain part of the ledger and must affect date-range balances. */
        $sourceIds = [
            'invoice'         => $allInvoices->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'receipt_voucher' => $receipts->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'payment_voucher' => $customerRefunds->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ];

        foreach ($sourceIds as $sourceType => $ids) {
            if (empty($ids)) {
                continue;
            }

            $reversals = AccountingReversal::with('reversalJournalEntry.lines')
                ->where('source_type', $sourceType)
                ->whereIn('source_id', $ids)
                ->get();

            foreach ($reversals as $reversal) {
                $entry = $reversal->reversalJournalEntry;
                [$debit, $credit] = $this->receivableAmounts($entry, $receivableAccountId);

                if ($debit == 0.0 && $credit == 0.0) {
                    continue;
                }

                $date = $entry?->entry_date?->format('Y-m-d')
                    ?? $reversal->reversal_date?->format('Y-m-d');

                if (!$date) {
                    continue;
                }

                $events[] = [
                    'sort_date'   => $date,
                    'sort_id'     => (int) ($entry?->id ?? 0),
                    'description' => 'عكس حركة عميل — ' . ($reversal->source_number ?? ''),
                    'reference'   => $reversal->reversal_number,
                    'invoice_id'  => null,
                    'debit'       => $debit,
                    'credit'      => $credit,
                    'type'        => 'reversal',
                    'type_label'  => 'عكس حركة',
                    'status'      => null,
                    'source_type' => 'accounting_reversal',
                    'source_id'   => (int) $reversal->id,
                ];
            }
        }

        usort($events, function (array $a, array $b): int {
            $dateCompare = strcmp($a['sort_date'], $b['sort_date']);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            return ($a['sort_id'] ?? 0) <=> ($b['sort_id'] ?? 0);
        });

        $openingBalance = 0.0;
        foreach ($events as $event) {
            if ($event['sort_date'] >= $this->dateFrom) {
                break;
            }

            $openingBalance += (float) $event['debit'];
            $openingBalance -= (float) $event['credit'];
        }

        $this->openingBalance = round($openingBalance, 2);
        $this->statement = [];
        $this->totalDebit = 0;
        $this->totalCredit = 0;
        $this->totalSales = 0;
        $this->totalPayments = 0;
        $this->totalReturns = 0;
        $this->totalCustomerRefunds = 0;
        $this->netMovement = 0;
        $this->transactionCount = 0;

        $runningBalance = $this->openingBalance;

        if (abs($this->openingBalance) > 0.00001) {
            $this->statement[] = [
                'date' => date('d/m/Y', strtotime($this->dateFrom)),
                'sort_date' => $this->dateFrom,
                'sort_id' => 0,
                'description' => 'رصيد أول المدة',
                'reference' => '-',
                'invoice_id' => null,
                'debit' => $this->openingBalance > 0 ? $this->openingBalance : 0,
                'credit' => $this->openingBalance < 0 ? abs($this->openingBalance) : 0,
                'balance' => $this->openingBalance,
                'type' => 'opening',
                'type_label' => 'رصيد أول المدة',
                'status' => null,
            ];
        }

        foreach ($events as $event) {
            if ($event['sort_date'] < $this->dateFrom || $event['sort_date'] > $this->dateTo) {
                continue;
            }

            $debit = round((float) $event['debit'], 2);
            $credit = round((float) $event['credit'], 2);

            $runningBalance = round($runningBalance + $debit - $credit, 2);
            $this->totalDebit = round($this->totalDebit + $debit, 2);
            $this->totalCredit = round($this->totalCredit + $credit, 2);

            match ($event['type']) {
                'sale'        => $this->totalSales = round($this->totalSales + $debit, 2),
                'payment'     => $this->totalPayments = round($this->totalPayments + $credit, 2),
                'sale_return' => $this->totalReturns = round($this->totalReturns + $credit, 2),
                'refund'      => $this->totalCustomerRefunds = round($this->totalCustomerRefunds + $debit, 2),
                default       => null,
            };

            $this->statement[] = [
                'date'        => date('d/m/Y', strtotime($event['sort_date'])),
                'sort_date'   => $event['sort_date'],
                'sort_id'     => $event['sort_id'],
                'description' => $event['description'],
                'reference'   => $event['reference'],
                'invoice_id'  => $event['invoice_id'],
                'debit'       => $debit,
                'credit'      => $credit,
                'balance'     => $runningBalance,
                'type'        => $event['type'],
                'type_label'  => $event['type_label'],
                'status'      => $event['status'],
            ];
        }

        $this->closingBalance = round($runningBalance, 2);
        $this->netMovement = round($this->closingBalance - $this->openingBalance, 2);
        $this->transactionCount = count(array_filter(
            $this->statement,
            fn (array $row) => $row['type'] !== 'opening'
        ));
    }

    /** @return array{0: float, 1: float} */
    private function receivableAmounts(?JournalEntry $entry, int $receivableAccountId): array
    {
        if (!$entry || $entry->status !== 'posted') {
            return [0.0, 0.0];
        }

        $lines = $entry->relationLoaded('lines') ? $entry->lines : $entry->lines()->get();

        $debit = 0.0;
        $credit = 0.0;

        foreach ($lines as $line) {
            if ((int) $line->account_id !== $receivableAccountId) {
                continue;
            }

            $debit += (float) $line->debit;
            $credit += (float) $line->credit;
        }

        return [round($debit, 2), round($credit, 2)];
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        if (!$this->customer) {
            Notification::make()->title('اعرض كشف الحساب أولاً')->warning()->send();
            abort(422);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'directionality' => 'rtl',
            'default_font' => 'dejavusans',
        ]);

        $html = view('pdf.customer-statement', [
            'customer' => $this->customer,
            'statement' => $this->statement,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'openingBalance' => $this->openingBalance,
            'totalDebit' => $this->totalDebit,
            'totalCredit' => $this->totalCredit,
            'closingBalance' => $this->closingBalance,
            'totalCustomerRefunds' => $this->totalCustomerRefunds,
        ])->render();

        $mpdf->WriteHTML($html);

        return response()->streamDownload(
            fn () => print($mpdf->Output('', 'S')),
            'كشف-حساب-' . ($this->customer?->name ?? 'عميل') . '.pdf'
        );
    }
}
