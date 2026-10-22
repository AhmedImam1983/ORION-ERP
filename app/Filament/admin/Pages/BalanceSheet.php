<?php

namespace App\Filament\Admin\Pages;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BalanceSheet extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'accountant']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'الميزانية العمومية';
    protected static ?string $title = 'الميزانية العمومية · Balance Sheet';
    protected static ?int $navigationSort = 6;
    protected string $view = 'filament.admin.pages.balance-sheet';

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public string $asOfDate = '';
    public string $comparisonDate = '';
    public string $viewMode = 'detailed';
    public bool $showComparison = true;
    public bool $loaded = false;

    public array $fixedAssets = [];
    public array $fixedAssetContra = [];
    public float $totalFixedAssetGross = 0;
    public float $totalFixedAssetContra = 0;
    public float $totalFixedAssets = 0;

    public array $currentAssets = [];
    public float $totalCurrentAssets = 0;
    public float $totalAssets = 0;

    public array $currentLiabilities = [];
    public float $totalCurrentLiabilities = 0;
    public array $longTermLiabilities = [];
    public float $totalLongTermLiabilities = 0;
    public float $totalLiabilities = 0;

    public array $equityAccounts = [];
    public float $totalEquityAccounts = 0;
    public float $retainedEarnings = 0;
    public float $totalEquity = 0;

    public float $totalLiabilitiesAndEquity = 0;
    public float $balanceDifference = 0;
    public bool $isBalanced = false;

    public float $workingCapital = 0;
    public ?float $currentRatio = null;
    public int $reverseNatureCount = 0;

    public float $previousTotalAssets = 0;
    public float $previousTotalLiabilities = 0;
    public float $previousTotalEquity = 0;
    public float $previousTotalLiabilitiesAndEquity = 0;
    public float $previousRetainedEarnings = 0;

    public ?int $selectedAccountId = null;
    public string $selectedAccountCode = '';
    public string $selectedAccountName = '';
    public array $selectedLedger = [];
    public array $selectedLedgerSummary = [];

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
        $this->comparisonDate = now()->subYear()->format('Y-m-d');
        $this->loadReport();
    }

    public function setPreset(string $preset): void
    {
        $date = match ($preset) {
            'previous_month' => now()->subMonthNoOverflow()->endOfMonth(),
            'previous_year' => now()->subYear()->endOfYear(),
            default => now(),
        };

        $this->asOfDate = $date->format('Y-m-d');
        $this->comparisonDate = $date->copy()->subYear()->format('Y-m-d');
        $this->loadReport();
    }

    public function loadReport(): void
    {
        if (!$this->asOfDate) {
            Notification::make()->title('اختر تاريخ الميزانية')->warning()->send();
            return;
        }

        if ($this->showComparison && (!$this->comparisonDate || $this->comparisonDate >= $this->asOfDate)) {
            Notification::make()
                ->title('راجع تاريخ المقارنة')
                ->body('تاريخ المقارنة يجب أن يكون أقدم من تاريخ الميزانية.')
                ->warning()
                ->send();
            return;
        }

        $accounts = Account::query()
            ->with('parent')
            ->where('is_active', true)
            ->where('level', 'detail')
            ->orderBy('code')
            ->get();

        $currentSigned = $this->signedBalancesAsOf($accounts, $this->asOfDate);
        $previousSigned = $this->showComparison
            ? $this->signedBalancesAsOf($accounts, $this->comparisonDate)
            : [];

        $this->resetReportState();

        foreach ($accounts as $account) {
            $currentDisplay = $this->displayBalanceForType(
                $account->type,
                (float) ($currentSigned[$account->id] ?? 0)
            );

            $previousDisplay = $this->showComparison
                ? $this->displayBalanceForType(
                    $account->type,
                    (float) ($previousSigned[$account->id] ?? 0)
                )
                : 0;

            if (abs($currentDisplay) < 0.01 && abs($previousDisplay) < 0.01) {
                continue;
            }

            $item = $this->makeItem($account, $currentDisplay, $previousDisplay);

            if ($account->type === 'asset') {
                if (str_starts_with((string) $account->code, '15')) {
                    if ($currentDisplay < -0.005 || ($currentDisplay == 0.0 && $previousDisplay < -0.005)) {
                        $this->fixedAssetContra[] = $item;
                        $this->totalFixedAssetContra += abs($currentDisplay);
                    } else {
                        $this->fixedAssets[] = $item;
                        $this->totalFixedAssetGross += $currentDisplay;
                    }
                } else {
                    $this->currentAssets[] = $item;
                    $this->totalCurrentAssets += $currentDisplay;

                    if ($currentDisplay < -0.005) {
                        $this->reverseNatureCount++;
                    }
                }
            } elseif ($account->type === 'liability') {
                if (str_starts_with((string) $account->code, '22')) {
                    $this->longTermLiabilities[] = $item;
                    $this->totalLongTermLiabilities += $currentDisplay;
                } else {
                    $this->currentLiabilities[] = $item;
                    $this->totalCurrentLiabilities += $currentDisplay;
                }

                if ($currentDisplay < -0.005) {
                    $this->reverseNatureCount++;
                }
            } elseif ($account->type === 'equity') {
                $this->equityAccounts[] = $item;
                $this->totalEquityAccounts += $currentDisplay;

                if ($currentDisplay < -0.005) {
                    $this->reverseNatureCount++;
                }
            }
        }

        $this->totalFixedAssets = $this->totalFixedAssetGross - $this->totalFixedAssetContra;

        $this->retainedEarnings = $this->calculateRetainedEarnings($this->asOfDate);
        $this->previousRetainedEarnings = $this->showComparison
            ? $this->calculateRetainedEarnings($this->comparisonDate)
            : 0;

        $this->totalAssets = $this->totalCurrentAssets + $this->totalFixedAssets;
        $this->totalLiabilities = $this->totalCurrentLiabilities + $this->totalLongTermLiabilities;
        $this->totalEquity = $this->totalEquityAccounts + $this->retainedEarnings;
        $this->totalLiabilitiesAndEquity = $this->totalLiabilities + $this->totalEquity;

        if ($this->showComparison) {
            $previousTotals = $this->calculatePreviousTotals($accounts, $previousSigned);
            $this->previousTotalAssets = $previousTotals['assets'];
            $this->previousTotalLiabilities = $previousTotals['liabilities'];
            $this->previousTotalEquity = $previousTotals['equity_accounts'] + $this->previousRetainedEarnings;
            $this->previousTotalLiabilitiesAndEquity = $this->previousTotalLiabilities + $this->previousTotalEquity;
        }

        $this->balanceDifference = round(
            $this->totalAssets - $this->totalLiabilitiesAndEquity,
            2
        );

        $this->isBalanced = abs($this->balanceDifference) < 0.01;
        $this->workingCapital = $this->totalCurrentAssets - $this->totalCurrentLiabilities;
        $this->currentRatio = abs($this->totalCurrentLiabilities) >= 0.01
            ? $this->totalCurrentAssets / $this->totalCurrentLiabilities
            : null;

        $this->loaded = true;

        if ($this->selectedAccountId) {
            $this->openLedger($this->selectedAccountId);
        }
    }

    private function resetReportState(): void
    {
        $this->fixedAssets = [];
        $this->fixedAssetContra = [];
        $this->currentAssets = [];
        $this->currentLiabilities = [];
        $this->longTermLiabilities = [];
        $this->equityAccounts = [];

        $this->totalFixedAssetGross = 0;
        $this->totalFixedAssetContra = 0;
        $this->totalFixedAssets = 0;
        $this->totalCurrentAssets = 0;
        $this->totalAssets = 0;
        $this->totalCurrentLiabilities = 0;
        $this->totalLongTermLiabilities = 0;
        $this->totalLiabilities = 0;
        $this->totalEquityAccounts = 0;
        $this->retainedEarnings = 0;
        $this->totalEquity = 0;
        $this->totalLiabilitiesAndEquity = 0;
        $this->balanceDifference = 0;
        $this->workingCapital = 0;
        $this->currentRatio = null;
        $this->reverseNatureCount = 0;

        $this->previousTotalAssets = 0;
        $this->previousTotalLiabilities = 0;
        $this->previousTotalEquity = 0;
        $this->previousTotalLiabilitiesAndEquity = 0;
    }

    private function signedBalancesAsOf(Collection $accounts, string $date): array
    {
        $ids = $accounts->pluck('id')->all();

        if (empty($ids)) {
            return [];
        }

        $movement = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->whereNull('je.deleted_at')
            ->where('je.status', 'posted')
            ->where('je.entry_date', '<=', $date)
            ->whereIn('jel.account_id', $ids)
            ->groupBy('jel.account_id')
            ->selectRaw('jel.account_id, SUM(jel.debit) as debit, SUM(jel.credit) as credit')
            ->get()
            ->keyBy('account_id');

        $balances = [];

        foreach ($accounts as $account) {
            $row = $movement->get($account->id);
            $debit = (float) ($row->debit ?? 0);
            $credit = (float) ($row->credit ?? 0);

            $openingSigned = $account->balance_type === 'credit'
                ? -1 * (float) $account->opening_balance
                : (float) $account->opening_balance;

            // Signed balance convention: debit positive, credit negative.
            $balances[$account->id] = $openingSigned + $debit - $credit;
        }

        return $balances;
    }

    private function displayBalanceForType(string $type, float $signed): float
    {
        return in_array($type, ['liability', 'equity', 'revenue'], true)
            ? -$signed
            : $signed;
    }

    private function makeItem(Account $account, float $current, float $previous): array
    {
        $variance = $current - $previous;
        $variancePct = abs($previous) >= 0.01
            ? ($variance / abs($previous)) * 100
            : null;

        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'balance' => $current,
            'comparison_balance' => $previous,
            'variance' => $variance,
            'variance_pct' => $variancePct,
            'reverse_nature' => $current < -0.005,
        ];
    }

    private function calculatePreviousTotals(Collection $accounts, array $signedBalances): array
    {
        $assets = 0;
        $liabilities = 0;
        $equity = 0;

        foreach ($accounts as $account) {
            $display = $this->displayBalanceForType(
                $account->type,
                (float) ($signedBalances[$account->id] ?? 0)
            );

            if ($account->type === 'asset') {
                $assets += $display;
            } elseif ($account->type === 'liability') {
                $liabilities += $display;
            } elseif ($account->type === 'equity') {
                $equity += $display;
            }
        }

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity_accounts' => $equity,
        ];
    }

    private function calculateRetainedEarnings(string $date): float
    {
        $rows = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->whereNull('je.deleted_at')
            ->where('je.status', 'posted')
            ->where('je.entry_date', '<=', $date)
            ->whereIn('a.type', ['revenue', 'expense'])
            ->groupBy('a.type')
            ->selectRaw('a.type, SUM(jel.debit) as debit, SUM(jel.credit) as credit')
            ->get()
            ->keyBy('type');

        $revenueDebit = (float) ($rows->get('revenue')->debit ?? 0);
        $revenueCredit = (float) ($rows->get('revenue')->credit ?? 0);
        $expenseDebit = (float) ($rows->get('expense')->debit ?? 0);
        $expenseCredit = (float) ($rows->get('expense')->credit ?? 0);

        $netRevenue = $revenueCredit - $revenueDebit;
        $netExpenses = $expenseDebit - $expenseCredit;

        return $netRevenue - $netExpenses;
    }

    public function openLedger(int $accountId): void
    {
        $account = Account::find($accountId);

        if (!$account) {
            return;
        }

        $this->selectedAccountId = $account->id;
        $this->selectedAccountCode = (string) $account->code;
        $this->selectedAccountName = (string) $account->name;

        $yearStart = Carbon::parse($this->asOfDate)->startOfYear()->format('Y-m-d');

        $baseOpeningSigned = $account->balance_type === 'credit'
            ? -1 * (float) $account->opening_balance
            : (float) $account->opening_balance;

        $before = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->whereNull('je.deleted_at')
            ->where('je.status', 'posted')
            ->where('jel.account_id', $account->id)
            ->where('je.entry_date', '<', $yearStart)
            ->selectRaw('COALESCE(SUM(jel.debit),0) as debit, COALESCE(SUM(jel.credit),0) as credit')
            ->first();

        $runningSigned = $baseOpeningSigned + (float) $before->debit - (float) $before->credit;
        $openingSigned = $runningSigned;

        $lines = JournalEntryLine::query()
            ->with('journalEntry')
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($yearStart) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$yearStart, $this->asOfDate]);
            })
            ->get()
            ->sortBy(fn ($line) =>
                ($line->journalEntry?->entry_date?->format('Y-m-d') ?? '0000-00-00')
                . '-'
                . str_pad((string) $line->id, 12, '0', STR_PAD_LEFT)
            );

        $ledger = [];
        $periodDebit = 0;
        $periodCredit = 0;

        foreach ($lines as $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;

            $periodDebit += $debit;
            $periodCredit += $credit;
            $runningSigned += $debit - $credit;

            $display = $this->displayBalanceForType($account->type, $runningSigned);

            $ledger[] = [
                'journal_entry_id' => $line->journalEntry?->id,
                'date' => $line->journalEntry?->entry_date?->format('d/m/Y'),
                'entry_number' => $line->journalEntry?->entry_number,
                'reference' => $line->journalEntry?->reference,
                'description' => $line->description ?: $line->journalEntry?->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $display,
            ];
        }

        $this->selectedLedger = array_slice($ledger, -50);

        $this->selectedLedgerSummary = [
            'from' => $yearStart,
            'opening' => $this->displayBalanceForType($account->type, $openingSigned),
            'period_debit' => $periodDebit,
            'period_credit' => $periodCredit,
            'closing' => $this->displayBalanceForType($account->type, $runningSigned),
            'count' => count($ledger),
        ];
    }

    public function closeLedger(): void
    {
        $this->selectedAccountId = null;
        $this->selectedAccountCode = '';
        $this->selectedAccountName = '';
        $this->selectedLedger = [];
        $this->selectedLedgerSummary = [];
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'directionality' => 'rtl',
            'default_font' => 'dejavusans',
        ]);

        $html = view('pdf.balance-sheet', get_object_vars($this))->render();
        $mpdf->WriteHTML($html);

        return response()->streamDownload(
            fn () => print($mpdf->Output('', 'S')),
            'balance-sheet-' . $this->asOfDate . '.pdf'
        );
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fileName = 'balance-sheet-' . $this->asOfDate . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'القسم', 'الكود', 'الحساب',
                'الرصيد الحالي',
                'رصيد المقارنة',
                'التغير',
                'التغير %',
            ]);

            $writeRows = function (string $section, array $rows) use ($handle): void {
                foreach ($rows as $item) {
                    fputcsv($handle, [
                        $section,
                        $item['code'],
                        $item['name'],
                        $item['balance'],
                        $item['comparison_balance'],
                        $item['variance'],
                        $item['variance_pct'],
                    ]);
                }
            };

            $writeRows('الأصول الثابتة', $this->fixedAssets);
            $writeRows('حسابات مقابلة للأصول الثابتة', $this->fixedAssetContra);
            $writeRows('الأصول المتداولة', $this->currentAssets);
            $writeRows('الخصوم المتداولة', $this->currentLiabilities);
            $writeRows('الخصوم طويلة الأجل', $this->longTermLiabilities);
            $writeRows('حقوق الملكية', $this->equityAccounts);

            fputcsv($handle, [
                'حقوق الملكية', '', 'الأرباح المحتجزة / نتيجة الأعمال',
                $this->retainedEarnings,
                $this->previousRetainedEarnings,
                $this->retainedEarnings - $this->previousRetainedEarnings,
                null,
            ]);

            fputcsv($handle, []);
            fputcsv($handle, ['الإجمالي', '', 'إجمالي الأصول', $this->totalAssets]);
            fputcsv($handle, ['الإجمالي', '', 'إجمالي الخصوم', $this->totalLiabilities]);
            fputcsv($handle, ['الإجمالي', '', 'إجمالي حقوق الملكية', $this->totalEquity]);
            fputcsv($handle, ['Control', '', 'فرق المعادلة', $this->balanceDifference]);

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}