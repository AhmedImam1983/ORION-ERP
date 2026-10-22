<?php

namespace App\Filament\Admin\Pages;

use App\Models\Account;
use App\Models\AccountingReversal;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\PaymentVoucher;
use App\Models\ReceiptVoucher;
use App\Models\Supplier;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SupplierStatement extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'accountant']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'كشف حساب المورد';
    protected static ?string $title = 'كشف حساب المورد';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.admin.pages.supplier-statement';

    public static function getNavigationGroup(): ?string
    {
        return 'العملاء والموردين · CRM';
    }

    public ?int $supplierId = null;
    public string $dateFrom = '';
    public string $dateTo   = '';

    public array $statement = [];

    public float $openingBalance       = 0;
    public float $totalDebit           = 0;
    public float $totalCredit          = 0;
    public float $closingBalance       = 0;
    public float $totalPurchases       = 0;
    public float $totalPurchaseReturns = 0;
    public float $totalPayments        = 0;
    public float $totalSupplierRefunds = 0;

    public ?Supplier $supplier = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');

        $requestedSupplierId = (int) request()->query('supplierId', 0);

        if ($requestedSupplierId > 0) {
            $this->supplierId = $requestedSupplierId;
            $this->loadStatement();
        }
    }

    public function loadStatement(): void
    {
        if (!$this->supplierId) {
            Notification::make()
                ->title('اختر مورداً أولاً')
                ->warning()
                ->send();

            return;
        }

        if (!$this->dateFrom || !$this->dateTo || $this->dateFrom > $this->dateTo) {
            Notification::make()
                ->title('فترة كشف الحساب غير صحيحة')
                ->body('تأكد أن تاريخ البداية يسبق أو يساوي تاريخ النهاية.')
                ->danger()
                ->send();

            return;
        }

        $this->supplier = Supplier::find($this->supplierId);

        if (!$this->supplier) {
            Notification::make()->title('المورد غير موجود')->danger()->send();
            return;
        }

        $payableAccount = Account::where('code', '2101')->first();

        if (!$payableAccount) {
            Notification::make()
                ->title('حساب ذمم الموردين 2101 غير موجود')
                ->danger()
                ->send();

            return;
        }

        /*
         * Preserve the existing branch scope on invoices. Any payment/refund included
         * below must point to one of these supplier invoices, preventing cross-branch
         * leakage while keeping the statement tied to the same commercial documents.
         */
        $allInvoices = Invoice::where('supplier_id', $this->supplierId)
            ->whereIn('type', ['purchase', 'purchase_return'])
            ->orderBy('invoice_date')
            ->get();

        $invoiceIds = $allInvoices->pluck('id')->map(fn ($id) => (int) $id)->values();
        $invoiceNumbers = $allInvoices->pluck('invoice_number')->filter()->values();

        $invoiceEntries = JournalEntry::with('lines')
            ->whereIn('reference', $invoiceNumbers)
            ->where('status', 'posted')
            ->get()
            ->keyBy('reference');

        $payments = $invoiceIds->isEmpty()
            ? collect()
            : PaymentVoucher::with(['journalEntry.lines', 'invoice:id,invoice_number'])
                ->where('supplier_id', $this->supplierId)
                ->where('purpose', 'supplier_payment')
                ->whereIn('invoice_id', $invoiceIds)
                ->get();

        $supplierRefunds = $invoiceIds->isEmpty()
            ? collect()
            : ReceiptVoucher::with(['journalEntry.lines', 'invoice:id,invoice_number'])
                ->where('supplier_id', $this->supplierId)
                ->where('purpose', 'supplier_refund')
                ->whereIn('invoice_id', $invoiceIds)
                ->get();

        $events = [];
        $payableAccountId = (int) $payableAccount->id;

        /* Purchase invoices and purchase returns: use the actual posted GL entry. */
        foreach ($allInvoices as $invoice) {
            $entry = $invoiceEntries->get($invoice->invoice_number);
            [$debit, $credit] = $this->payableAmounts($entry, $payableAccountId);

            if ($debit == 0.0 && $credit == 0.0) {
                continue;
            }

            $entryDate = $entry?->entry_date?->format('Y-m-d')
                ?? $invoice->invoice_date->format('Y-m-d');

            $events[] = [
                'sort_date'   => $entryDate,
                'sort_id'     => (int) ($entry?->id ?? 0),
                'description' => $invoice->type === 'purchase'
                    ? 'فاتورة شراء — ' . $invoice->invoice_number
                    : 'مرتجع شراء — ' . $invoice->invoice_number,
                'reference'   => $invoice->invoice_number,
                'debit'       => $debit,
                'credit'      => $credit,
                'type'        => $invoice->type,
                'status'      => $invoice->status,
                'source_type' => 'invoice',
                'source_id'   => (int) $invoice->id,
            ];
        }

        /* Supplier payments: never infer them from invoice.paid_amount. */
        foreach ($payments as $voucher) {
            $entry = $voucher->journalEntry;
            [$debit, $credit] = $this->payableAmounts($entry, $payableAccountId);

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
                'description' => 'دفعة للمورد — ' . ($voucher->invoice?->invoice_number ?? $voucher->voucher_number),
                'reference'   => $voucher->voucher_number,
                'debit'       => $debit,
                'credit'      => $credit,
                'type'        => 'payment',
                'status'      => $voucher->status,
                'source_type' => 'payment_voucher',
                'source_id'   => (int) $voucher->id,
            ];
        }

        /* Supplier cash/bank refunds: Dr cash/bank, Cr 2101. */
        foreach ($supplierRefunds as $voucher) {
            $entry = $voucher->journalEntry;
            [$debit, $credit] = $this->payableAmounts($entry, $payableAccountId);

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
                'description' => 'استرداد من المورد — ' . ($voucher->invoice?->invoice_number ?? $voucher->voucher_number),
                'reference'   => $voucher->voucher_number,
                'debit'       => $debit,
                'credit'      => $credit,
                'type'        => 'supplier_refund',
                'status'      => $voucher->status,
                'source_type' => 'receipt_voucher',
                'source_id'   => (int) $voucher->id,
            ];
        }

        /*
         * Preserve GL history when a source document is reversed. The original entry
         * stays posted, and the reversal JE is a separate posted movement. Including
         * both makes opening/closing balances correct for arbitrary date ranges.
         */
        $sourceIds = [
            'invoice'         => $allInvoices->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'payment_voucher' => $payments->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'receipt_voucher' => $supplierRefunds->pluck('id')->map(fn ($id) => (int) $id)->all(),
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
                [$debit, $credit] = $this->payableAmounts($entry, $payableAccountId);

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
                    'description' => 'عكس حركة مورد — ' . ($reversal->source_number ?? ''),
                    'reference'   => $reversal->reversal_number,
                    'debit'       => $debit,
                    'credit'      => $credit,
                    'type'        => 'reversal',
                    'status'      => 'posted',
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

            $openingBalance += (float) $event['credit'];
            $openingBalance -= (float) $event['debit'];
        }

        $this->openingBalance = round($openingBalance, 2);
        $this->statement = [];
        $this->totalDebit = 0;
        $this->totalCredit = 0;
        $this->totalPurchases = 0;
        $this->totalPurchaseReturns = 0;
        $this->totalPayments = 0;
        $this->totalSupplierRefunds = 0;

        $runningBalance = $this->openingBalance;

        if (abs($this->openingBalance) > 0.00001) {
            $this->statement[] = [
                'date'        => date('d/m/Y', strtotime($this->dateFrom)),
                'sort_date'   => $this->dateFrom,
                'sort_id'     => 0,
                'description' => 'رصيد أول المدة',
                'reference'   => '-',
                'debit'       => $this->openingBalance < 0 ? abs($this->openingBalance) : 0,
                'credit'      => $this->openingBalance > 0 ? $this->openingBalance : 0,
                'balance'     => $this->openingBalance,
                'type'        => 'opening',
                'status'      => null,
            ];
        }

        foreach ($events as $event) {
            if ($event['sort_date'] < $this->dateFrom || $event['sort_date'] > $this->dateTo) {
                continue;
            }

            $debit = round((float) $event['debit'], 2);
            $credit = round((float) $event['credit'], 2);

            $runningBalance = round($runningBalance + $credit - $debit, 2);

            $this->totalDebit = round($this->totalDebit + $debit, 2);
            $this->totalCredit = round($this->totalCredit + $credit, 2);

            match ($event['type']) {
                'purchase'        => $this->totalPurchases = round($this->totalPurchases + $credit, 2),
                'purchase_return' => $this->totalPurchaseReturns = round($this->totalPurchaseReturns + $debit, 2),
                'payment'         => $this->totalPayments = round($this->totalPayments + $debit, 2),
                'supplier_refund' => $this->totalSupplierRefunds = round($this->totalSupplierRefunds + $credit, 2),
                default           => null,
            };

            $this->statement[] = [
                'date'        => date('d/m/Y', strtotime($event['sort_date'])),
                'sort_date'   => $event['sort_date'],
                'sort_id'     => $event['sort_id'],
                'description' => $event['description'],
                'reference'   => $event['reference'],
                'debit'       => $debit,
                'credit'      => $credit,
                'balance'     => $runningBalance,
                'type'        => $event['type'],
                'status'      => $event['status'],
            ];
        }

        $this->closingBalance = round($runningBalance, 2);
    }

    /** @return array{0: float, 1: float} */
    private function payableAmounts(?JournalEntry $entry, int $payableAccountId): array
    {
        if (!$entry || $entry->status !== 'posted') {
            return [0.0, 0.0];
        }

        $lines = $entry->relationLoaded('lines') ? $entry->lines : $entry->lines()->get();

        $debit = 0.0;
        $credit = 0.0;

        foreach ($lines as $line) {
            if ((int) $line->account_id !== $payableAccountId) {
                continue;
            }

            $debit += (float) $line->debit;
            $credit += (float) $line->credit;
        }

        return [round($debit, 2), round($credit, 2)];
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        if (!$this->supplier) {
            Notification::make()->title('اعرض كشف الحساب أولاً')->warning()->send();
            abort(422);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode'           => 'utf-8',
            'format'         => 'A4',
            'directionality' => 'rtl',
            'default_font'   => 'dejavusans',
        ]);

        $html = view('pdf.supplier-statement', [
            'supplier'             => $this->supplier,
            'statement'            => $this->statement,
            'dateFrom'             => $this->dateFrom,
            'dateTo'               => $this->dateTo,
            'openingBalance'       => $this->openingBalance,
            'totalDebit'           => $this->totalDebit,
            'totalCredit'          => $this->totalCredit,
            'closingBalance'       => $this->closingBalance,
            'totalSupplierRefunds' => $this->totalSupplierRefunds,
        ])->render();

        $mpdf->WriteHTML($html);

        return response()->streamDownload(
            fn() => print($mpdf->Output('', 'S')),
            'كشف-حساب-' . ($this->supplier?->name ?? 'مورد') . '.pdf'
        );
    }
}
