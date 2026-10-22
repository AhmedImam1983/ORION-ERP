<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Milon\Barcode\DNS1D;

class BarcodeController extends Controller
{
    public function print(Request $request): Response
    {
        $productIds = explode(',', $request->get('ids', ''));
        $copies     = max(1, intval($request->get('copies', 1)));
        $size       = in_array($request->get('size'), ['small','medium','large']) ? $request->get('size') : 'medium';

        $products = Product::whereIn('id', array_filter($productIds))->get();

        if ($products->isEmpty()) {
            return response('<h1>لم يتم تحديد أصناف</h1>', 404);
        }

        $dns1d  = new DNS1D();
        $labels = [];

        foreach ($products as $product) {
            $barcode = $product->barcode ?: $product->code;
            if (empty($barcode)) continue;

            try {
                $barcodeImage = $dns1d->getBarcodePNG($barcode, 'C128', 1.5, 40, [0, 0, 0]);
            } catch (\Exception $e) {
                $barcodeImage = null;
            }

            for ($i = 0; $i < $copies; $i++) {
                $labels[] = [
                    'name'         => $product->name,
                    'code'         => $product->code,
                    'barcode_text' => $barcode,
                    'barcode_img'  => $barcodeImage,
                    'price'        => number_format($product->sale_price, 2),
                    'unit'         => $product->unit,
                ];
            }
        }

        $cols = $size === 'small' ? 4 : ($size === 'large' ? 2 : 3);
        $labelSize = $size === 'small' ? '9px' : ($size === 'large' ? '13px' : '11px');
        $priceSize = $size === 'small' ? '11px' : ($size === 'large' ? '16px' : '13px');
        $count = count($labels);

        $labelsHtml = '';
        foreach ($labels as $label) {
            $name    = htmlspecialchars($label['name']);
            $code    = htmlspecialchars($label['barcode_text']);
            $price   = htmlspecialchars($label['price']);
            $unit    = htmlspecialchars($label['unit']);
            $imgTag  = $label['barcode_img']
                ? '<img src="data:image/png;base64,' . $label['barcode_img'] . '" style="margin:2mm auto;display:block;max-width:100%" alt="' . $code . '">'
                : '<div style="height:40px;background:#f0f0f0;margin:4px 0;display:flex;align-items:center;justify-content:center;font-size:10px;color:#999">' . $code . '</div>';

            $labelsHtml .= '
            <div style="border:1px solid #e2e8f0;border-radius:4px;padding:4mm 3mm;text-align:center;background:#fff;page-break-inside:avoid">
                <div style="font-size:' . $labelSize . ';font-weight:700;color:#1e293b;margin-bottom:2mm;line-height:1.3;max-height:2.6em;overflow:hidden">' . $name . '</div>
                ' . $imgTag . '
                <div style="font-size:8px;color:#64748b;font-family:monospace;letter-spacing:1px;margin-bottom:1mm">' . $code . '</div>
                <div style="font-size:' . $priceSize . ';font-weight:900;color:#7c6dff;direction:ltr">' . $price . ' ر.س</div>
                <div style="font-size:9px;color:#94a3b8">' . $unit . '</div>
            </div>';
        }

        $html = '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<title>طباعة باركود</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;background:#f5f5f5;direction:rtl}
.toolbar{background:#1e293b;padding:12px 20px;display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:100}
.btn-print{background:#7c6dff;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer}
.size-select{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:6px;color:#e2e8f0;padding:8px 12px;font-size:13px}
.labels-page{padding:10mm;background:#fff;max-width:210mm;margin:20px auto}
.labels-grid{display:grid;grid-template-columns:repeat(' . $cols . ',1fr);gap:4mm}
@media print{
  @page{margin:8mm;size:A4}
  body{background:#fff}
  .toolbar{display:none!important}
  .labels-page{margin:0;padding:0;max-width:100%}
}
</style>
</head>
<body>
<div class="toolbar">
  <span style="color:#e2e8f0;font-size:15px;font-weight:700;flex:1">🏷️ طباعة باركود</span>
  <span style="color:#94a3b8;font-size:13px">' . $count . ' label</span>
  <select class="size-select" onchange="changeSize(this.value)">
    <option value="small"' . ($size==='small'?' selected':'') . '>صغير — 4 في الصف</option>
    <option value="medium"' . ($size==='medium'?' selected':'') . '>متوسط — 3 في الصف</option>
    <option value="large"' . ($size==='large'?' selected':'') . '>كبير — 2 في الصف</option>
  </select>
  <button class="btn-print" onclick="window.print()">🖨️ طباعة</button>
</div>
<div class="labels-page">
  <div class="labels-grid">' . $labelsHtml . '</div>
</div>
<script>
function changeSize(s){
  var url=new URL(window.location.href);
  url.searchParams.set("size",s);
  window.location.href=url.toString();
}
</script>
</body>
</html>';

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}