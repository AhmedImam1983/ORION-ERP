<?php

namespace App\Filament\Admin\Pages;

use App\Models\Salary;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SalaryReport extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'تقرير الرواتب';
    protected static ?string $title = 'تقرير الرواتب الشهري';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.admin.pages.salary-report';

    public static function getNavigationGroup(): ?string
    {
        return 'الموارد البشرية · HR';
    }

    public int $month = 0;
    public int $year = 0;
    public bool $loaded = false;

    public string $search = '';
    public string $statusFilter = '';
    public string $departmentFilter = '';

    public array $salaries = [];
    public float $totalBasic = 0;
    public float $totalAllowances = 0;
    public float $totalOvertime = 0;
    public float $totalBonus = 0;
    public float $totalDeductions = 0;
    public float $totalNet = 0;
    public int $totalEmployees = 0;
    public int $paidCount = 0;
    public int $approvedCount = 0;
    public int $draftCount = 0;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function updatedMonth(): void
    {
        $this->loaded = false;
    }

    public function updatedYear(): void
    {
        $this->loaded = false;
    }

    public function loadReport(): void
    {
        $salaries = Salary::with('employee')
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->orderBy('created_at')
            ->get();

        $this->salaries = [];
        $this->totalBasic = 0;
        $this->totalAllowances = 0;
        $this->totalOvertime = 0;
        $this->totalBonus = 0;
        $this->totalDeductions = 0;
        $this->totalNet = 0;
        $this->paidCount = 0;
        $this->approvedCount = 0;
        $this->draftCount = 0;
        $this->search = '';
        $this->statusFilter = '';
        $this->departmentFilter = '';

        foreach ($salaries as $salary) {
            $totalDed = (float) $salary->deductions
                + (float) $salary->absence_deduction
                + (float) $salary->late_deduction
                + (float) $salary->insurance_deduction
                + (float) $salary->tax_deduction;

            $this->salaries[] = [
                'id' => $salary->id,
                'code' => $salary->employee?->code ?? '-',
                'name' => $salary->employee?->name ?? '-',
                'department' => $salary->employee?->department ?? '-',
                'job_title' => $salary->employee?->job_title ?? '-',
                'basic_salary' => (float) $salary->basic_salary,
                'allowances' => (float) $salary->allowances,
                'overtime' => (float) $salary->overtime_amount,
                'bonus' => (float) $salary->bonus,
                'deductions' => $totalDed,
                'net_salary' => (float) $salary->net_salary,
                'status' => $salary->status,
                'payment_method' => $salary->payment_method,
            ];

            $this->totalBasic += (float) $salary->basic_salary;
            $this->totalAllowances += (float) $salary->allowances;
            $this->totalOvertime += (float) $salary->overtime_amount;
            $this->totalBonus += (float) $salary->bonus;
            $this->totalDeductions += $totalDed;
            $this->totalNet += (float) $salary->net_salary;

            match ($salary->status) {
                'paid' => $this->paidCount++,
                'approved' => $this->approvedCount++,
                default => $this->draftCount++,
            };
        }

        $this->totalEmployees = count($this->salaries);
        $this->loaded = true;

        if ($this->totalEmployees === 0) {
            Notification::make()
                ->title('لا توجد رواتب لهذا الشهر')
                ->warning()
                ->send();
        }
    }

    public function resetTableFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->departmentFilter = '';
    }

    public function filteredSalaries(): array
    {
        return collect($this->salaries)
            ->filter(function (array $salary): bool {
                if ($this->statusFilter !== '' && $salary['status'] !== $this->statusFilter) {
                    return false;
                }

                if ($this->departmentFilter !== '' && $salary['department'] !== $this->departmentFilter) {
                    return false;
                }

                if ($this->search !== '') {
                    $needle = mb_strtolower(trim($this->search));
                    $haystack = mb_strtolower(implode(' ', [
                        $salary['code'],
                        $salary['name'],
                        $salary['department'],
                        $salary['job_title'],
                    ]));

                    if (! str_contains($haystack, $needle)) {
                        return false;
                    }
                }

                return true;
            })
            ->values()
            ->all();
    }

    public function departments(): array
    {
        return collect($this->salaries)
            ->pluck('department')
            ->filter(fn ($department) => filled($department) && $department !== '-')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function getTotalAdditions(): float
    {
        return $this->totalAllowances + $this->totalOvertime + $this->totalBonus;
    }

    public function getAverageNet(): float
    {
        return $this->totalEmployees > 0 ? $this->totalNet / $this->totalEmployees : 0;
    }

    public function getPaidProgress(): float
    {
        return $this->totalEmployees > 0 ? ($this->paidCount / $this->totalEmployees) * 100 : 0;
    }

    public function approveAll(): void
    {
        Salary::where('month', $this->month)
            ->where('year', $this->year)
            ->where('status', 'draft')
            ->update(['status' => 'approved']);

        $this->loadReport();
        Notification::make()->title('تم اعتماد جميع الرواتب المسودة')->success()->send();
    }

    public function payAll(): void
    {
        Salary::where('month', $this->month)
            ->where('year', $this->year)
            ->whereIn('status', ['draft', 'approved'])
            ->update(['status' => 'paid', 'payment_date' => today()]);

        $this->loadReport();
        Notification::make()->title('تم صرف جميع الرواتب المستحقة')->success()->send();
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $months = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
            7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'directionality' => 'rtl',
            'default_font' => 'dejavusans',
        ]);

        $html = view('pdf.salary-report', [
            'salaries' => $this->salaries,
            'month' => $months[$this->month] ?? $this->month,
            'year' => $this->year,
            'totalBasic' => $this->totalBasic,
            'totalAllowances' => $this->totalAllowances,
            'totalOvertime' => $this->totalOvertime,
            'totalBonus' => $this->totalBonus,
            'totalDeductions' => $this->totalDeductions,
            'totalNet' => $this->totalNet,
            'totalEmployees' => $this->totalEmployees,
        ])->render();

        $mpdf->WriteHTML($html);

        return response()->streamDownload(
            fn () => print($mpdf->Output('', 'S')),
            'تقرير-رواتب-' . ($months[$this->month] ?? $this->month) . '-' . $this->year . '.pdf'
        );
    }

    public function getMonthName(): string
    {
        return [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
            7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'][$this->month] ?? '';
    }
}
