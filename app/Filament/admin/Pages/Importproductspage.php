<?php

namespace App\Filament\Admin\Pages;

use App\Jobs\ImportProductsJob;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ImportProductsPage extends Page
{

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'storekeeper']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'استيراد أصناف';
    protected static ?string $title = 'استيراد الأصناف من Excel';
    protected static ?int $navigationSort = 10;
    protected string $view = 'filament.admin.pages.import-products';

    public static function getNavigationGroup(): ?string
    {
        return 'المخازن · Warehouse';
    }

    public ?string $uploadedFile  = null;
    public bool $importing        = false;
    public ?string $importResult  = null;
    public ?int $selectedWarehouseId = null;

    public function mount(): void
    {
        // المخزن الافتراضي
        $default = \App\Models\Warehouse::where('is_default', true)->first();
        $this->selectedWarehouseId = $default?->id;
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'اسم الصنف *',
            'B' => 'كود الصنف',
            'C' => 'باركود',
            'D' => 'الوحدة',
            'E' => 'المجموعة',
            'F' => 'سعر التكلفة',
            'G' => 'سعر البيع *',
            'H' => 'الكمية',
            'I' => 'الحد الأدنى',
            'J' => 'الوصف',
            'K' => 'نوع الضريبة',
            'L' => 'نسبة الضريبة %',
        ];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . '1', $header);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $samples = [
            ['لاب توب Dell Core i5', 'LT-001', '1234567890', 'قطعة', 'إلكترونيات', 8000, 12000, 10, 2, 'لاب توب للأعمال', 'S', 15],
            ['شاشة Samsung 24 بوصة', 'SCR-001', '0987654321', 'قطعة', 'إلكترونيات', 1500, 2500, 5, 1, 'شاشة Full HD', 'S', 15],
            ['كيبورد لاسلكي', 'KB-001', '', 'قطعة', 'ملحقات', 200, 350, 20, 5, '', 'S', 15],
        ];

        foreach ($samples as $i => $row) {
            $rowNum = $i + 2;
            foreach (array_values($row) as $j => $value) {
                $col = chr(65 + $j);
                $sheet->setCellValue($col . $rowNum, $value);
            }
            $rowStyle = [
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $i % 2 === 0 ? 'F8F9FF' : 'FFFFFF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ];
            $sheet->getStyle('A' . $rowNum . ':L' . $rowNum)->applyFromArray($rowStyle);
        }

        $widths = ['A'=>25,'B'=>15,'C'=>18,'D'=>12,'E'=>15,'F'=>15,'G'=>15,'H'=>12,'I'=>12,'J'=>25,'K'=>15,'L'=>18];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->setTitle('الأصناف');
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, 'products_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importFile(): void
    {
        if (!$this->uploadedFile) {
            Notification::make()->title('اختر ملف أولاً!')->warning()->send();
            return;
        }

        try {
            // جرب المسارات المختلفة
            $paths = [
                Storage::path($this->uploadedFile),
                storage_path('app/' . $this->uploadedFile),
                storage_path('app/private/' . $this->uploadedFile),
            ];

            $filePath = null;
            foreach ($paths as $path) {
                if (file_exists($path)) { $filePath = $path; break; }
            }

            if (!$filePath) {
                Notification::make()->title('لم يتم العثور على الملف! حاول رفعه مرة أخرى.')->danger()->send();
                $this->uploadedFile = null;
                return;
            }

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);

            array_shift($rows); // تخطي الـ header

            $imported = 0;
            $updated  = 0;
            $errors   = [];

            foreach ($rows as $rowNum => $row) {
                $name = trim($row['A'] ?? '');
                if (empty($name)) continue;

                try {
                    $code        = trim($row['B'] ?? '');
                    $barcode     = trim($row['C'] ?? '');
                    $unit        = trim($row['D'] ?? 'قطعة');
                    $category    = trim($row['E'] ?? '');
                    $costPrice   = floatval(str_replace(',', '', $row['F'] ?? 0));
                    $salePrice   = floatval(str_replace(',', '', $row['G'] ?? 0));
                    $quantity    = floatval($row['H'] ?? 0);
                    $minQty      = floatval($row['I'] ?? 0);
                    $description = trim($row['J'] ?? '');
                    $vatType     = strtoupper(trim($row['K'] ?? 'S'));
                    $vatPercent  = floatval($row['L'] ?? 15);
                    // التحقق من نوع الضريبة
                    if (!in_array($vatType, ['S', 'Z', 'E', 'O'])) $vatType = 'S';
                    if ($vatPercent <= 0 || $vatPercent > 100) $vatPercent = 15;

                    $categoryId = null;
                    if (!empty($category)) {
                        $cat = \App\Models\Category::firstOrCreate(
                            ['name' => $category],
                            ['is_active' => true]
                        );
                        $categoryId = $cat->id;
                    }

                    $product = null;
                    if (!empty($code))    $product = \App\Models\Product::where('code', $code)->first();
                    if (!$product && !empty($barcode)) $product = \App\Models\Product::where('barcode', $barcode)->first();

                    if ($product) {
                        $product->update([
                            'name'         => $name,
                            'unit'         => $unit ?: $product->unit,
                            'category_id'  => $categoryId ?? $product->category_id,
                            'cost_price'   => $costPrice ?: $product->cost_price,
                            'sale_price'   => $salePrice ?: $product->sale_price,
                            'quantity'     => $quantity ?: $product->quantity,
                            'min_quantity' => $minQty,
                            'description'  => $description ?: $product->description,
                            'barcode'      => $barcode ?: $product->barcode,
                            'warehouse_id' => $this->selectedWarehouseId ?? $product->warehouse_id,
                            'vat_type'     => $vatType,
                            'vat_percent'  => $vatPercent,
                        ]);
                        $updated++;
                    } else {
                        \App\Models\Product::create([
                            'name'         => $name,
                            'code'         => $code ?: 'PRD-' . str_pad(\App\Models\Product::count() + 1, 6, '0', STR_PAD_LEFT),
                            'barcode'      => $barcode ?: null,
                            'unit'         => $unit ?: 'قطعة',
                            'category_id'  => $categoryId,
                            'cost_price'   => $costPrice,
                            'sale_price'   => $salePrice,
                            'quantity'     => $quantity,
                            'min_quantity' => $minQty,
                            'description'  => $description,
                            'warehouse_id' => $this->selectedWarehouseId,
                            'vat_type'     => $vatType,
                            'vat_percent'  => $vatPercent,
                            'is_active'    => true,
                        ]);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = 'سطر ' . ($rowNum + 1) . ': ' . $e->getMessage();
                }
            }

            Storage::delete($this->uploadedFile);
            $this->uploadedFile = null;

            $msg = "تم الاستيراد: {$imported} صنف جديد · {$updated} تم تحديثه";
            if (!empty($errors)) $msg .= ' · أخطاء: ' . count($errors);

            $this->importResult = $msg;
            Notification::make()->title('✅ ' . $msg)->success()->send();

        } catch (\Exception $e) {
            Notification::make()->title('خطأ: ' . $e->getMessage())->danger()->send();
        }
    }
}