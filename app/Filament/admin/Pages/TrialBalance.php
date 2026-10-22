<?php

namespace App\Filament\Admin\Pages;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class TrialBalance extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'accountant']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'ميزان المراجعة';
    protected static ?string $title = 'ميزان المراجعة · Trial Balance';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.admin.pages.trial-balance';

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public string $dateFrom = '';
    public string $dateTo = '';
    public string $accountType = 'all';
    public string $levelFilter = 'detail';
    public bool $onlyWithBalance = true;
    public string $quickPeriod = 'year';

    public array $accounts = [];
    public float $totalDebit = 0;
    public float $totalCredit = 0;
    public float $periodDebit = 0;
    public float $periodCredit = 0;
    public int $accountsCount = 0;
    public int $reverseNatureCount = 0;
    public bool $loaded = false;

    public ?int $selectedAccountId = null;
    public string $selectedAccountCode = '';
    public string $selectedAccountName = '';
    public array $selectedLedger = [];
    public array $selectedSummary = [];

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->loadTrialBalance();
    }

    public function setPeriod(string $period): void
    {
        $this->quickPeriod = $period;

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

        $this->loadTrialBalance();
    }

    public function loadTrialBalance(): void
    {
        if (!$this->dateFrom || !$this->dateTo || $this->dateFrom > $this->dateTo) {
            Notification::make()
                ->title('راجع نطاق التاريخ')
                ->body('تاريخ البداية يجب أن يكون قبل أو مساوياً لتاريخ النهاية.')
                ->warning()
                ->send();
            return;
        }

        $query = Account::query()
            ->with('parent')
            ->where('is_active', true);

        if ($this->accountType !== 'all') {
            $query->where('type', $this->accountType);
        }

        if ($this->levelFilter !== 'all') {
            $query->where('level', $this->levelFilter);
        }

        $accounts = $query->orderBy('code')->get();

        $accountIds = $accounts->pluck('id');

        $before = $this->movementSummary($accountIds->all(), null, $this->dateFrom);
        $period = $this->movementSummary($accountIds->all(), $this->dateFrom, $this->dateTo, true);

        $this->accounts = [];
        $this->totalDebit = 0;
        $this->totalCredit = 0;
        $this->periodDebit = 0;
        $this->periodCredit = 0;
        $this->accountsCount = 0;
        $this->reverseNatureCount = 0;

        foreach ($accounts as $account) {
            $priorDebit = (float) ($before[$account->id]['debit'] ?? 0);
            $priorCredit = (float) ($before[$account->id]['credit'] ?? 0);

            $periodDebit = (float) ($period[$account->id]['debit'] ?? 0);
            $periodCredit = (float) ($period[$account->id]['credit'] ?? 0);
            $movementCount = (int) ($period[$account->id]['count'] ?? 0);

            $baseOpening = (float) $account->opening_balance;
            $baseOpeningSigned = $account->balance_type === 'credit' ? -$baseOpening : $baseOpening;

            // الرصيد الافتتاحي الحقيقي للفترة = الرصيد الافتتاحي الأساسي + كل القيود المرحلة قبل تاريخ البداية.
            $openingSigned = $baseOpeningSigned + $priorDebit - $priorCredit;
            $closingSigned = $openingSigned + $periodDebit - $periodCredit;

            $openingDebit = $openingSigned >= 0 ? $openingSigned : 0;
            $openingCredit = $openingSigned < 0 ? abs($openingSigned) : 0;
            $closingDebit = $closingSigned >= 0 ? $closingSigned : 0;
            $closingCredit = $closingSigned < 0 ? abs($closingSigned) : 0;

            $hasValue = abs($openingSigned) >= 0.005
                || abs($periodDebit) >= 0.005
                || abs($periodCredit) >= 0.005
                || abs($closingSigned) >= 0.005;

            if ($this->onlyWithBalance && !$hasValue) {
                continue;
            }

            $reverseNature = ($account->balance_type === 'debit' && $closingCredit > 0.005)
                || ($account->balance_type === 'credit' && $closingDebit > 0.005);

            $this->accounts[] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'level' => $account->level,
                'parent' => $account->parent?->name,
                'balance_type' => $account->balance_type,
                'opening_debit' => $openingDebit,
                'opening_credit' => $openingCredit,
                'period_debit' => $periodDebit,
                'period_credit' => $periodCredit,
                'total_debit' => $openingDebit + $periodDebit,
                'total_credit' => $openingCredit + $periodCredit,
                'balance_debit' => $closingDebit,
                'balance_credit' => $closingCredit,
                'movement_count' => $movementCount,
                'reverse_nature' => $reverseNature,
            ];

            $this->totalDebit += $closingDebit;
            $this->totalCredit += $closingCredit;
            $this->periodDebit += $periodDebit;
            $this->periodCredit += $periodCredit;
            $this->accountsCount++;

            if ($reverseNature) {
                $this->reverseNatureCount++;
            }
        }

        $this->loaded = true;

        if ($this->selectedAccountId) {
            $this->openLedger($this->selectedAccountId);
        }
    }

    private function movementSummary(
        array $accountIds,
        ?string $from = null,
        ?string $to = null,
        bool $inclusiveTo = false
    ): array {
        if (empty($accountIds)) {
            return [];
        }

        $query = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->whereNull('je.deleted_at')
            ->where('je.status', 'posted')
            ->whereIn('jel.account_id', $accountIds);

        if ($from !== null && $to !== null) {
            $query->whereBetween('je.entry_date', [$from, $to]);
        } elseif ($to !== null) {
            $query->where('je.entry_date', '<', $to);
        }

        return $query
            ->groupBy('jel.account_id')
            ->selectRaw('jel.account_id, SUM(jel.debit) as debit, SUM(jel.credit) as credit, COUNT(*) as movement_count')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (int) $row->account_id => [
                    'debit' => (float) $row->debit,
                    'credit' => (float) $row->credit,
                    'count' => (int) $row->movement_count,
                ],
            ])
            ->all();
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

        $baseOpening = (float) $account->opening_balance;
        $baseOpeningSigned = $account->balance_type === 'credit' ? -$baseOpening : $baseOpening;

        $prior = $this->movementSummary([$account->id], null, $this->dateFrom);
        $priorDebit = (float) ($prior[$account->id]['debit'] ?? 0);
        $priorCredit = (float) ($prior[$account->id]['credit'] ?? 0);

        $running = $baseOpeningSigned + $priorDebit - $priorCredit;
        $opening = $running;

        $lines = JournalEntryLine::query()
            ->with('journalEntry')
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$this->dateFrom, $this->dateTo]);
            })
            ->get()
            ->sortBy([
                fn ($a, $b) => strcmp(
                    (string) ($a->journalEntry?->entry_date?->format('Y-m-d') ?? ''),
                    (string) ($b->journalEntry?->entry_date?->format('Y-m-d') ?? '')
                ),
                fn ($a, $b) => $a->id <=> $b->id,
            ]);

        $ledger = [];
        $debit = 0;
        $credit = 0;

        foreach ($lines as $line) {
            $lineDebit = (float) $line->debit;
            $lineCredit = (float) $line->credit;

            $debit += $lineDebit;
            $credit += $lineCredit;
            $running += $lineDebit - $lineCredit;

            $ledger[] = [
                'journal_entry_id' => $line->journalEntry?->id,
                'entry_number' => $line->journalEntry?->entry_number,
                'date' => $line->journalEntry?->entry_date?->format('d/m/Y'),
                'reference' => $line->journalEntry?->reference,
                'description' => $line->description ?: $line->journalEntry?->description,
                'debit' => $lineDebit,
                'credit' => $lineCredit,
                'balance_debit' => $running >= 0 ? $running : 0,
                'balance_credit' => $running < 0 ? abs($running) : 0,
            ];
        }

        $this->selectedLedger = $ledger;
        $this->selectedSummary = [
            'opening_debit' => $opening >= 0 ? $opening : 0,
            'opening_credit' => $opening < 0 ? abs($opening) : 0,
            'period_debit' => $debit,
            'period_credit' => $credit,
            'closing_debit' => $running >= 0 ? $running : 0,
            'closing_credit' => $running < 0 ? abs($running) : 0,
            'count' => count($ledger),
        ];
    }

    public function closeLedger(): void
    {
        $this->selectedAccountId = null;
        $this->selectedAccountCode = '';
        $this->selectedAccountName = '';
        $this->selectedLedger = [];
        $this->selectedSummary = [];
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'directionality' => 'rtl',
            'default_font' => 'dejavusans',
        ]);

        $html = view('pdf.trial-balance', [
            'accounts' => $this->accounts,
            'totalDebit' => $this->totalDebit,
            'totalCredit' => $this->totalCredit,
            'periodDebit' => $this->periodDebit,
            'periodCredit' => $this->periodCredit,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'accountsCount' => $this->accountsCount,
        ])->render();

        $mpdf->WriteHTML($html);

        return response()->streamDownload(
            fn () => print($mpdf->Output('', 'S')),
            'trial-balance-' . $this->dateFrom . '-to-' . $this->dateTo . '.pdf'
        );
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fileName = 'trial-balance-' . $this->dateFrom . '-to-' . $this->dateTo . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM حتى يفتح العربي بصورة سليمة داخل Excel.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'الكود', 'اسم الحساب', 'النوع',
                'افتتاحي مدين', 'افتتاحي دائن',
                'حركة مدين', 'حركة دائن',
                'إجمالي مدين', 'إجمالي دائن',
                'ختامي مدين', 'ختامي دائن',
            ]);

            $typeMap = [
                'asset' => 'أصول',
                'liability' => 'خصوم',
                'equity' => 'حقوق الملكية',
                'revenue' => 'إيرادات',
                'expense' => 'مصروفات',
            ];

            foreach ($this->accounts as $account) {
                fputcsv($handle, [
                    $account['code'],
                    $account['name'],
                    $typeMap[$account['type']] ?? $account['type'],
                    $account['opening_debit'],
                    $account['opening_credit'],
                    $account['period_debit'],
                    $account['period_credit'],
                    $account['total_debit'],
                    $account['total_credit'],
                    $account['balance_debit'],
                    $account['balance_credit'],
                ]);
            }

            fputcsv($handle, [
                '', 'الإجمالي', '',
                '', '', $this->periodDebit, $this->periodCredit,
                '', '', $this->totalDebit, $this->totalCredit,
            ]);

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}