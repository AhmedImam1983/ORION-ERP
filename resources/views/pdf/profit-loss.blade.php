<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<style>
body{font-family:'DejaVu Sans',sans-serif;font-size:9px;color:#182033;direction:rtl}
.header{border-bottom:2px solid #6d5dfc;padding-bottom:9px;margin-bottom:10px}.brand{font-size:16px;font-weight:bold;color:#5b4fff}.title{font-size:15px;font-weight:bold;margin-top:4px}.sub{font-size:8px;color:#687386;margin-top:3px}
.kpis{width:100%;border-collapse:separate;border-spacing:5px;margin-bottom:9px}.kpis td{background:#f7f8fb;border:1px solid #e4e7ef;padding:7px;text-align:center}.kpis small{display:block;color:#7b8597;font-size:7px}.kpis strong{font-size:11px}
.section{border:1px solid #e3e7ef;margin-bottom:7px}.section-title{background:#18233b;color:#fff;padding:6px 8px;font-weight:bold}.row{width:100%;border-collapse:collapse}.row td{padding:4px 7px;border-bottom:1px solid #eceff4;font-size:8px}.row td:last-child{text-align:left;font-weight:bold}.subtotal{background:#f7f8fb;font-weight:bold}.result{background:#f0efff;font-weight:bold;font-size:10px}
.green{color:#14945d}.red{color:#c93b51}.blue{color:#217bb4}.purple{color:#6954c7}.amber{color:#b47900}.footer{margin-top:9px;border-top:1px solid #e1e5ec;padding-top:6px;text-align:center;font-size:7px;color:#8a93a3}
</style>
</head>
<body>
<div class="header">
    <div class="brand">{{ \App\Models\Setting::get('company_name', 'Orion ERP') }}</div>
    <div class="title">قائمة الأرباح والخسائر · P&amp;L</div>
    <div class="sub">من {{ $dateFrom }} إلى {{ $dateTo }} @if($showComparison) · مقارنة {{ $comparisonFrom }} إلى {{ $comparisonTo }} @endif · تم الاستخراج {{ now()->format('d/m/Y H:i') }}</div>
</div>

<table class="kpis">
<tr>
    <td><small>إجمالي الإيرادات</small><strong class="green">{{ number_format($totalRevenue,2) }} ر.س</strong></td>
    <td><small>مجمل الربح</small><strong class="{{ $grossProfit >= 0 ? 'blue':'red' }}">{{ number_format($grossProfit,2) }} ر.س</strong></td>
    <td><small>الربح التشغيلي</small><strong class="{{ $operatingProfit >= 0 ? 'purple':'red' }}">{{ number_format($operatingProfit,2) }} ر.س</strong></td>
    <td><small>صافي الربح</small><strong class="{{ $netProfit >= 0 ? 'green':'red' }}">{{ number_format($netProfit,2) }} ر.س</strong></td>
</tr>
</table>

<div class="section">
    <div class="section-title">إيرادات المبيعات · {{ number_format($salesRevenue,2) }} ر.س</div>
    <table class="row">
        @foreach($salesRevenueDetails as $i)
        <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="{{ $i['amount'] >= 0 ? 'green':'red' }}">{{ number_format($i['amount'],2) }}</td></tr>
        @endforeach
        <tr class="subtotal"><td>صافي إيرادات المبيعات</td><td>{{ number_format($salesRevenue,2) }}</td></tr>
    </table>
</div>

<div class="section">
    <div class="section-title">تكلفة المبيعات · {{ number_format($totalCogs,2) }} ر.س</div>
    <table class="row">
        @foreach($cogsDetails as $i)
        <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="red">{{ number_format($i['amount'],2) }}</td></tr>
        @endforeach
        <tr class="subtotal"><td>إجمالي تكلفة المبيعات</td><td>{{ number_format($totalCogs,2) }}</td></tr>
        <tr class="result"><td>مجمل الربح · {{ number_format($grossProfitMargin,1) }}%</td><td>{{ number_format($grossProfit,2) }}</td></tr>
    </table>
</div>

<div class="section">
    <div class="section-title">المصروفات التشغيلية · {{ number_format($totalOpex,2) }} ر.س</div>
    <table class="row">
        @foreach($opexDetails as $i)
        <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="amber">{{ number_format($i['amount'],2) }}</td></tr>
        @endforeach
        <tr class="subtotal"><td>إجمالي المصروفات التشغيلية</td><td>{{ number_format($totalOpex,2) }}</td></tr>
        <tr class="result"><td>الربح التشغيلي · {{ number_format($operatingProfitMargin,1) }}%</td><td>{{ number_format($operatingProfit,2) }}</td></tr>
    </table>
</div>

@if(!empty($otherRevenueDetails))
<div class="section">
    <div class="section-title">إيرادات أخرى · {{ number_format($otherRevenue,2) }} ر.س</div>
    <table class="row">
        @foreach($otherRevenueDetails as $i)
        <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="green">{{ number_format($i['amount'],2) }}</td></tr>
        @endforeach
    </table>
</div>
@endif

@if(!empty($financialDetails))
<div class="section">
    <div class="section-title">المصروفات التمويلية · {{ number_format($totalFinancial,2) }} ر.س</div>
    <table class="row">
        @foreach($financialDetails as $i)
        <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="red">{{ number_format($i['amount'],2) }}</td></tr>
        @endforeach
    </table>
</div>
@endif

@if(!empty($otherExpenseDetails))
<div class="section">
    <div class="section-title">مصروفات أخرى · {{ number_format($totalOtherExpenses,2) }} ر.س</div>
    <table class="row">
        @foreach($otherExpenseDetails as $i)
        <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="red">{{ number_format($i['amount'],2) }}</td></tr>
        @endforeach
    </table>
</div>
@endif

<table class="kpis">
<tr>
    <td><small>Gross Margin</small><strong>{{ number_format($grossProfitMargin,1) }}%</strong></td>
    <td><small>Operating Margin</small><strong>{{ number_format($operatingProfitMargin,1) }}%</strong></td>
    <td><small>Net Margin</small><strong>{{ number_format($netProfitMargin,1) }}%</strong></td>
    <td><small>صافي {{ $netProfit >= 0 ? 'الربح':'الخسارة' }}</small><strong class="{{ $netProfit >= 0 ? 'green':'red' }}">{{ number_format(abs($netProfit),2) }} ر.س</strong></td>
</tr>
</table>

<div class="footer">{{ \App\Models\Setting::get('company_name', 'Orion ERP') }} · Income Statement · يعتمد التقرير على القيود المرحلة فقط</div>
</body>
</html>