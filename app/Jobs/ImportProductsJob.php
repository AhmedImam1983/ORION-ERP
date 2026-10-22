<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public int $userId,
    ) {}

    public function handle(): void
    {
        $spreadsheet = IOFactory::load(storage_path('app/' . $this->filePath));
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true);

        // تخطي أول سطر (headers)
        $headers = array_shift($rows);

        foreach ($rows as $row) {
            $name = trim($row['A'] ?? '');
            if (empty($name)) continue;

            $code     = trim($row['B'] ?? '');
            $barcode  = trim($row['C'] ?? '');
            $unit     = trim($row['D'] ?? 'قطعة');
            $category = trim($row['E'] ?? '');
            $costPrice  = floatval($row['F'] ?? 0);
            $salePrice  = floatval($row['G'] ?? 0);
            $quantity   = floatval($row['H'] ?? 0);
            $minQty     = floatval($row['I'] ?? 0);
            $description = trim($row['J'] ?? '');

            // جيب الـ category id
            $categoryId = null;
            if (!empty($category)) {
                $cat = \App\Models\Category::firstOrCreate(['name' => $category]);
                $categoryId = $cat->id;
            }

            // ابحث عن الصنف بالكود أو الباركود
            $product = null;
            if (!empty($code)) {
                $product = Product::where('code', $code)->first();
            }
            if (!$product && !empty($barcode)) {
                $product = Product::where('barcode', $barcode)->first();
            }

            if ($product) {
                // تحديث
                $product->update([
                    'name'        => $name,
                    'unit'        => $unit ?: $product->unit,
                    'category_id' => $categoryId ?? $product->category_id,
                    'cost_price'  => $costPrice ?: $product->cost_price,
                    'sale_price'  => $salePrice ?: $product->sale_price,
                    'quantity'    => $quantity ?: $product->quantity,
                    'min_quantity'=> $minQty,
                    'description' => $description ?: $product->description,
                    'barcode'     => $barcode ?: $product->barcode,
                ]);
            } else {
                // إضافة جديد
                Product::create([
                    'name'        => $name,
                    'code'        => $code ?: 'PRD-' . str_pad(Product::count() + 1, 6, '0', STR_PAD_LEFT),
                    'barcode'     => $barcode ?: null,
                    'unit'        => $unit ?: 'قطعة',
                    'category_id' => $categoryId,
                    'cost_price'  => $costPrice,
                    'sale_price'  => $salePrice,
                    'quantity'    => $quantity,
                    'min_quantity'=> $minQty,
                    'description' => $description,
                    'is_active'   => true,
                ]);
            }
        }
    }
}