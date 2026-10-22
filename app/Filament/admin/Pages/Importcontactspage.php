<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ImportContactsPage extends Page
{

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'استيراد عملاء/موردين';
    protected static ?string $title = 'استيراد العملاء والموردين من Excel';
    protected static ?int $navigationSort = 11;
    protected string $view = 'filament.admin.pages.import-contacts';

    public static function getNavigationGroup(): ?string
    {
        return 'CRM · العملاء والموردين';
    }

    public ?string $uploadedFile = null;
    public ?string $importResult = null;
    public string $importType    = 'customer';

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $isCustomer = $this->importType === 'customer';

        $headers = [
            'A' => 'الاسم *',
            'B' => 'الكود',
            'C' => 'التليفون',
            'D' => 'الموبايل',
            'E' => 'البريد الإلكتروني',
            'F' => 'العنوان',
            'G' => 'الرقم الضريبي',
            'H' => 'الحد الائتماني',
            'I' => 'ملاحظات',
        ];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . '1', $header);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $isCustomer ? '059669' : '7C3AED']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $samples = $isCustomer ? [
            ['أحمد محمد', 'CUS-001', '0223456789', '01012345678', 'ahmed@email.com', 'القاهرة — المعادي', '', '50000', ''],
            ['شركة النور', 'CUS-002', '0234567890', '01098765432', 'info@nour.com', 'الإسكندرية', '123456789', '100000', 'عميل مميز'],
            ['مؤسسة الأمل', 'CUS-003', '', '01123456789', '', 'الجيزة', '', '20000', ''],
        ] : [
            ['مورد الإلكترونيات', 'SUP-001', '0223456789', '01012345678', 'supplier@email.com', 'القاهرة', '987654321', '', ''],
            ['شركة التوريد', 'SUP-002', '', '01098765432', '', 'الإسكندرية', '', '', 'مورد موثوق'],
        ];

        foreach ($samples as $i => $row) {
            $rowNum = $i + 2;
            foreach (array_values($row) as $j => $value) {
                $col = chr(65 + $j);
                $sheet->setCellValue($col . $rowNum, $value);
            }
            $rowStyle = [
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $i % 2 === 0 ? 'F0FDF4' : 'FFFFFF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ];
            $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->applyFromArray($rowStyle);
        }

        $widths = ['A'=>25,'B'=>15,'C'=>15,'D'=>15,'E'=>25,'F'=>25,'G'=>15,'H'=>15,'I'=>25];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->setTitle($isCustomer ? 'العملاء' : 'الموردين');
        $writer = new Xlsx($spreadsheet);
        $filename = $isCustomer ? 'customers_template.xlsx' : 'suppliers_template.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
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
                Notification::make()->title('لم يتم العثور على الملف!')->danger()->send();
                $this->uploadedFile = null;
                return;
            }

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);
            array_shift($rows);

            $imported = 0;
            $updated  = 0;
            $errors   = [];
            $isCustomer = $this->importType === 'customer';
            $model = $isCustomer ? \App\Models\Customer::class : \App\Models\Supplier::class;

            foreach ($rows as $rowNum => $row) {
                $name = trim($row['A'] ?? '');
                if (empty($name)) continue;

                try {
                    $code    = trim($row['B'] ?? '');
                    $phone   = trim($row['C'] ?? '');
                    $mobile  = trim($row['D'] ?? '');
                    $email   = trim($row['E'] ?? '');
                    $address = trim($row['F'] ?? '');
                    $taxNum  = trim($row['G'] ?? '');
                    $credit  = floatval(str_replace(',', '', $row['H'] ?? 0));
                    $notes   = trim($row['I'] ?? '');

                    $record = null;
                    if (!empty($code)) $record = $model::where('code', $code)->first();
                    if (!$record && !empty($email)) $record = $model::where('email', $email)->first();

                    $data = [
                        'name'           => $name,
                        'phone'          => $phone ?: null,
                        'mobile'         => $mobile ?: null,
                        'email'          => $email ?: null,
                        'address'        => $address ?: null,
                        'tax_number'     => $taxNum ?: null,
                        'credit_limit'   => $credit ?: 0,
                        'notes'          => $notes ?: null,
                        'is_active'      => true,
                    ];

                    if ($record) {
                        $record->update($data);
                        $updated++;
                    } else {
                        $data['code'] = $code ?: ($isCustomer ? 'CUS-' : 'SUP-') . str_pad($model::count() + 1, 6, '0', STR_PAD_LEFT);
                        $model::create($data);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = 'سطر ' . ($rowNum + 1) . ': ' . $e->getMessage();
                }
            }

            Storage::delete($this->uploadedFile);
            $this->uploadedFile = null;

            $type = $isCustomer ? 'عميل' : 'مورد';
            $msg  = "تم الاستيراد: {$imported} {$type} جديد · {$updated} تم تحديثه";
            if (!empty($errors)) $msg .= ' · أخطاء: ' . count($errors);

            $this->importResult = $msg;
            Notification::make()->title('✅ ' . $msg)->success()->send();

        } catch (\Exception $e) {
            Notification::make()->title('خطأ: ' . $e->getMessage())->danger()->send();
        }
    }
}