<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body{font-family:'DejaVu Sans',sans-serif;font-size:12px;color:#1a1a2e;direction:rtl}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:10px;border-bottom:3px solid #6c63ff}
.logo{font-size:18px;font-weight:bold;color:#6c63ff}
.title{font-size:15px;font-weight:bold}
.sub{font-size:11px;color:#666;margin-top:3px}
.customer-box{background:#f8f9ff;border:1px solid #e5e7ff;border-radius:6px;padding:10px;margin-bottom:14px;display:flex;gap:20px}
.c-item label{font-size:10px;color:#666;display:block}
.c-item strong{font-size:13px;color:#1a1a2e}
.kpis{display:flex;gap:10px;margin-bottom:14px}
.kpi{flex:1;border-radius:6px;padding:10px;text-align:center}
.kpi label{font-size:10px;display:block;margin-bottom:4px}
.kpi strong{font-size:16px;font-weight:bold}
table{width:100%;border-collapse:collapse}
thead tr{background:#6c63ff;color:#fff}
th{padding:7px 6px;text-align:right;font-size:11px}
td{padding:7px 6px;border-bottom:1px solid #eee;font-size:11px}
tr.opening td{background:#f0f0ff}
tr.payment td{background:#f0fff0}
tfoot tr td{background:#6c63ff;color:#fff;font-weight:bold;padding:8px 6px}
.footer{text-align:center;margin-top:14px;padding-top:8px;border-top:1px solid #eee;font-size:10px;color:#999}
</style>
</head>
<body>
<div class="header">
    <div class="logo">⚡ Orion ERP</div>
    <div>
        <div class="title">كشف حساب عميل</div>
        <div class="sub">من {{ $dateFrom }} إلى {{ $dateTo }}</div>
    </div>
</div>

<div class="customer-box">
    <div class="c-item"><label>اسم العميل</label><strong>{{ $customer->name }}</strong></div>
    <div class="c-item"><label>التليفون</label><strong>{{ $customer->phone ?? '-' }}</strong></div>
    <div class="c-item"><label>النوع</label><strong>{{ $customer->type === 'company' ? 'شركة' : 'فرد' }}</strong></div>
    <div class="c-item"><label>الرقم الضريبي</label><strong>{{ $customer->tax_number ?? '-' }}</strong></div>
</div>

<div class="kpis">
    <div class="kpi" style="background:#fff0f0"><label>إجمالي المبيعات</label><strong style="color:#dc2626">{{ number_format($totalDebit, 2) }} ر.س</strong></div>
    <div class="kpi" style="background:#f0fff0"><label>إجمالي المدفوعات</label><strong style="color:#16a34a">{{ number_format($totalCredit, 2) }} ر.س</strong></div>
    <div class="kpi" style="background:#fff8f0"><label>الرصيد الختامي</label><strong style="color:#ea580c">{{ number_format(abs($closingBalance), 2) }} ر.س {{ $closingBalance >= 0 ? 'مدين' : 'دائن' }}</strong></div>
</div>

<table>
    <thead><tr><th>التاريخ</th><th>البيان</th><th>المرجع</th><th>مدين</th><th>دائن</th><th>الرصيد</th></tr></thead>
    <tbody>
        @foreach($statement as $row)
        <tr class="{{ $row['type'] }}">
            <td>{{ $row['date'] }}</td>
            <td>{{ $row['description'] }}</td>
            <td>{{ $row['reference'] }}</td>
            <td style="color:#dc2626">{{ $row['debit'] > 0 ? number_format($row['debit'], 2).' ر.س' : '-' }}</td>
            <td style="color:#16a34a">{{ $row['credit'] > 0 ? number_format($row['credit'], 2).' ر.س' : '-' }}</td>
            <td style="font-weight:bold;color:{{ $row['balance'] >= 0 ? '#ea580c' : '#16a34a' }}">{{ number_format(abs($row['balance']), 2) }} {{ $row['balance'] >= 0 ? 'مدين' : 'دائن' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr><td colspan="3">الإجمالي</td><td>{{ number_format($totalDebit, 2) }} ر.س</td><td>{{ number_format($totalCredit, 2) }} ر.س</td><td>{{ number_format(abs($closingBalance), 2) }} ر.س {{ $closingBalance >= 0 ? 'مدين' : 'دائن' }}</td></tr>
    </tfoot>
</table>

<div class="footer">Orion ERP · كشف حساب مُنشأ بتاريخ {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>