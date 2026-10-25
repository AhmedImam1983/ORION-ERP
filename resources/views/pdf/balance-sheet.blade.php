<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<style>
body{font-family:'DejaVu Sans',sans-serif;font-size:9px;color:#182033;direction:rtl}
.header{border-bottom:2px solid #6d5dfc;padding-bottom:9px;margin-bottom:10px}
.brand{font-size:16px;font-weight:bold;color:#5b4fff}.title{font-size:15px;font-weight:bold;margin-top:4px}.sub{font-size:8px;color:#687386;margin-top:3px}
.control{padding:7px;border-radius:4px;text-align:center;margin-bottom:8px;font-weight:bold}.ok{background:#edfdf5;color:#14945d}.bad{background:#fff0f2;color:#c83b50}
.kpis{width:100%;border-collapse:separate;border-spacing:5px;margin-bottom:8px}.kpis td{background:#f6f7fb;border:1px solid #e4e7ef;padding:7px;text-align:center}.kpis small{display:block;color:#7b8597;font-size:7px}.kpis strong{font-size:11px}
.grid{width:100%;border-collapse:separate;border-spacing:8px;table-layout:fixed}.grid>tbody>tr>td{vertical-align:top;width:50%}
.box{border:1px solid #e2e6ef}.box-title{padding:7px 8px;background:#17233b;color:#fff;font-size:11px;font-weight:bold}.section-title{padding:6px 8px;background:#eef1f7;font-weight:bold;font-size:9px}.row{width:100%;border-collapse:collapse}.row td{padding:4px 7px;border-bottom:1px solid #eceff4;font-size:8px}.row td:last-child{text-align:left;font-weight:bold}.subtotal{background:#f7f8fb;font-weight:bold}.grand{background:#e9e8ff;font-weight:bold;font-size:10px}.red{color:#c93b51}.green{color:#14945d}.blue{color:#217bb4}.purple{color:#6954c7}.muted{color:#8a93a3}.footer{margin-top:9px;border-top:1px solid #e1e5ec;padding-top:6px;text-align:center;font-size:7px;color:#8a93a3}
</style>
</head>
<body>
<div class="header">
    <div class="brand">{{ \App\Models\Setting::get('company_name', 'Orion ERP') }}</div>
    <div class="title">الميزانية العمومية · Balance Sheet</div>
    <div class="sub">بتاريخ {{ $asOfDate }} @if($showComparison) · مقارنة بتاريخ {{ $comparisonDate }} @endif · تم الاستخراج {{ now()->format('d/m/Y H:i') }}</div>
</div>

<div class="control {{ $isBalanced ? 'ok':'bad' }}">
    {{ $isBalanced ? 'المعادلة المحاسبية متوازنة' : 'الميزانية غير متوازنة' }}
    · الفرق {{ number_format(abs($balanceDifference),2) }} ر.س
</div>

<table class="kpis">
<tr>
    <td><small>إجمالي الأصول</small><strong class="blue">{{ number_format($totalAssets,2) }} ر.س</strong></td>
    <td><small>إجمالي الخصوم</small><strong class="red">{{ number_format($totalLiabilities,2) }} ر.س</strong></td>
    <td><small>حقوق الملكية</small><strong class="purple">{{ number_format($totalEquity,2) }} ر.س</strong></td>
    <td><small>رأس المال العامل</small><strong>{{ number_format($workingCapital,2) }} ر.س</strong></td>
</tr>
</table>

<table class="grid">
<tr>
<td>
    <div class="box">
        <div class="box-title">الأصول · {{ number_format($totalAssets,2) }} ر.س</div>

        <div class="section-title">الأصول المتداولة · {{ number_format($totalCurrentAssets,2) }}</div>
        <table class="row">
        @foreach($currentAssets as $i)
            <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="{{ $i['balance'] < 0 ? 'red':'blue' }}">{{ number_format($i['balance'],2) }}</td></tr>
        @endforeach
        <tr class="subtotal"><td>إجمالي الأصول المتداولة</td><td>{{ number_format($totalCurrentAssets,2) }}</td></tr>
        </table>

        <div class="section-title">الأصول الثابتة · صافي {{ number_format($totalFixedAssets,2) }}</div>
        <table class="row">
        @foreach($fixedAssets as $i)
            <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="blue">{{ number_format($i['balance'],2) }}</td></tr>
        @endforeach
        @foreach($fixedAssetContra as $i)
            <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="red">{{ number_format($i['balance'],2) }}</td></tr>
        @endforeach
        @if($totalFixedAssetContra > 0)
            <tr class="subtotal"><td>إجمالي التكلفة</td><td>{{ number_format($totalFixedAssetGross,2) }}</td></tr>
            <tr class="subtotal"><td>(-) الحسابات المقابلة / مجمع الإهلاك</td><td class="red">{{ number_format($totalFixedAssetContra,2) }}</td></tr>
        @endif
        <tr class="subtotal"><td>صافي الأصول الثابتة</td><td>{{ number_format($totalFixedAssets,2) }}</td></tr>
        <tr class="grand"><td>إجمالي الأصول</td><td>{{ number_format($totalAssets,2) }} ر.س</td></tr>
        </table>
    </div>
</td>

<td>
    <div class="box">
        <div class="box-title">الخصوم وحقوق الملكية · {{ number_format($totalLiabilitiesAndEquity,2) }} ر.س</div>

        <div class="section-title">الخصوم المتداولة · {{ number_format($totalCurrentLiabilities,2) }}</div>
        <table class="row">
        @foreach($currentLiabilities as $i)
            <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="red">{{ number_format($i['balance'],2) }}</td></tr>
        @endforeach
        <tr class="subtotal"><td>إجمالي الخصوم المتداولة</td><td>{{ number_format($totalCurrentLiabilities,2) }}</td></tr>
        </table>

        @if(!empty($longTermLiabilities))
        <div class="section-title">الخصوم طويلة الأجل · {{ number_format($totalLongTermLiabilities,2) }}</div>
        <table class="row">
        @foreach($longTermLiabilities as $i)
            <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="red">{{ number_format($i['balance'],2) }}</td></tr>
        @endforeach
        <tr class="subtotal"><td>إجمالي الخصوم طويلة الأجل</td><td>{{ number_format($totalLongTermLiabilities,2) }}</td></tr>
        </table>
        @endif

        <div class="section-title">حقوق الملكية · {{ number_format($totalEquity,2) }}</div>
        <table class="row">
        @foreach($equityAccounts as $i)
            <tr><td>{{ $i['code'] }} · {{ $i['name'] }}</td><td class="purple">{{ number_format($i['balance'],2) }}</td></tr>
        @endforeach
        <tr><td>الأرباح المحتجزة / نتيجة الأعمال</td><td class="{{ $retainedEarnings >= 0 ? 'green':'red' }}">{{ number_format($retainedEarnings,2) }}</td></tr>
        <tr class="subtotal"><td>إجمالي حقوق الملكية</td><td>{{ number_format($totalEquity,2) }}</td></tr>
        <tr class="grand"><td>إجمالي الخصوم وحقوق الملكية</td><td>{{ number_format($totalLiabilitiesAndEquity,2) }} ر.س</td></tr>
        </table>
    </div>
</td>
</tr>
</table>

<div class="footer">{{ \App\Models\Setting::get('company_name', 'Orion ERP') }} · Balance Sheet · يعتمد التقرير على القيود المرحلة فقط</div>
</body>
</html>