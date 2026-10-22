<?php

namespace App\Filament\Admin\Pages;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ProfitLoss extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'accountant']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'الأرباح والخسائر';
    protected static ?string $title = 'قائمة الأرباح والخسائر · P&L';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.admin.pages.profit-loss';

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public string $dateFrom = '';
    public string $dateTo = '';
    public string $comparisonFrom = '';
    public string $comparisonTo = '';
    public string $viewMode = 'detailed';
    public bool $showComparison = true;
    public bool $loaded = false;

    // الإيرادات
    public array $salesRevenueDetails = [];
    public array $otherRevenueDetails = [];
    public float $salesRevenue = 0;
    public float $otherRevenue = 0;
    public float $totalRevenue = 0;

    // التكاليف والمصروفات
    public array $cogsDetails = [];
    public array $opexDetails = [];
    public array $financialDetails = [];
    public array $otherExpenseDetails = [];

    public float $totalCogs = 0;
    public float $totalOpex = 0;
    public float $totalFinancial = 0;
    public float $totalOtherExpenses = 0;

    // الأرباح والهوامش
    public float $grossProfit = 0;
    public float $operatingProfit = 0;
    public float $profitBeforeTax = 0;
    public float $netProfit = 0;

    public float $grossProfitMargin = 0;
    public float $operatingProfitMargin = 0;
    public float $netProfitMargin = 0;

    // المقارنة
    public float $previousSalesRevenue = 0;
    public float $previousOtherRevenue = 0;
    public float $previousTotalRevenue = 0;
    public float $previousCogs = 0;
    public float $previousOpex = 0;
    public float $previousFinancial = 0;
    public float $previousOtherExpenses = 0;
    public float $previousGrossProfit = 0;
    public float $previousOperatingProfit = 0;
    public float $previousNetProfit = 0;

    public int $reverseNatureCount = 0;

    // Drill-down
    public ?int $selectedAccountId = null;
    public string $selectedAccountCode = '';
    public string $selectedAccountName = '';
    public string $selectedAccountType = '';
    public array $selectedLedger = [];
    public array $selectedSummary = [];

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->syncComparisonDates();
        $this->loadReport();
    }

    public function setPeriod(string $period): void
    {
        match ($period) {
            'today' => [$this->dateFrom, $this->dateTo] = [
                now()->format('Y-m-d'),
                now()->format('Y-m-d'),
            ],
            'month' => [$this->dateFrom, $this->dateTo] = [
                now()->startOfMonth()->format('Y-m-d'),
                now()->format('Y-m-d'),
            ],
            'quarter' => [$this->dateFrom, $this->dateTo] = [
                now()->startOfQuarter()->format('Y-m-d'),
                now()->format('Y-m-d'),
            ],
            default => [$this->dateFrom, $this->dateTo] = [
                now()->startOfYear()->format('Y-m-d'),
                now()->format('Y-m-d'),
            ],
        };

        $this->syncComparisonDates();
        $this->loadReport();
    }

    public function loadReport(): void
    {
        if (!$this->dateFrom || !$this->dateTo || $this->dateFrom > $this->dateTo) {
            Notification::make()
                ->title('راجع الفترة')
                ->body('تاريخ البداية يجب أن يكون قبل أو مساوياً لتاريخ النهاية.')
                ->warning()
                ->send();

            return;
        }

        $this->syncComparisonDates();
        $this->resetReport();

        $current = $this->loadPeriodAmounts($this->dateFrom, $this->dateTo);
        $previous = $this->showComparison
            ? $this->loadPeriodAmounts($this->comparisonFrom, $this->comparisonTo)
            : [];

        $previousByAccount = collect($previous)->keyBy('id');

        foreach ($current as $row) {
            $previousAmount = (float) ($previousByAccount->get($row['id'])['amount'] ?? 0);
            $item = $this->makeItem($row, $previousAmount);

            if ($row['type'] === 'revenue') {
                if (str_starts_with((string) $row['code'], '41')) {
                    $this->salesRevenueDetails[] = $item;
                    $this->salesRevenue += $row['amount'];
                    $this->previousSalesRevenue += $previousAmount;
                } else {
                    $this->otherRevenueDetails[] = $item;
                    $this->otherRevenue += $row['amount'];
                    $this->previousOtherRevenue += $previousAmount;
                }
            } elseif ($row['type'] === 'expense') {
                if (str_starts_with((string) $row['code'], '51')) {
                    $this->cogsDetails[] = $item;
                    $this->totalCogs += $row['amount'];
                    $this->previousCogs += $previousAmount;
                } elseif (str_starts_with((string) $row['code'], '52')) {
                    $this->opexDetails[] = $item;
                    $this->totalOpex += $row['amount'];
                    $this->previousOpex += $previousAmount;
                } elseif (str_starts_with((string) $row['code'], '53')) {
                    $this->financialDetails[] = $item;
                    $this->totalFinancial += $row['amount'];
                    $this->previousFinancial += $previousAmount;
                } else {
                    $this->otherExpenseDetails[] = $item;
                    $this->totalOtherExpenses += $row['amount'];
                    $this->previousOtherExpenses += $previousAmount;
                }
            }

            if ($row['amount'] < -0.005) {
                $this->reverseNatureCount++;
            }
        }

        // الحسابات التي ظهرت فقط في فترة المقارنة، نحتاجها لإجماليات المقارنة.
        $currentIds = collect($current)->pluck('id')->all();

        foreach ($previous as $row) {
            if (in_array($row['id'], $currentIds, true)) {
                continue;
            }

            if ($row['type'] === 'revenue') {
                if (str_starts_with((string) $row['code'], '41')) {
                    $this->previousSalesRevenue += $row['amount'];
                } else {
                    $this->previousOtherRevenue += $row['amount'];
                }
            } elseif ($row['type'] === 'expense') {
                if (str_starts_with((string) $row['code'], '51')) {
                    $this->previousCogs += $row['amount'];
                } elseif (str_starts_with((string) $row['code'], '52')) {
                    $this->previousOpex += $row['amount'];
                } elseif (str_starts_with((string) $row['code'], '53')) {
                    $this->previousFinancial += $row['amount'];
                } else {
                    $this->previousOtherExpenses += $row['amount'];
                }
            }
        }

        $this->totalRevenue = $this->salesRevenue + $this->otherRevenue;

        // مجمل الربح يعتمد على المبيعات الأساسية فقط، ولا يخلط الإيرادات الأخرى داخله.
        $this->grossProfit = $this->salesRevenue - $this->totalCogs;

        // الربح التشغيلي قبل الإيرادات الأخرى والمصروفات التمويلية.
        $this->operatingProfit = $this->grossProfit - $this->totalOpex;

        $this->profitBeforeTax =
            $this->operatingProfit
            + $this->otherRevenue
            - $this->totalFinancial
            - $this->totalOtherExpenses;

        // لا يوجد فصل ضريبي مستقل في الهيكل المرسل، لذلك صافي الربح = الربح قبل الضريبة.
        $this->netProfit = $this->profitBeforeTax;

        $denominator = abs($this->salesRevenue) >= 0.01
            ? abs($this->salesRevenue)
            : abs($this->totalRevenue);

        $this->grossProfitMargin = $denominator >= 0.01
            ? round(($this->grossProfit / $denominator) * 100, 1)
            : 0;

        $this->operatingProfitMargin = $denominator >= 0.01
            ? round(($this->operatingProfit / $denominator) * 100, 1)
            : 0;

        $this->netProfitMargin = $denominator >= 0.01
            ? round(($this->netProfit / $denominator) * 100, 1)
            : 0;

        $this->previousTotalRevenue = $this->previousSalesRevenue + $this->previousOtherRevenue;
        $this->previousGrossProfit = $this->previousSalesRevenue - $this->previousCogs;
        $this->previousOperatingProfit = $this->previousGrossProfit - $this->previousOpex;
        $this->previousNetProfit =
            $this->previousOperatingProfit
            + $this->previousOtherRevenue
            - $this->previousFinancial
            - $this->previousOtherExpenses;

        $this->loaded = true;

        if ($this->selectedAccountId) {
            $this->openAccount($this->selectedAccountId);
        }
    }

    private function loadPeriodAmounts(string $from, string $to): array
    {
        return DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->whereNull('je.deleted_at')
            ->where('je.status', 'posted')
            ->whereBetween('je.entry_date', [$from, $to])
            ->where('a.is_active', true)
            ->where('a.level', 'detail')
            ->whereIn('a.type', ['revenue', 'expense'])
            ->groupBy('a.id', 'a.code', 'a.name', 'a.type')
            ->selectRaw(
                "a.id, a.code, a.name, a.type,
                 SUM(jel.debit) as debit,
                 SUM(jel.credit) as credit"
            )
            ->orderBy('a.code')
            ->get()
            ->map(function ($row) {
                $debit = (float) $row->debit;
                $credit = (float) $row->credit;

                $amount = $row->type === 'revenue'
                    ? $credit - $debit
                    : $debit - $credit;

                return [
                    'id' => (int) $row->id,
                    'code' => (string) $row->code,
                    'name' => (string) $row->name,
                    'type' => (string) $row->type,
                    'debit' => $debit,
                    'credit' => $credit,
                    'amount' => $amount,
                ];
            })
            ->filter(fn ($row) => abs($row['amount']) >= 0.005)
            ->values()
            ->all();
    }

    private function makeItem(array $row, float $previous): array
    {
        $variance = $row['amount'] - $previous;
        $variancePct = abs($previous) >= 0.01
            ? ($variance / abs($previous)) * 100
            : null;

        return [
            'id' => $row['id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'amount' => $row['amount'],
            'comparison_amount' => $previous,
            'variance' => $variance,
            'variance_pct' => $variancePct,
            'reverse_nature' => $row['amount'] < -0.005,
        ];
    }

    private function syncComparisonDates(): void
    {
        $this->comparisonFrom = Carbon::parse($this->dateFrom)->subYear()->format('Y-m-d');
        $this->comparisonTo = Carbon::parse($this->dateTo)->subYear()->format('Y-m-d');
    }

    private function resetReport(): void
    {
        $this->salesRevenueDetails = [];
        $this->otherRevenueDetails = [];
        $this->cogsDetails = [];
        $this->opexDetails = [];
        $this->financialDetails = [];
        $this->otherExpenseDetails = [];

        $this->salesRevenue = 0;
        $this->otherRevenue = 0;
        $this->totalRevenue = 0;
        $this->totalCogs = 0;
        $this->totalOpex = 0;
        $this->totalFinancial = 0;
        $this->totalOtherExpenses = 0;
        $this->grossProfit = 0;
        $this->operatingProfit = 0;
        $this->profitBeforeTax = 0;
        $this->netProfit = 0;
        $this->grossProfitMargin = 0;
        $this->operatingProfitMargin = 0;
        $this->netProfitMargin = 0;

        $this->previousSalesRevenue = 0;
        $this->previousOtherRevenue = 0;
        $this->previousTotalRevenue = 0;
        $this->previousCogs = 0;
        $this->previousOpex = 0;
        $this->previousFinancial = 0;
        $this->previousOtherExpenses = 0;
        $this->previousGrossProfit = 0;
        $this->previousOperatingProfit = 0;
        $this->previousNetProfit = 0;

        $this->reverseNatureCount = 0;
    }

    public function openAccount(int $accountId): void
    {
        $account = Account::find($accountId);

        if (!$account || !in_array($account->type, ['revenue', 'expense'], true)) {
            return;
        }

        $this->selectedAccountId = $account->id;
        $this->selectedAccountCode = (string) $account->code;
        $this->selectedAccountName = (string) $account->name;
        $this->selectedAccountType = (string) $account->type;

        $running = 0.0;
        $debitTotal = 0.0;
        $creditTotal = 0.0;

        $lines = JournalEntryLine::query()
            ->with('journalEntry')
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$this->dateFrom, $this->dateTo]);
            })
            ->get()
            ->sortBy(fn ($line) =>
                ($line->journalEntry?->entry_date?->format('Y-m-d') ?? '0000-00-00')
                . '-'
                . str_pad((string) $line->id, 12, '0', STR_PAD_LEFT)
            );

        $ledger = [];

        foreach ($lines as $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;

            $debitTotal += $debit;
            $creditTotal += $credit;

            $effect = $account->type === 'revenue'
                ? $credit - $debit
                : $debit - $credit;

            $running += $effect;

            $ledger[] = [
                'journal_entry_id' => $line->journalEntry?->id,
                'entry_number' => $line->journalEntry?->entry_number,
                'date' => $line->journalEntry?->entry_date?->format('d/m/Y'),
                'reference' => $line->journalEntry?->reference,
                'description' => $line->description ?: $line->journalEntry?->description,
                'debit' => $debit,
                'credit' => $credit,
                'effect' => $effect,
                'running' => $running,
            ];
        }

        $this->selectedLedger = array_slice($ledger, -50);

        $this->selectedSummary = [
            'debit' => $debitTotal,
            'credit' => $creditTotal,
            'net' => $running,
            'count' => count($ledger),
        ];
    }

    public function closeAccount(): void
    {
        $this->selectedAccountId = null;
        $this->selectedAccountCode = '';
        $this->selectedAccountName = '';
        $this->selectedAccountType = '';
        $this->selectedLedger = [];
        $this->selectedSummary = [];
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'directionality' => 'rtl',
            'default_font' => 'dejavusans',
        ]);

        $html = view('pdf.profit-loss', get_object_vars($this))->render();
        $mpdf->WriteHTML($html);

        return response()->streamDownload(
            fn () => print($mpdf->Output('', 'S')),
            'profit-loss-' . $this->dateFrom . '-to-' . $this->dateTo . '.pdf'
        );
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fileName = 'profit-loss-' . $this->dateFrom . '-to-' . $this->dateTo . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'القسم', 'الكود', 'الحساب',
                'الفترة الحالية',
                'الفترة المقارنة',
                'التغير',
                'التغير %',
            ]);

            $writeRows = function (string $section, array $rows) use ($handle): void {
                foreach ($rows as $item) {
                    fputcsv($handle, [
                        $section,
                        $item['code'],
                        $item['name'],
                        $item['amount'],
                        $item['comparison_amount'],
                        $item['variance'],
                        $item['variance_pct'],
                    ]);
                }
            };

            $writeRows('إيرادات المبيعات', $this->salesRevenueDetails);
            $writeRows('إيرادات أخرى', $this->otherRevenueDetails);
            $writeRows('تكلفة المبيعات', $this->cogsDetails);
            $writeRows('مصروفات تشغيلية', $this->opexDetails);
            $writeRows('مصروفات تمويلية', $this->financialDetails);
            $writeRows('مصروفات أخرى', $this->otherExpenseDetails);

            fputcsv($handle, []);
            fputcsv($handle, ['الإجمالي', '', 'إجمالي الإيرادات', $this->totalRevenue, $this->previousTotalRevenue]);
            fputcsv($handle, ['الإجمالي', '', 'مجمل الربح', $this->grossProfit, $this->previousGrossProfit]);
            fputcsv($handle, ['الإجمالي', '', 'الربح التشغيلي', $this->operatingProfit, $this->previousOperatingProfit]);
            fputcsv($handle, ['الإجمالي', '', 'صافي الربح', $this->netProfit, $this->previousNetProfit]);

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}