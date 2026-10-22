<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body{font-family:'DejaVu Sans',sans-serif;font-size:10px;color:#1a1a2e;direction:rtl}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding-bottom:8px;border-bottom:3px solid #6c63ff}
.logo{font-size:16px;font-weight:bold;color:#6c63ff}
.title{font-size:14px;font-weight:bold}
.sub{font-size:10px;color:#666}
.kpis{display:flex;gap:8px;margin-bottom:12px}
.kpi{flex:1;border-radius:5px;padding:8px;text-align:center}
.kpi label{font-size:9px;display:block;margin-bottom:2px;color:rgba(255,255,255,.8)}
.kpi strong{font-size:13px;font-weight:bold;color:#fff}
table{width:100%;border-collapse:collapse}
thead tr{background:#6c63ff;color:#fff}
th{padding:6px 4px;text-align:right;font-size:9px;font-weight:bold}
td{padding:5px 4px;border-bottom:1px solid #eee;font-size:9px}
tfoot tr td{background:#4c3fce;color:#fff;font-weight:bold;padding:6px 4px}
.footer{text-align:center;margin-top:10px;font-size:9px;color:#999;border-top:1px solid #eee;padding-top:6px}
</style>
</head>
<body>
<div class="header">
    <div class="logo">⚡ Orion ERP</div>
    <div>
        <div class="title">كشف رواتب شهر {{ $month }} {{ $year }}</div>
        <div class="sub">{{ now()->format('d/m/Y H:i') }}</div>
    </div>
</div>
<div class="kpis">
    <div class="kpi" style="background:#1e40af"><label>الموظفين</label><strong>{{ $totalEmployees }}</strong></div>
    <div class="kpi" style="background:#16a34a"><label>المرتبات الأساسية</label><strong>{{ number_format($totalBasic,2) }} ر.س</strong></div>
    <div class="kpi" style="background:#dc2626"><label>الخصومات</label><strong>{{ number_format($totalDeductions,2) }} ر.س</strong></div>
    <div class="kpi" style="background:#7c3aed"><label>صافي الرواتب</label><strong>{{ number_format($totalNet,2) }} ر.س</strong></div>
</div>
<table>
    <thead>
        <tr>
            <th>الموظف</th><th>القسم</th><th>الأساسي</th><th>البدلات</th>
            <th>الإضافي</th><th>المكافأة</th><th>الخصومات</th><th>الصافي</th><th>الحالة</th>
        </tr>
    </thead>
    <tbody>
        @foreach($salaries as $s)
        <tr>
            <td>{{ $s['name'] }}</td>
            <td>{{ $s['department'] }}</td>
            <td>{{ number_format($s['basic_salary'],2) }}</td>
            <td>{{ $s['allowances'] > 0 ? number_format($s['allowances'],2) : '-' }}</td>
            <td>{{ $s['overtime'] > 0 ? number_format($s['overtime'],2) : '-' }}</td>
            <td>{{ $s['bonus'] > 0 ? number_format($s['bonus'],2) : '-' }}</td>
            <td style="color:#dc2626">{{ $s['deductions'] > 0 ? number_format($s['deductions'],2) : '-' }}</td>
            <td style="font-weight:bold;color:#7c3aed">{{ number_format($s['net_salary'],2) }} ر.س</td>
            <td>{{ ['paid'=>'مصروف','approved'=>'معتمد','draft'=>'مسودة'][$s['status']] ?? $s['status'] }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">الإجمالي — {{ $totalEmployees }} موظف</td>
            <td>{{ number_format($totalBasic,2) }}</td>
            <td>{{ number_format($totalAllowances,2) }}</td>
            <td>{{ number_format($totalOvertime,2) }}</td>
            <td>{{ number_format($totalBonus,2) }}</td>
            <td>{{ number_format($totalDeductions,2) }}</td>
            <td>{{ number_format($totalNet,2) }} ر.س</td>
            <td></td>
        </tr>
    </tfoot>
</table>
<div class="footer">Orion ERP · كشف رواتب · {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>