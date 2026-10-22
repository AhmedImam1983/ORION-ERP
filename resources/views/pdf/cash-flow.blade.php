<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<style>
body{font-family:dejavusans;font-size:10px;color:#182033}h1{font-size:20px;margin:0 0 4px}.sub{color:#667085;margin-bottom:14px}.kpis{width:100%;border-collapse:separate;border-spacing:6px}.kpis td{border:1px solid #d9deea;padding:10px;background:#f8f9fc}.label{color:#667085;font-size:9px}.value{font-size:15px;font-weight:bold;margin-top:4px}.pos{color:#087f5b}.neg{color:#c92a2a}.section{margin-top:15px}.section h2{font-size:13px;background:#eef1f7;padding:7px;margin:0}.tbl{width:100%;border-collapse:collapse}.tbl th,.tbl td{border:1px solid #e0e4ec;padding:6px}.tbl th{background:#f7f8fb;color:#475467}.num{text-align:left;direction:ltr}.total{font-weight:bold;background:#f3f5f9}.recon{margin-top:15px;border:1px solid #d9deea;padding:10px}.ok{color:#087f5b;font-weight:bold}.bad{color:#c92a2a;font-weight:bold}
</style>
</head>
<body>
<h1>قائمة التدفقات النقدية</h1>
<div class="sub">الفترة من {{ $dateFrom }} إلى {{ $dateTo }} · مبنية على القيود المرحلة فقط</div>
<table class="kpis"><tr>
<td><div class="label">التشغيل</div><div class="value {{ $operating>=0?'pos':'neg' }}">{{ number_format($operating,2) }} ر.س</div></td>
<td><div class="label">الاستثمار</div><div class="value {{ $investing>=0?'pos':'neg' }}">{{ number_format($investing,2) }} ر.س</div></td>
<td><div class="label">التمويل</div><div class="value {{ $financing>=0?'pos':'neg' }}">{{ number_format($financing,2) }} ر.س</div></td>
<td><div class="label">صافي التدفق</div><div class="value {{ $netCashFlow>=0?'pos':'neg' }}">{{ number_format($netCashFlow,2) }} ر.س</div></td>
</tr></table>

@foreach(['operating'=>'الأنشطة التشغيلية','investing'=>'الأنشطة الاستثمارية','financing'=>'الأنشطة التمويلية'] as $cat=>$label)
<div class="section"><h2>{{ $label }}</h2><table class="tbl"><thead><tr><th>الكود</th><th>الحساب</th><th>عدد الحركات</th><th>التدفق</th></tr></thead><tbody>
@forelse(($grouped[$cat] ?? []) as $row)
<tr><td>{{ $row['code'] }}</td><td>{{ $row['name'] }}</td><td>{{ $row['count'] }}</td><td class="num">{{ number_format($row['amount'],2) }}</td></tr>
@empty<tr><td colspan="4">لا توجد حركة</td></tr>@endforelse
<tr class="total"><td colspan="3">إجمالي {{ $label }}</td><td class="num">{{ number_format($$cat,2) }}</td></tr>
</tbody></table></div>
@endforeach

<div class="recon">
<strong>تسوية النقدية</strong><br><br>
رصيد أول الفترة: {{ number_format($openingCash,2) }} ر.س<br>
+ صافي التدفق المصنف: {{ number_format($netCashFlow,2) }} ر.س<br>
= الرصيد المحسوب: {{ number_format($openingCash+$netCashFlow,2) }} ر.س<br>
الرصيد الفعلي من الأستاذ: {{ number_format($closingCash,2) }} ر.س<br>
<span class="{{ $isReconciled?'ok':'bad' }}">فرق التسوية: {{ number_format($reconciliationDifference,2) }} ر.س · {{ $isReconciled?'متسوي':'يحتاج مراجعة' }}</span>
</div>
</body>
</html>
