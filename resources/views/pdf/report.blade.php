<!DOCTYPE html>
<html >
<head>
<meta charset="UTF-8">
<style>
*{box-sizing:border-box;margin:0;padding:0}
@font-face {
    font-family: 'ArabicFont';
    font-style: normal;
    font-weight: normal;
    src: url('{{ storage_path("fonts/NotoSansArabic-Regular.ttf") }}');
}
body{
    font-family: 'ArabicFont', 'DejaVu Sans', sans-serif;
    font-size:12px;
    color:#1a1a2e;
    direction:rtl;
    text-align:right;
}
.page{padding:25px}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:12px;border-bottom:3px solid #6c63ff}
.logo{font-size:20px;font-weight:bold;color:#6c63ff}
.logo span{color:#1a1a2e}
.report-info{text-align:left}
.report-title{font-size:16px;font-weight:bold;color:#1a1a2e}
.report-date{font-size:11px;color:#666;margin-top:3px}
.kpis{display:flex;gap:12px;margin-bottom:16px}
.kpi{flex:1;background:#f8f9ff;border:1px solid #e5e7ff;border-radius:8px;padding:10px 12px;text-align:center}
.kpi-l{font-size:10px;color:#666;margin-bottom:4px}
.kpi-v{font-size:16px;font-weight:bold;color:#6c63ff}
table{width:100%;border-collapse:collapse;margin-top:10px}
thead tr{background:#6c63ff;color:white}
thead th{padding:8px 6px;text-align:right;font-size:11px}
tbody tr:nth-child(even){background:#f8f9ff}
tbody td{padding:7px 6px;font-size:11px;border-bottom:1px solid #eee}
.footer{text-align:center;margin-top:20px;padding-top:10px;border-top:1px solid #eee;font-size:10px;color:#999}
.green{color:#16a34a;font-weight:bold}
.red{color:#dc2626;font-weight:bold}
.purple{color:#7c3aed;font-weight:bold}
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="logo">⚡ Orion <span>ERP</span></div>
        <div class="report-info">
            <div class="report-title">{{ $title }}</div>
            <div class="report-date">من {{ $dateFrom }} إلى {{ $dateTo }} · {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    @if($report === 'sales')
    <div class="kpis">
        <div class="kpi"><div class="kpi-l">إجمالي المبيعات</div><div class="kpi-v">{{ number_format($data['total_sales'], 2) }} ر.س</div></div>
        <div class="kpi"><div class="kpi-l">المحصّل</div><div class="kpi-v" style="color:#16a34a">{{ number_format($data['total_paid'], 2) }} ر.س</div></div>
        <div class="kpi"><div class="kpi-l">المديونيات</div><div class="kpi-v" style="color:#dc2626">{{ number_format($data['total_remaining'], 2) }} ر.س</div></div>
        <div class="kpi"><div class="kpi-l">عدد الفواتير</div><div class="kpi-v">{{ $data['count'] }}</div></div>
    </div>
    <table>
        <thead><tr><th>رقم الفاتورة</th><th>العميل</th><th>التاريخ</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th></tr></thead>
        <tbody>
            @foreach($data['invoices'] as $inv)
            @php $statusMap=['paid'=>'مدفوعة','confirmed'=>'مؤكدة','partial'=>'جزئي','draft'=>'مسودة','cancelled'=>'ملغية']; @endphp
            <tr>
                <td>{{ $inv->invoice_number }}</td>
                <td>{{ $inv->customer?->name ?? 'عميل نقدي' }}</td>
                <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                <td class="purple">{{ number_format($inv->total, 2) }} ر.س</td>
                <td class="green">{{ number_format($inv->paid_amount, 2) }} ر.س</td>
                <td class="{{ $inv->remaining_amount > 0 ? 'red' : 'green' }}">{{ number_format($inv->remaining_amount, 2) }} ر.س</td>
                <td>{{ $statusMap[$inv->status] ?? $inv->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($report === 'profit')
    <div class="kpis">
        <div class="kpi"><div class="kpi-l">الإيرادات</div><div class="kpi-v" style="color:#16a34a">{{ number_format($data['total_revenue'], 2) }} ر.س</div></div>
        <div class="kpi"><div class="kpi-l">التكلفة</div><div class="kpi-v" style="color:#dc2626">{{ number_format($data['total_cost'], 2) }} ر.س</div></div>
        <div class="kpi"><div class="kpi-l">صافي الربح</div><div class="kpi-v">{{ number_format($data['gross_profit'], 2) }} ر.س</div></div>
        <div class="kpi"><div class="kpi-l">هامش الربح</div><div class="kpi-v">{{ $data['profit_margin'] }}٪</div></div>
    </div>
    <table>
        <thead><tr><th>الصنف</th><th>الكمية</th><th>الإيرادات</th><th>التكلفة</th><th>الربح</th><th>الهامش</th></tr></thead>
        <tbody>
            @foreach($data['by_product'] as $p)
            <tr>
                <td>{{ $p['name'] }}</td>
                <td>{{ number_format($p['qty'], 2) }}</td>
                <td class="green">{{ number_format($p['revenue'], 2) }} ر.س</td>
                <td class="red">{{ number_format($p['cost'], 2) }} ر.س</td>
                <td class="{{ $p['profit'] >= 0 ? 'green' : 'red' }}">{{ number_format($p['profit'], 2) }} ر.س</td>
                <td class="purple">{{ $p['margin'] }}٪</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($report === 'debts')
    <div class="kpis">
        <div class="kpi"><div class="kpi-l">إجمالي المديونيات</div><div class="kpi-v" style="color:#dc2626">{{ number_format($data['total_debt'], 2) }} ر.س</div></div>
        <div class="kpi"><div class="kpi-l">عدد العملاء المدينين</div><div class="kpi-v">{{ $data['count'] }}</div></div>
    </div>
    <table>
        <thead><tr><th>كود العميل</th><th>اسم العميل</th><th>التليفون</th><th>إجمالي الدين</th></tr></thead>
        <tbody>
            @foreach($data['customers'] as $c)
            <tr>
                <td>{{ $c->code }}</td>
                <td>{{ $c->name }}</td>
                <td>{{ $c->phone ?? '-' }}</td>
                <td class="red">{{ number_format($c->total_debt, 2) }} ر.س</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($report === 'attendance')
    <table>
        <thead><tr><th>الموظف</th><th>القسم</th><th>حاضر</th><th>غائب</th><th>متأخر</th><th>إجازة</th><th>ساعات العمل</th></tr></thead>
        <tbody>
            @foreach($data['employees'] as $e)
            <tr>
                <td>{{ $e['name'] }}</td>
                <td>{{ $e['department'] }}</td>
                <td class="green">{{ $e['present'] }}</td>
                <td class="red">{{ $e['absent'] }}</td>
                <td style="color:#d97706">{{ $e['late'] }}</td>
                <td>{{ $e['vacation'] }}</td>
                <td class="purple">{{ number_format($e['total_hours'], 1) }} س</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($report === 'top_products')
    <table>
        <thead><tr><th>#</th><th>الصنف</th><th>الكود</th><th>الكمية</th><th>الإيرادات</th></tr></thead>
        <tbody>
            @foreach($data['products'] as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p['name'] }}</td>
                <td>{{ $p['code'] ?? '-' }}</td>
                <td>{{ number_format($p['qty'], 2) }}</td>
                <td class="green">{{ number_format($p['total'], 2) }} ر.س</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @elseif($report === 'stock')
<table>
    <thead>
        <tr>
            <th>الكود</th>
            <th>الصنف</th>
            <th>المجموعة</th>
            <th>الوحدة</th>
            <th>الحد الأدنى</th>
            <th>سعر البيع</th>
            <th>الحالة</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['products'] as $p)
        <tr>
            <td>{{ $p->code }}</td>
            <td>{{ $p->name }}</td>
            <td>{{ $p->category?->name ?? '-' }}</td>
            <td>{{ $p->unit }}</td>
            <td>{{ $p->min_quantity }}</td>
            <td class="green">{{ number_format($p->sale_price, 2) }} ر.س</td>
            <td>{{ $p->is_active ? 'نشط' : 'متوقف' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
    @endif

    <div class="footer">Orion ERP · تقرير مُنشأ بتاريخ {{ now()->format('d/m/Y H:i') }}</div>
</div>
</body>
</html>