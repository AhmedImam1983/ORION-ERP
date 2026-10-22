<?php

namespace App\Filament\Admin\Pages;

use App\Services\CashFlowService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CashFlow extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'accountant']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-up-down';
    protected static ?string $navigationLabel = 'التدفقات النقدية';
    protected static ?string $title = 'قائمة التدفقات النقدية · Cash Flow';
    protected static ?int $navigationSort = 7;
    protected string $view = 'filament.admin.pages.cash-flow';

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public string $dateFrom = '';
    public string $dateTo = '';
    public string $comparisonFrom = '';
    public string $comparisonTo = '';
    public string $quickPeriod = 'month';
    public string $viewMode = 'detailed';
    public bool $showComparison = true;
    public bool $loaded = false;

    public float $operating = 0;
    public float $investing = 0;
    public float $financing = 0;
    public float $unclassified = 0;
    public float $netCashFlow = 0;

    public float $previousOperating = 0;
    public float $previousInvesting = 0;
    public float $previousFinancing = 0;
    public float $previousNetCashFlow = 0;

    public float $openingCash = 0;
    public float $closingCash = 0;
    public float $actualCashChange = 0;
    public float $reconciliationDifference = 0;
    public bool $isReconciled = false;

    public array $grouped = [];
    public array $previousGrouped = [];
    public array $movements = [];
    public array $cashPositions = [];
    public array $waterfall = [];
    public array $legacyWarning = [];

    public int $cashEntriesCount = 0;
    public int $internalTransferCount = 0;
    public float $internalTransferVolume = 0;
    public int $unclassifiedCount = 0;

    public ?string $selectedCategory = null;
    public string $selectedCategoryLabel = '';
    public array $selectedMovements = [];

    public function mount(): void
    {
        $this->setPeriod('month', false);
        $this->loadReport();
    }

    public function setPeriod(string $period, bool $reload = true): void
    {
        $today = now();
        $this->quickPeriod = $period;

        match ($period) {
            'today' => [$this->dateFrom, $this->dateTo] = [$today->format('Y-m-d'), $today->format('Y-m-d')],
            'quarter' => [$this->dateFrom, $this->dateTo] = [$today->copy()->startOfQuarter()->format('Y-m-d'), $today->format('Y-m-d')],
            'year' => [$this->dateFrom, $this->dateTo] = [$today->copy()->startOfYear()->format('Y-m-d'), $today->format('Y-m-d')],
            default => [$this->dateFrom, $this->dateTo] = [$today->copy()->startOfMonth()->format('Y-m-d'), $today->format('Y-m-d')],
        };

        $this->syncComparisonDates();

        if ($reload) {
            $this->loadReport();
        }
    }

    public function loadReport(): void
    {
        if (!$this->dateFrom || !$this->dateTo || Carbon::parse($this->dateFrom)->gt(Carbon::parse($this->dateTo))) {
            Notification::make()
                ->title('الفترة غير صحيحة')
                ->body('تأكد أن تاريخ البداية يسبق أو يساوي تاريخ النهاية.')
                ->warning()
                ->send();
            return;
        }

        $this->syncComparisonDates();

        $report = app(CashFlowService::class)->buildReport(
            $this->dateFrom,
            $this->dateTo,
            $this->showComparison ? $this->comparisonFrom : null,
            $this->showComparison ? $this->comparisonTo : null,
        );

        $current = $report['current'];
        $previous = $report['comparison'];

        $this->operating = (float) $current['totals']['operating'];
        $this->investing = (float) $current['totals']['investing'];
        $this->financing = (float) $current['totals']['financing'];
        $this->unclassified = (float) $current['totals']['unclassified'];
        $this->netCashFlow = (float) $report['net_classified_flow'];

        $this->previousOperating = (float) $previous['totals']['operating'];
        $this->previousInvesting = (float) $previous['totals']['investing'];
        $this->previousFinancing = (float) $previous['totals']['financing'];
        $this->previousNetCashFlow = $this->previousOperating + $this->previousInvesting + $this->previousFinancing;

        $this->openingCash = (float) $report['opening_cash'];
        $this->closingCash = (float) $report['closing_cash'];
        $this->actualCashChange = (float) $report['actual_cash_change'];
        $this->reconciliationDifference = (float) $report['reconciliation_difference'];
        $this->isReconciled = (bool) $report['is_reconciled'];

        $this->grouped = $current['grouped'];
        $this->previousGrouped = $previous['grouped'];
        $this->movements = $current['movements'];
        $this->cashPositions = $report['cash_positions'];
        $this->waterfall = $report['waterfall'];
        $this->legacyWarning = $report['legacy_warning'];

        $this->cashEntriesCount = (int) $current['cash_entries_count'];
        $this->internalTransferCount = (int) $current['internal_transfer_count'];
        $this->internalTransferVolume = (float) $current['internal_transfer_volume'];
        $this->unclassifiedCount = (int) $current['unclassified_count'];

        $this->loaded = true;
        $this->closeCategory();
    }

    public function openCategory(string $category): void
    {
        $labels = [
            'operating' => 'التدفقات التشغيلية',
            'investing' => 'التدفقات الاستثمارية',
            'financing' => 'التدفقات التمويلية',
            'unclassified' => 'حركات غير مصنفة',
        ];

        if (!array_key_exists($category, $labels)) {
            return;
        }

        $this->selectedCategory = $category;
        $this->selectedCategoryLabel = $labels[$category];
        $this->selectedMovements = array_values(array_filter(
            $this->movements,
            fn (array $row): bool => $row['category'] === $category,
        ));
    }

    public function closeCategory(): void
    {
        $this->selectedCategory = null;
        $this->selectedCategoryLabel = '';
        $this->selectedMovements = [];
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'directionality' => 'rtl',
            'default_font' => 'dejavusans',
        ]);

        $html = view('pdf.cash-flow', get_object_vars($this))->render();
        $mpdf->WriteHTML($html);

        return response()->streamDownload(
            fn () => print($mpdf->Output('', 'S')),
            'cash-flow-' . $this->dateFrom . '-to-' . $this->dateTo . '.pdf'
        );
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fileName = 'cash-flow-' . $this->dateFrom . '-to-' . $this->dateTo . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['قائمة التدفقات النقدية']);
            fputcsv($handle, ['من', $this->dateFrom, 'إلى', $this->dateTo]);
            fputcsv($handle, []);
            fputcsv($handle, ['البند', 'الفترة الحالية', 'فترة المقارنة']);
            fputcsv($handle, ['التدفق التشغيلي', $this->operating, $this->previousOperating]);
            fputcsv($handle, ['التدفق الاستثماري', $this->investing, $this->previousInvesting]);
            fputcsv($handle, ['التدفق التمويلي', $this->financing, $this->previousFinancing]);
            fputcsv($handle, ['صافي التدفق النقدي', $this->netCashFlow, $this->previousNetCashFlow]);
            fputcsv($handle, ['رصيد أول الفترة', $this->openingCash, '']);
            fputcsv($handle, ['رصيد آخر الفترة', $this->closingCash, '']);
            fputcsv($handle, ['فرق التسوية', $this->reconciliationDifference, '']);
            fputcsv($handle, []);

            fputcsv($handle, ['التصنيف', 'التاريخ', 'رقم القيد', 'المرجع', 'كود الحساب', 'الحساب المقابل', 'البيان', 'التدفق']);
            $labels = [
                'operating' => 'تشغيلي',
                'investing' => 'استثماري',
                'financing' => 'تمويلي',
                'unclassified' => 'غير مصنف',
            ];

            foreach (array_reverse($this->movements) as $row) {
                fputcsv($handle, [
                    $labels[$row['category']] ?? $row['category'],
                    $row['date_iso'],
                    $row['entry_number'],
                    $row['reference'],
                    $row['account_code'],
                    $row['account_name'],
                    $row['description'],
                    $row['amount'],
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function syncComparisonDates(): void
    {
        if (!$this->dateFrom || !$this->dateTo) {
            return;
        }

        $this->comparisonFrom = Carbon::parse($this->dateFrom)->subYear()->format('Y-m-d');
        $this->comparisonTo = Carbon::parse($this->dateTo)->subYear()->format('Y-m-d');
    }
}
