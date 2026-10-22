<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ExcelExportController extends Controller
{
    private Spreadsheet $spreadsheet;
    private $sheet;

    public function export(Request $request, string $type)
    {
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->get('to', now()->format('Y-m-d'));

        $this->spreadsheet = new Spreadsheet();
        $this->sheet       = $this->spreadsheet->getActiveSheet();

        // RTL
        $this->sheet->setRightToLeft(true);

        match($type) {
            'sales'        => $this->buildSalesSheet($from, $to),
            'profit'       => $this->buildProfitSheet($from, $to),
            'debts'        => $this->buildDebtsSheet(),
            'stock'        => $this->buildStockSheet(),
            'top_products' => $this->buildTopProductsSheet($from, $to),
            'attendance'   => $this->buildAttendanceSheet($from, $to),
            default        => null,
        };

        $titles = [
            'sales'        => 'تقرير المبيعات',
            'profit'       => 'تقرير الأرباح والخسائر',
            'debts'        => 'تقرير المديونيات',
            'stock'        => 'تقرير المخزون',
            'top_products' => 'أكثر الأصناف مبيعاً',
            'attendance'   => 'تقرير الحضور والانصراف',
        ];

        $filename = ($titles[$type] ?? 'تقرير') . '-' . now()->format('Y-m-d') . '.xlsx';

        $writer = new Xlsx($this->spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function addHeader(string $title, array $columns): void
    {
        $colCount = count($columns);
        $lastCol  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        // عنوان الشركة
        $this->sheet->mergeCells("A1:{$lastCol}1");
        $this->sheet->setCellValue('A1', config('app.company_name', 'Orion ERP'));
        $this->sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1a1a2e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDE9FE']],
        ]);
        $this->sheet->getRowDimension(1)->setRowHeight(30);

        // عنوان التقرير
        $this->sheet->mergeCells("A2:{$lastCol}2");
        $this->sheet->setCellValue('A2', $title);
        $this->sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
        ]);
        $this->sheet->getRowDimension(2)->setRowHeight(25);

        // تاريخ الطباعة
        $this->sheet->mergeCells("A3:{$lastCol}3");
        $this->sheet->setCellValue('A3', 'تاريخ الطباعة: ' . now()->format('d/m/Y H:i'));
        $this->sheet->getStyle('A3')->applyFromArray([
            'font'      => ['size' => 10, 'color' => ['rgb' => '666666']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F3FF']],
        ]);

        // رؤوس الأعمدة
        foreach ($columns as $i => $col) {
            $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '4';
            $this->sheet->setCellValue($cellRef, $col);
        }

        $headerRange = "A4:{$lastCol}4";
        $this->sheet->getStyle($headerRange)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a1a2e']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '444444']]],
        ]);
        $this->sheet->getRowDimension(4)->setRowHeight(22);
    }

    private function addRow(int $row, array $values, bool $isTotal = false, ?string $bgColor = null): void
    {
        foreach ($values as $i => $val) {
            $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $row;
            $this->sheet->setCellValue($cellRef, $val);
        }

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($values));
        $range   = "A{$row}:{$lastCol}{$row}";

        $style = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ];

        if ($isTotal) {
            $style['font'] = ['bold' => true, 'size' => 11];
            $style['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDE9FE']];
        } elseif ($bgColor) {
            $style['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]];
        } elseif ($row % 2 === 0) {
            $style['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9F9FC']];
        }

        $this->sheet->getStyle($range)->applyFromArray($style);
    }

    private function autoSizeColumns(int $colCount): void
    {
        for ($i = 1; $i <= $colCount; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    // ===== تقرير المبيعات =====
    private function buildSalesSheet(string $from, string $to): void
    {
        $this->sheet->setTitle('المبيعات');
        $cols = ['رقم الفاتورة', 'التاريخ', 'العميل', 'طريقة الدفع', 'الإجمالي', 'المدفوع', 'المتبقي', 'الحالة'];
        $this->addHeader("تقرير المبيعات من {$from} إلى {$to}", $cols);

        $invoices = Invoice::with('customer')
            ->where('type', 'sale')
            ->whereBetween('invoice_date', [$from, $to])
            ->orderBy('invoice_date', 'desc')
            ->get();

        $row = 5;
        foreach ($invoices as $inv) {
            $this->addRow($row++, [
                $inv->invoice_number,
                $inv->invoice_date->format('d/m/Y'),
                $inv->customer?->name ?? 'عميل نقدي',
                match($inv->payment_type) { 'cash'=>'نقدي','credit'=>'آجل','card'=>'بطاقة','wallet'=>'محفظة',default=>$inv->payment_type },
                $inv->total,
                $inv->paid_amount,
                $inv->remaining_amount,
                match($inv->status) { 'paid'=>'مدفوعة','partial'=>'جزئي','confirmed'=>'مؤكدة','draft'=>'مسودة',default=>$inv->status },
            ]);
        }

        // إجمالي
        $this->addRow($row, ['الإجمالي', '', '', '', $invoices->sum('total'), $invoices->sum('paid_amount'), $invoices->sum('remaining_amount'), ''], true);
        $this->autoSizeColumns(count($cols));
    }

    // ===== تقرير الأرباح =====
    private function buildProfitSheet(string $from, string $to): void
    {
        $this->sheet->setTitle('الأرباح');
        $cols = ['الصنف', 'الكمية المباعة', 'إجمالي الإيرادات', 'إجمالي التكلفة', 'صافي الربح', 'هامش الربح %'];
        $this->addHeader("تقرير الأرباح والخسائر من {$from} إلى {$to}", $cols);

        $items = InvoiceItem::whereHas('invoice', fn($q) =>
            $q->where('type','sale')->whereBetween('invoice_date',[$from,$to])
        )->get();

        $row = 5;
        $totalRev = 0; $totalCost = 0;

        foreach ($items->groupBy('product_name') as $name => $group) {
            $rev  = $group->sum('total');
            $cost = $group->sum(fn($i) => $i->quantity * $i->cost_price);
            $profit = $rev - $cost;
            $margin = $rev > 0 ? round($profit / $rev * 100, 1) : 0;
            $totalRev += $rev; $totalCost += $cost;

            $this->addRow($row++, [$name, $group->sum('quantity'), $rev, $cost, $profit, $margin . '%'],
                false, $profit < 0 ? 'FFE5E5' : null);
        }

        $totalProfit = $totalRev - $totalCost;
        $totalMargin = $totalRev > 0 ? round($totalProfit / $totalRev * 100, 1) : 0;
        $this->addRow($row, ['الإجمالي', '', $totalRev, $totalCost, $totalProfit, $totalMargin . '%'], true);
        $this->autoSizeColumns(count($cols));
    }

    // ===== تقرير المديونيات =====
    private function buildDebtsSheet(): void
    {
        $this->sheet->setTitle('المديونيات');
        $cols = ['كود العميل', 'اسم العميل', 'التليفون', 'النوع', 'إجمالي المديونية'];
        $this->addHeader('تقرير المديونيات', $cols);

        $customers = Customer::withSum(['invoices as total_debt' => fn($q) =>
            $q->where('type','sale')->where('remaining_amount','>',0)
        ], 'remaining_amount')->having('total_debt','>',0)->orderByDesc('total_debt')->get();

        $row = 5;
        foreach ($customers as $c) {
            $this->addRow($row++, [$c->code, $c->name, $c->phone??'-', $c->type==='company'?'شركة':'فرد', $c->total_debt]);
        }
        $this->addRow($row, ['الإجمالي','','','',$customers->sum('total_debt')], true);
        $this->autoSizeColumns(count($cols));
    }

    // ===== تقرير المخزون =====
    private function buildStockSheet(): void
    {
        $this->sheet->setTitle('المخزون');
        $cols = ['الكود', 'الصنف', 'المجموعة', 'الوحدة', 'الكمية الحالية', 'الحد الأدنى', 'سعر البيع', 'سعر التكلفة', 'الحالة'];
        $this->addHeader('تقرير المخزون', $cols);

        $products = Product::with('category')->where('is_active', true)->get();
        $row = 5;
        foreach ($products as $p) {
            $isLow = ($p->quantity ?? 0) <= $p->min_quantity;
            $this->addRow($row++, [
                $p->code, $p->name, $p->category?->name??'-', $p->unit,
                $p->quantity ?? 0, $p->min_quantity, $p->sale_price, $p->cost_price,
                $isLow ? '⚠️ ناقص' : '✅ كافي',
            ], false, $isLow ? 'FFE5E5' : null);
        }
        $this->autoSizeColumns(count($cols));
    }

    // ===== أكثر الأصناف مبيعاً =====
    private function buildTopProductsSheet(string $from, string $to): void
    {
        $this->sheet->setTitle('الأكثر مبيعاً');
        $cols = ['#', 'الصنف', 'الكود', 'الكمية المباعة', 'إجمالي الإيرادات'];
        $this->addHeader("أكثر الأصناف مبيعاً من {$from} إلى {$to}", $cols);

        $items = InvoiceItem::whereHas('invoice', fn($q) =>
            $q->where('type','sale')->whereBetween('invoice_date',[$from,$to])
        )->get();

        $row = 5; $i = 1;
        foreach ($items->groupBy('product_name')->map(fn($g) => [
            'name'=>$g->first()->product_name,'code'=>$g->first()->product_code,
            'qty'=>$g->sum('quantity'),'total'=>$g->sum('total'),
        ])->sortByDesc('total')->values() as $p) {
            $bg = match($i) { 1=>'FFF3CD', 2=>'F0F0F0', 3=>'FFE8D0', default=>null };
            $this->addRow($row++, [$i++, $p['name'], $p['code'], $p['qty'], $p['total']], false, $bg);
        }
        $this->autoSizeColumns(count($cols));
    }

    // ===== تقرير الحضور =====
    private function buildAttendanceSheet(string $from, string $to): void
    {
        $this->sheet->setTitle('الحضور');
        $cols = ['الموظف', 'القسم', 'أيام الحضور', 'أيام الغياب', 'أيام التأخير', 'أيام الإجازة', 'ساعات العمل', 'دقائق التأخير'];
        $this->addHeader("تقرير الحضور من {$from} إلى {$to}", $cols);

        $row = 5;
        foreach (Employee::where('status','active')->get() as $emp) {
            $records = Attendance::where('employee_id',$emp->id)->whereBetween('date',[$from,$to])->get();
            $this->addRow($row++, [
                $emp->name, $emp->department,
                $records->where('status','present')->count(),
                $records->where('status','absent')->count(),
                $records->where('status','late')->count(),
                $records->where('status','vacation')->count(),
                $records->sum('working_hours'),
                $records->sum('late_minutes'),
            ]);
        }
        $this->autoSizeColumns(count($cols));
    }
}