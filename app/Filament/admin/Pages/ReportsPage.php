<?php

namespace App\Filament\Admin\Pages;

use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsPage extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'accountant']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'التقارير';
    protected static ?string $title = 'مركز التقارير · Reports Center';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.admin.pages.reports-page';

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public string $dateFrom = '';
    public string $dateTo = '';
    public string $quickPeriod = 'month';
    public string $activeReport = '';
    public array $reportData = [];
    public string $reportTitle = '';

    public function mount(): void
    {
        $this->setPeriod('month', false);
    }

    public function setPeriod(string $period, bool $reload = true): void
    {
        $today = now();
        $this->quickPeriod = $period;

        match ($period) {
            'today' => [$this->dateFrom, $this->dateTo] = [
                $today->format('Y-m-d'),
                $today->format('Y-m-d'),
            ],
            'quarter' => [$this->dateFrom, $this->dateTo] = [
                $today->copy()->startOfQuarter()->format('Y-m-d'),
                $today->format('Y-m-d'),
            ],
            'year' => [$this->dateFrom, $this->dateTo] = [
                $today->copy()->startOfYear()->format('Y-m-d'),
                $today->format('Y-m-d'),
            ],
            default => [$this->dateFrom, $this->dateTo] = [
                $today->copy()->startOfMonth()->format('Y-m-d'),
                $today->format('Y-m-d'),
            ],
        };

        if ($reload && $this->activeReport !== '') {
            $this->refreshReport();
        }
    }

    public function refreshReport(): void
    {
        if (!$this->validatePeriod()) {
            return;
        }

        match ($this->activeReport) {
            'sales' => $this->loadSalesReport(),
            'profit' => $this->loadProfitReport(),
            'debts' => $this->loadDebtsReport(),
            'stock' => $this->loadStockReport(),
            'top_products' => $this->loadTopProductsReport(),
            'attendance' => $this->loadAttendanceReport(),
            default => null,
        };
    }

    public function clearReport(): void
    {
        $this->activeReport = '';
        $this->reportTitle = '';
        $this->reportData = [];
    }

    public function getOverviewStats(): array
    {
        $validStatuses = ['confirmed', 'paid', 'partial'];

        $sales = Invoice::query()
            ->where('type', 'sale')
            ->whereIn('status', $validStatuses)
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo]);

        $receivables = Invoice::query()
            ->where('type', 'sale')
            ->whereIn('status', $validStatuses)
            ->where('remaining_amount', '>', 0)
            ->sum('remaining_amount');

        return [
            'sales' => (float) (clone $sales)->sum('total'),
            'invoices' => (int) (clone $sales)->count(),
            'receivables' => (float) $receivables,
            'products' => (int) Product::where('is_active', true)->count(),
            'employees' => (int) Employee::where('status', 'active')->count(),
        ];
    }

    public function getCenterLinks(): array
    {
        return [
            'financial' => [
                $this->pageLink(TrialBalance::class, 'ميزان المراجعة', 'Trial Balance', 'scale'),
                $this->pageLink(ProfitLoss::class, 'الأرباح والخسائر', 'Profit & Loss', 'chart'),
                $this->pageLink(BalanceSheet::class, 'الميزانية العمومية', 'Balance Sheet', 'building'),
                $this->pageLink(CashFlow::class, 'التدفقات النقدية', 'Cash Flow', 'arrows'),
            ],
            'statements' => [
                $this->pageLink(CustomerStatement::class, 'كشف حساب العميل', 'Customer Statement', 'user'),
                $this->pageLink(SupplierStatement::class, 'كشف حساب المورد', 'Supplier Statement', 'truck'),
            ],
        ];
    }

    private function pageLink(string $class, string $label, string $subtitle, string $icon): array
    {
        $url = null;

        try {
            if (class_exists($class) && method_exists($class, 'getUrl')) {
                $url = $class::getUrl();
            }
        } catch (\Throwable) {
            $url = null;
        }

        return compact('label', 'subtitle', 'icon', 'url');
    }

    public function loadSalesReport(): void
    {
        if (!$this->validatePeriod()) {
            return;
        }

        $invoices = Invoice::with('customer')
            ->where('type', 'sale')
            ->whereIn('status', ['confirmed', 'paid', 'partial'])
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo])
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get();

        $this->reportData = [
            'invoices' => $invoices,
            'total_sales' => (float) $invoices->sum('total'),
            'total_paid' => (float) $invoices->sum('paid_amount'),
            'total_remaining' => (float) $invoices->sum('remaining_amount'),
            'count' => $invoices->count(),
        ];

        $this->reportTitle = 'تقرير المبيعات';
        $this->activeReport = 'sales';
    }

    /**
     * تقرير ربحيّة الأصناف وليس قائمة P&L المحاسبية.
     * قائمة الأرباح والخسائر الحقيقية موجودة كشاشة مستقلة ومبنية على الأستاذ العام.
     */
    public function loadProfitReport(): void
    {
        if (!$this->validatePeriod()) {
            return;
        }

        $sales = InvoiceItem::query()
            ->whereHas('invoice', function ($q): void {
                $q->where('type', 'sale')
                    ->whereIn('status', ['confirmed', 'paid', 'partial'])
                    ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo]);
            })
            ->get();

        $returns = InvoiceItem::query()
            ->whereHas('invoice', function ($q): void {
                $q->where('type', 'sale_return')
                    ->whereIn('status', ['confirmed', 'paid', 'partial'])
                    ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo]);
            })
            ->get();

        $rows = collect();

        foreach ($sales as $item) {
            $rows->push([
                'name' => (string) $item->product_name,
                'qty' => (float) $item->quantity,
                'revenue' => (float) $item->total,
                'cost' => (float) $item->quantity * (float) ($item->cost_price ?? 0),
            ]);
        }

        foreach ($returns as $item) {
            $rows->push([
                'name' => (string) $item->product_name,
                'qty' => -1 * (float) $item->quantity,
                'revenue' => -1 * (float) $item->total,
                'cost' => -1 * (float) $item->quantity * (float) ($item->cost_price ?? 0),
            ]);
        }

        $byProduct = $rows
            ->groupBy('name')
            ->map(function ($group, $name): array {
                $revenue = (float) $group->sum('revenue');
                $cost = (float) $group->sum('cost');
                $profit = $revenue - $cost;

                return [
                    'name' => (string) $name,
                    'qty' => (float) $group->sum('qty'),
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $profit,
                    'margin' => abs($revenue) > 0.01 ? round(($profit / abs($revenue)) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('profit')
            ->values();

        $totalRevenue = (float) $rows->sum('revenue');
        $totalCost = (float) $rows->sum('cost');
        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = abs($totalRevenue) > 0.01
            ? round(($grossProfit / abs($totalRevenue)) * 100, 1)
            : 0;

        $this->reportData = [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,
            'profit_margin' => $profitMargin,
            'by_product' => $byProduct,
        ];

        $this->reportTitle = 'ربحية الأصناف';
        $this->activeReport = 'profit';
    }

    public function loadDebtsReport(): void
    {
        if (!$this->validatePeriod()) {
            return;
        }

        $customers = Customer::withSum(['invoices as total_debt' => function ($q): void {
            $q->where('type', 'sale')
                ->whereIn('status', ['confirmed', 'paid', 'partial'])
                ->where('remaining_amount', '>', 0);
        }], 'remaining_amount')
            ->having('total_debt', '>', 0)
            ->orderByDesc('total_debt')
            ->get();

        $this->reportData = [
            'customers' => $customers,
            'total_debt' => (float) $customers->sum('total_debt'),
            'count' => $customers->count(),
        ];

        $this->reportTitle = 'مديونيات العملاء';
        $this->activeReport = 'debts';
    }

    public function loadStockReport(): void
    {
        $products = Product::with('category')
            ->where('item_type', 'stock')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $this->reportData = [
            'products' => $products,
            'count' => $products->count(),
            'low' => $products->filter(
                fn ($product): bool => (float) ($product->quantity ?? 0) <= (float) ($product->min_quantity ?? 0)
            )->count(),
        ];

        $this->reportTitle = 'حالة المخزون';
        $this->activeReport = 'stock';
    }

    public function loadTopProductsReport(): void
    {
        if (!$this->validatePeriod()) {
            return;
        }

        $sales = InvoiceItem::query()
            ->whereHas('invoice', function ($q): void {
                $q->where('type', 'sale')
                    ->whereIn('status', ['confirmed', 'paid', 'partial'])
                    ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo]);
            })
            ->get();

        $returns = InvoiceItem::query()
            ->whereHas('invoice', function ($q): void {
                $q->where('type', 'sale_return')
                    ->whereIn('status', ['confirmed', 'paid', 'partial'])
                    ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo]);
            })
            ->get();

        $rows = collect();

        foreach ($sales as $item) {
            $rows->push([
                'name' => (string) $item->product_name,
                'code' => (string) ($item->product_code ?? ''),
                'qty' => (float) $item->quantity,
                'total' => (float) $item->total,
            ]);
        }

        foreach ($returns as $item) {
            $rows->push([
                'name' => (string) $item->product_name,
                'code' => (string) ($item->product_code ?? ''),
                'qty' => -1 * (float) $item->quantity,
                'total' => -1 * (float) $item->total,
            ]);
        }

        $byProduct = $rows
            ->groupBy('name')
            ->map(function ($group, $name): array {
                return [
                    'name' => (string) $name,
                    'code' => (string) ($group->first()['code'] ?? ''),
                    'qty' => (float) $group->sum('qty'),
                    'total' => (float) $group->sum('total'),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->take(20);

        $this->reportData = [
            'products' => $byProduct,
            'total_revenue' => (float) $rows->sum('total'),
        ];

        $this->reportTitle = 'أكثر الأصناف مبيعاً';
        $this->activeReport = 'top_products';
    }

    public function loadAttendanceReport(): void
    {
        if (!$this->validatePeriod()) {
            return;
        }

        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $data = [];

        foreach ($employees as $employee) {
            $records = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$this->dateFrom, $this->dateTo])
                ->get();

            $data[] = [
                'name' => $employee->name,
                'department' => $employee->department,
                'present' => $records->where('status', 'present')->count(),
                'absent' => $records->where('status', 'absent')->count(),
                'late' => $records->where('status', 'late')->count(),
                'vacation' => $records->where('status', 'vacation')->count(),
                'total_hours' => (float) $records->sum('working_hours'),
                'late_minutes' => (int) $records->sum('late_minutes'),
            ];
        }

        $this->reportData = [
            'employees' => $data,
            'total' => count($data),
        ];

        $this->reportTitle = 'الحضور والانصراف';
        $this->activeReport = 'attendance';
    }

    public function exportExcel(): StreamedResponse
    {
        if ($this->activeReport === '') {
            Notification::make()->title('اختر تقريراً أولاً')->warning()->send();

            return response()->streamDownload(static fn (): null => null, 'report.csv');
        }

        $rows = $this->getExcelRows();
        $fileName = 'orion-report-' . $this->activeReport . '-' . $this->dateFrom . '-to-' . $this->dateTo . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function getExcelRows(): array
    {
        return match ($this->activeReport) {
            'sales' => $this->getSalesRows(),
            'profit' => $this->getProfitRows(),
            'debts' => $this->getDebtsRows(),
            'stock' => $this->getStockRows(),
            'top_products' => $this->getTopProductsRows(),
            'attendance' => $this->getAttendanceRows(),
            default => [],
        };
    }

    private function getSalesRows(): array
    {
        $rows = [['رقم الفاتورة', 'التاريخ', 'العميل', 'الإجمالي', 'المدفوع', 'المتبقي', 'الحالة']];

        foreach ($this->reportData['invoices'] as $invoice) {
            $rows[] = [
                $invoice->invoice_number,
                $invoice->invoice_date->format('d/m/Y'),
                $invoice->customer?->name ?? 'عميل نقدي',
                $invoice->total,
                $invoice->paid_amount,
                $invoice->remaining_amount,
                match ($invoice->status) {
                    'paid' => 'مدفوعة',
                    'partial' => 'جزئي',
                    'confirmed' => 'مؤكدة',
                    default => $invoice->status,
                },
            ];
        }

        $rows[] = ['', '', 'الإجمالي', $this->reportData['total_sales'], $this->reportData['total_paid'], $this->reportData['total_remaining'], ''];

        return $rows;
    }

    private function getProfitRows(): array
    {
        $rows = [['الصنف', 'صافي الكمية', 'الإيراد', 'التكلفة', 'الربح', 'هامش الربح %']];

        foreach ($this->reportData['by_product'] as $product) {
            $rows[] = [
                $product['name'],
                $product['qty'],
                $product['revenue'],
                $product['cost'],
                $product['profit'],
                $product['margin'] . '%',
            ];
        }

        $rows[] = ['الإجمالي', '', $this->reportData['total_revenue'], $this->reportData['total_cost'], $this->reportData['gross_profit'], $this->reportData['profit_margin'] . '%'];

        return $rows;
    }

    private function getDebtsRows(): array
    {
        $rows = [['العميل', 'التليفون', 'المديونية']];

        foreach ($this->reportData['customers'] as $customer) {
            $rows[] = [$customer->name, $customer->phone ?? '-', $customer->total_debt];
        }

        $rows[] = ['الإجمالي', '', $this->reportData['total_debt']];

        return $rows;
    }

    private function getStockRows(): array
    {
        $rows = [['الكود', 'الصنف', 'المجموعة', 'الكمية الحالية', 'الحد الأدنى', 'الحالة']];

        foreach ($this->reportData['products'] as $product) {
            $isLow = (float) ($product->quantity ?? 0) <= (float) ($product->min_quantity ?? 0);

            $rows[] = [
                $product->code,
                $product->name,
                $product->category?->name ?? '-',
                $product->quantity ?? 0,
                $product->min_quantity,
                $isLow ? 'ناقص' : 'كافي',
            ];
        }

        return $rows;
    }

    private function getTopProductsRows(): array
    {
        $rows = [['#', 'الكود', 'الصنف', 'صافي الكمية المباعة', 'صافي المبيعات']];

        foreach ($this->reportData['products'] as $index => $product) {
            $rows[] = [$index + 1, $product['code'], $product['name'], $product['qty'], $product['total']];
        }

        return $rows;
    }

    private function getAttendanceRows(): array
    {
        $rows = [['الموظف', 'القسم', 'حضور', 'غياب', 'تأخير', 'إجازة', 'ساعات العمل', 'دقائق التأخير']];

        foreach ($this->reportData['employees'] as $employee) {
            $rows[] = [
                $employee['name'],
                $employee['department'],
                $employee['present'],
                $employee['absent'],
                $employee['late'],
                $employee['vacation'],
                $employee['total_hours'],
                $employee['late_minutes'],
            ];
        }

        return $rows;
    }

    private function validatePeriod(): bool
    {
        if ($this->dateFrom === '' || $this->dateTo === '') {
            Notification::make()->title('حدد نطاق التاريخ')->warning()->send();
            return false;
        }

        try {
            $from = Carbon::parse($this->dateFrom)->startOfDay();
            $to = Carbon::parse($this->dateTo)->endOfDay();
        } catch (\Throwable) {
            Notification::make()->title('صيغة التاريخ غير صحيحة')->danger()->send();
            return false;
        }

        if ($from->greaterThan($to)) {
            Notification::make()
                ->title('نطاق التاريخ غير صحيح')
                ->body('تاريخ البداية يجب أن يسبق أو يساوي تاريخ النهاية.')
                ->warning()
                ->send();

            return false;
        }

        return true;
    }
}
