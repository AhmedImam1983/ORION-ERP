<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<style>
body{font-family:'DejaVu Sans',sans-serif;font-size:9px;color:#172033;direction:rtl}
.header{border-bottom:2px solid #6d5dfc;padding-bottom:9px;margin-bottom:10px}
.brand{font-size:16px;font-weight:bold;color:#5b4fff}.title{font-size:15px;font-weight:bold;margin-top:4px}.sub{font-size:9px;color:#687386;margin-top:3px}
.summary{width:100%;margin-bottom:10px;border-collapse:separate;border-spacing:6px}
.summary td{border:1px solid #e3e7ef;background:#f7f8fb;padding:8px;text-align:center;border-radius:5px}
.summary small{display:block;color:#778195;font-size:8px;margin-bottom:3px}.summary strong{font-size:12px}
.ok{color:#169b61}.bad{color:#c9394f}.dr{color:#c9394f}.cr{color:#169b61}
table.report{width:100%;border-collapse:collapse}
.report th{background:#18223a;color:#fff;padding:5px 3px;text-align:center;font-size:7px}
.report th.group{background:#273455}.report td{border-bottom:1px solid #e7eaf0;padding:4px 3px;text-align:center;font-size:7px}
.report td.right{text-align:right}.report tr.reverse td{background:#fff9e8}
.type{font-size:7px;color:#59677d}
tfoot td{background:#eef0ff!important;font-weight:bold;border-top:1px solid #7468db!important}
.footer{margin-top:9px;border-top:1px solid #e1e5ec;padding-top:6px;text-align:center;font-size:7px;color:#8a93a3}
</style>
</head>
<body>
@php
$typeMap=['asset'=>'أصول','liability'=>'خصوم','equity'=>'حقوق الملكية','revenue'=>'إيرادات','expense'=>'مصروفات'];
$diff=abs($totalDebit-$totalCredit);
@endphp

<div class="header">
    <div class="brand">Orion ERP</div>
    <div class="title">ميزان المراجعة</div>
    <div class="sub">الفترة من {{ $dateFrom }} إلى {{ $dateTo }} · تاريخ الاستخراج {{ now()->format('d/m/Y H:i') }}</div>
</div>

<table class="summary">
<tr>
    <td><small>الرصيد الختامي المدين</small><strong class="dr">{{ number_format($totalDebit,2) }} ر.س</strong></td>
    <td><small>الرصيد الختامي الدائن</small><strong class="cr">{{ number_format($totalCredit,2) }} ر.س</strong></td>
    <td><small>فرق الميزان</small><strong class="{{ $diff < 0.01 ? 'ok':'bad' }}">{{ number_format($diff,2) }} ر.س</strong></td>
    <td><small>عدد الحسابات</small><strong>{{ $accountsCount }}</strong></td>
</tr>
</table>

<table class="report">
<thead>
<tr>
    <th rowspan="2">الكود</th>
    <th rowspan="2">اسم الحساب</th>
    <th rowspan="2">النوع</th>
    <th colspan="2" class="group">الرصيد الافتتاحي</th>
    <th colspan="2" class="group">حركة الفترة</th>
    <th colspan="2" class="group">الإجمالي</th>
    <th colspan="2" class="group">الرصيد الختامي</th>
</tr>
<tr>
    <th>مدين</th><th>دائن</th>
    <th>مدين</th><th>دائن</th>
    <th>مدين</th><th>دائن</th>
    <th>مدين</th><th>دائن</th>
</tr>
</thead>
<tbody>
@foreach($accounts as $acc)
<tr class="{{ $acc['reverse_nature'] ? 'reverse':'' }}">
    <td>{{ $acc['code'] }}</td>
    <td class="right">{{ $acc['name'] }}</td>
    <td class="type">{{ $typeMap[$acc['type']] ?? $acc['type'] }}</td>
    <td class="dr">{{ $acc['opening_debit'] > 0 ? number_format($acc['opening_debit'],2):'-' }}</td>
    <td class="cr">{{ $acc['opening_credit'] > 0 ? number_format($acc['opening_credit'],2):'-' }}</td>
    <td class="dr">{{ $acc['period_debit'] > 0 ? number_format($acc['period_debit'],2):'-' }}</td>
    <td class="cr">{{ $acc['period_credit'] > 0 ? number_format($acc['period_credit'],2):'-' }}</td>
    <td class="dr">{{ $acc['total_debit'] > 0 ? number_format($acc['total_debit'],2):'-' }}</td>
    <td class="cr">{{ $acc['total_credit'] > 0 ? number_format($acc['total_credit'],2):'-' }}</td>
    <td class="dr">{{ $acc['balance_debit'] > 0 ? number_format($acc['balance_debit'],2):'-' }}</td>
    <td class="cr">{{ $acc['balance_credit'] > 0 ? number_format($acc['balance_credit'],2):'-' }}</td>
</tr>
@endforeach
</tbody>
<tfoot>
<tr>
    <td colspan="5" class="right">الإجمالي · حركة الفترة / الرصيد الختامي</td>
    <td class="dr">{{ number_format($periodDebit,2) }}</td>
    <td class="cr">{{ number_format($periodCredit,2) }}</td>
    <td colspan="2"></td>
    <td class="dr">{{ number_format($totalDebit,2) }}</td>
    <td class="cr">{{ number_format($totalCredit,2) }}</td>
</tr>
</tfoot>
</table>

<div class="footer">Orion ERP · Trial Balance · يعتمد التقرير على القيود المرحلة فقط</div>
</body>
</html>