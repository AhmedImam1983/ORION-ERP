<x-filament-panels::page>
<style>
.rep-wrap{display:grid;grid-template-columns:260px 1fr;gap:14px}
.rep-sidebar{display:flex;flex-direction:column;gap:8px}
.rep-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:14px}
.rep-btn{display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:8px;cursor:pointer;border:1px solid transparent;background:transparent;color:#94a3b8;font-family:Cairo,sans-serif;font-size:13px;font-weight:600;width:100%;text-align:right;transition:all .2s}
.rep-btn:hover{background:rgba(108,99,255,.1);border-color:rgba(108,99,255,.2);color:#c4b5fd}
.rep-btn.active{background:rgba(108,99,255,.15);border-color:rgba(108,99,255,.35);color:#a78bfa}
.rep-btn-icon{font-size:18px;flex-shrink:0}
.rep-main{display:flex;flex-direction:column;gap:12px}
.rep-filters{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.rep-filter-label{font-size:12px;color:#64748b;font-weight:600}
.rep-date-input{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:8px;padding:8px 12px;color:#e2e8f0;font-size:13px;font-family:Cairo,sans-serif;outline:none}
.rep-date-input:focus{border-color:#7c6dff}
.rep-actions{display:flex;gap:8px;margin-right:auto}
.btn-excel{background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);color:#4ade80;padding:8px 16px;border-radius:8px;cursor:pointer;font-family:Cairo,sans-serif;font-size:13px;font-weight:700}
.btn-print{background:rgba(124,109,255,.15);border:1px solid rgba(124,109,255,.3);color:#a78bfa;padding:8px 16px;border-radius:8px;cursor:pointer;font-family:Cairo,sans-serif;font-size:13px;font-weight:700}
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.kpi{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:14px}
.kpi-l{font-size:11px;opacity:.6;margin-bottom:5px}
.kpi-v{font-size:20px;font-weight:800;color:#e2e8f0}
.rep-table{width:100%;border-collapse:collapse;font-size:13px}
.rep-table th{text-align:right;padding:9px 10px;font-weight:700;opacity:.6;border-bottom:1px solid rgba(255,255,255,.1);font-size:12px}
.rep-table td{padding:9px 10px;border-bottom:1px solid rgba(255,255,255,.05)}
.rep-table tr:hover td{background:rgba(255,255,255,.03)}
.badge{padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600}
.empty-state{text-align:center;padding:60px;opacity:.4;font-size:15px}
.profit-positive{color:#4ade80;font-weight:700}
.profit-negative{color:#f87171;font-weight:700}
.progress-bar{height:6px;background:rgba(255,255,255,.1);border-radius:3px;overflow:hidden;margin-top:4px}
.progress-fill{height:100%;background:linear-gradient(90deg,#7c6dff,#a78bfa);border-radius:3px}
</style>

<div class="rep-wrap" id="rep-content">
    {{-- Sidebar --}}
    <div class="rep-sidebar">
        <div class="rep-card">
            <div style="font-size:12px;color:#475569;font-weight:700;margin-bottom:10px;letter-spacing:.5px">التقارير المتاحة</div>
            <button wire:click="loadSalesReport" class="rep-btn {{ $activeReport === 'sales' ? 'active' : '' }}"><span class="rep-btn-icon">🧾</span><span>تقرير المبيعات</span></button>
            <button wire:click="loadProfitReport" class="rep-btn {{ $activeReport === 'profit' ? 'active' : '' }}"><span class="rep-btn-icon">📈</span><span>الأرباح والخسائر</span></button>
            <button wire:click="loadDebtsReport" class="rep-btn {{ $activeReport === 'debts' ? 'active' : '' }}"><span class="rep-btn-icon">💰</span><span>المديونيات</span></button>
            <button wire:click="loadStockReport" class="rep-btn {{ $activeReport === 'stock' ? 'active' : '' }}"><span class="rep-btn-icon">📦</span><span>تقرير المخزون</span></button>
            <button wire:click="loadTopProductsReport" class="rep-btn {{ $activeReport === 'top_products' ? 'active' : '' }}"><span class="rep-btn-icon">🏆</span><span>أكثر الأصناف مبيعاً</span></button>
            <button wire:click="loadAttendanceReport" class="rep-btn {{ $activeReport === 'attendance' ? 'active' : '' }}"><span class="rep-btn-icon">👥</span><span>الحضور والانصراف</span></button>
        </div>
    </div>

    {{-- Main --}}
    <div class="rep-main">
        {{-- Filters --}}
        <div class="rep-card">
            <div class="rep-filters">
                <div class="rep-filter-label">من:</div>
                <input type="date" wire:model="dateFrom" class="rep-date-input">
                <div class="rep-filter-label">إلى:</div>
                <input type="date" wire:model="dateTo" class="rep-date-input">
                @if($activeReport)
                <div class="rep-actions">
                    <button wire:click="exportExcel" class="btn-excel">📊 Excel</button>
                    <button onclick="printReport()" class="btn-print">🖨️ طباعة</button>
                </div>
                @endif
            </div>
        </div>

        @if(!$activeReport)
        <div class="rep-card"><div class="empty-state">📊 اختر تقريراً من القائمة على اليمين</div></div>

        @elseif($activeReport === 'sales')
        <div class="kpi-grid">
            <div class="kpi"><div class="kpi-l">إجمالي المبيعات</div><div class="kpi-v">{{ number_format($reportData['total_sales'], 0) }} ر.س</div></div>
            <div class="kpi"><div class="kpi-l">المحصّل</div><div class="kpi-v" style="color:#4ade80">{{ number_format($reportData['total_paid'], 0) }} ر.س</div></div>
            <div class="kpi"><div class="kpi-l">المديونيات</div><div class="kpi-v" style="color:#f87171">{{ number_format($reportData['total_remaining'], 0) }} ر.س</div></div>
            <div class="kpi"><div class="kpi-l">عدد الفواتير</div><div class="kpi-v" style="color:#a78bfa">{{ $reportData['count'] }}</div></div>
        </div>
        <div class="rep-card">
            <table class="rep-table">
                <thead><tr><th>رقم الفاتورة</th><th>العميل</th><th>التاريخ</th><th>طريقة الدفع</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th></tr></thead>
                <tbody>
                    @foreach($reportData['invoices'] as $inv)
                    @php
                        $statusMap = ['paid'=>['مدفوعة','color:#4ade80;background:rgba(74,222,128,.15)'],'confirmed'=>['مؤكدة','color:#60a5fa;background:rgba(96,165,250,.15)'],'partial'=>['جزئي','color:#fb923c;background:rgba(251,146,60,.15)'],'draft'=>['مسودة','color:#fbbf24;background:rgba(251,191,36,.15)'],'cancelled'=>['ملغية','color:#f87171;background:rgba(248,113,113,.15)']];
                        $s = $statusMap[$inv->status] ?? [$inv->status,''];
                        $payMap = ['cash'=>'نقدي','credit'=>'آجل','installment'=>'تقسيط','card'=>'بطاقة','wallet'=>'محفظة'];
                    @endphp
                    <tr>
                        <td style="color:#a78bfa;font-family:monospace">{{ $inv->invoice_number }}</td>
                        <td>{{ $inv->customer?->name ?? 'عميل نقدي' }}</td>
                        <td style="opacity:.7">{{ $inv->invoice_date->format('d/m/Y') }}</td>
                        <td>{{ $payMap[$inv->payment_type] ?? $inv->payment_type }}</td>
                        <td style="font-weight:700">{{ number_format($inv->total, 2) }} ر.س</td>
                        <td style="color:#4ade80">{{ number_format($inv->paid_amount, 2) }} ر.س</td>
                        <td style="color:{{ $inv->remaining_amount > 0 ? '#f87171' : '#4ade80' }}">{{ number_format($inv->remaining_amount, 2) }} ر.س</td>
                        <td><span class="badge" style="{{ $s[1] }}">{{ $s[0] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @elseif($activeReport === 'profit')
        <div class="kpi-grid">
            <div class="kpi"><div class="kpi-l">إجمالي الإيرادات</div><div class="kpi-v" style="color:#4ade80">{{ number_format($reportData['total_revenue'], 0) }} ر.س</div></div>
            <div class="kpi"><div class="kpi-l">إجمالي التكلفة</div><div class="kpi-v" style="color:#f87171">{{ number_format($reportData['total_cost'], 0) }} ر.س</div></div>
            <div class="kpi"><div class="kpi-l">صافي الربح</div><div class="kpi-v {{ $reportData['gross_profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">{{ number_format($reportData['gross_profit'], 0) }} ر.س</div></div>
            <div class="kpi"><div class="kpi-l">هامش الربح</div><div class="kpi-v" style="color:#a78bfa">{{ $reportData['profit_margin'] }}٪</div></div>
        </div>
        <div class="rep-card">
            <table class="rep-table">
                <thead><tr><th>الصنف</th><th>الكمية</th><th>الإيرادات</th><th>التكلفة</th><th>الربح</th><th>هامش الربح</th></tr></thead>
                <tbody>
                    @foreach($reportData['by_product'] as $p)
                    <tr>
                        <td style="font-weight:600">{{ $p['name'] }}</td>
                        <td>{{ number_format($p['qty'], 2) }}</td>
                        <td style="color:#4ade80">{{ number_format($p['revenue'], 2) }} ر.س</td>
                        <td style="color:#f87171">{{ number_format($p['cost'], 2) }} ر.س</td>
                        <td class="{{ $p['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">{{ number_format($p['profit'], 2) }} ر.س</td>
                        <td><div style="font-size:12px;color:#a78bfa">{{ $p['margin'] }}٪</div><div class="progress-bar"><div class="progress-fill" style="width:{{ min($p['margin'], 100) }}%"></div></div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @elseif($activeReport === 'debts')
        <div class="kpi-grid" style="grid-template-columns:repeat(2,1fr)">
            <div class="kpi"><div class="kpi-l">إجمالي المديونيات</div><div class="kpi-v" style="color:#f87171">{{ number_format($reportData['total_debt'], 0) }} ر.س</div></div>
            <div class="kpi"><div class="kpi-l">عدد العملاء المدينين</div><div class="kpi-v" style="color:#a78bfa">{{ $reportData['count'] }}</div></div>
        </div>
        <div class="rep-card">
            <table class="rep-table">
                <thead><tr><th>كود العميل</th><th>اسم العميل</th><th>التليفون</th><th>النوع</th><th>إجمالي الدين</th></tr></thead>
                <tbody>
                    @foreach($reportData['customers'] as $c)
                    <tr>
                        <td style="font-family:monospace;color:#a78bfa">{{ $c->code }}</td>
                        <td style="font-weight:600">{{ $c->name }}</td>
                        <td style="opacity:.7">{{ $c->phone ?? '-' }}</td>
                        <td><span class="badge" style="background:rgba(108,99,255,.15);color:#a78bfa">{{ $c->type === 'company' ? 'شركة' : 'فرد' }}</span></td>
                        <td style="color:#f87171;font-weight:800;font-size:15px">{{ number_format($c->total_debt, 2) }} ر.س</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @elseif($activeReport === 'stock')
        <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr)">
            <div class="kpi"><div class="kpi-l">إجمالي الأصناف</div><div class="kpi-v">{{ $reportData['count'] }}</div></div>
            <div class="kpi"><div class="kpi-l">أصناف ناقصة</div><div class="kpi-v" style="color:#f87171">{{ $reportData['low'] }}</div></div>
            <div class="kpi"><div class="kpi-l">أصناف كافية</div><div class="kpi-v" style="color:#4ade80">{{ $reportData['count'] - $reportData['low'] }}</div></div>
        </div>
        <div class="rep-card">
            <table class="rep-table">
                <thead><tr><th>الكود</th><th>الصنف</th><th>المجموعة</th><th>الكمية الحالية</th><th>الحد الأدنى</th><th>سعر البيع</th><th>الحالة</th></tr></thead>
                <tbody>
                    @foreach($reportData['products'] as $p)
                    @php $isLow = ($p->quantity ?? 0) <= $p->min_quantity; @endphp
                    <tr>
                        <td style="font-family:monospace;color:#a78bfa">{{ $p->code }}</td>
                        <td style="font-weight:600">{{ $p->name }}</td>
                        <td><span class="badge" style="background:rgba(108,99,255,.1);color:#a78bfa">{{ $p->category?->name ?? '-' }}</span></td>
                        <td style="font-weight:700;color:{{ $isLow ? '#f87171' : '#4ade80' }}">{{ number_format($p->quantity ?? 0, 2) }}</td>
                        <td style="opacity:.7">{{ $p->min_quantity }}</td>
                        <td style="color:#4ade80;font-weight:700">{{ number_format($p->sale_price, 2) }} ر.س</td>
                        <td><span class="badge" style="{{ $isLow ? 'background:rgba(239,68,68,.15);color:#f87171' : 'background:rgba(34,197,94,.15);color:#4ade80' }}">{{ $isLow ? '⚠️ ناقص' : '✅ كافي' }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @elseif($activeReport === 'top_products')
        <div class="kpi-grid" style="grid-template-columns:repeat(2,1fr)">
            <div class="kpi"><div class="kpi-l">إجمالي الإيرادات</div><div class="kpi-v" style="color:#4ade80">{{ number_format($reportData['total_revenue'], 0) }} ر.س</div></div>
            <div class="kpi"><div class="kpi-l">عدد الأصناف</div><div class="kpi-v" style="color:#a78bfa">{{ count($reportData['products']) }}</div></div>
        </div>
        <div class="rep-card">
            <table class="rep-table">
                <thead><tr><th>#</th><th>الصنف</th><th>الكود</th><th>الكمية المباعة</th><th>الإيرادات</th></tr></thead>
                <tbody>
                    @foreach($reportData['products'] as $i => $p)
                    <tr>
                        <td style="color:#{{ $i === 0 ? 'fbbf24' : ($i === 1 ? '94a3b8' : ($i === 2 ? 'fb923c' : '475569')) }};font-weight:800">{{ $i + 1 }}</td>
                        <td style="font-weight:600">{{ $p['name'] }}</td>
                        <td style="font-family:monospace;opacity:.7">{{ $p['code'] ?? '-' }}</td>
                        <td>{{ number_format($p['qty'], 2) }}</td>
                        <td style="color:#4ade80;font-weight:700">{{ number_format($p['total'], 2) }} ر.س</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @elseif($activeReport === 'attendance')
        <div class="rep-card">
            <table class="rep-table">
                <thead><tr><th>الموظف</th><th>القسم</th><th>حاضر</th><th>غائب</th><th>متأخر</th><th>إجازة</th><th>ساعات العمل</th><th>دقائق التأخير</th></tr></thead>
                <tbody>
                    @foreach($reportData['employees'] as $e)
                    <tr>
                        <td style="font-weight:600">{{ $e['name'] }}</td>
                        <td style="opacity:.7">{{ $e['department'] }}</td>
                        <td style="color:#4ade80;font-weight:700">{{ $e['present'] }}</td>
                        <td style="color:#f87171">{{ $e['absent'] }}</td>
                        <td style="color:#fbbf24">{{ $e['late'] }}</td>
                        <td style="color:#60a5fa">{{ $e['vacation'] }}</td>
                        <td style="color:#a78bfa">{{ number_format($e['total_hours'], 1) }} س</td>
                        <td style="color:{{ $e['late_minutes'] > 0 ? '#f87171' : '#4ade80' }}">{{ $e['late_minutes'] }} د</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<script>
function printReport() {
    var content = document.getElementById('rep-content').innerHTML;
    var win = window.open('', '_blank');
    win.document.write('<html dir="rtl"><head><title>تقرير</title>');
    win.document.write('<style>*{font-family:Arial,sans-serif}table{width:100%;border-collapse:collapse}th,td{padding:8px;border:1px solid #ddd;text-align:right}th{background:#f0f0f0;font-weight:700}.rep-sidebar{display:none}.rep-wrap{display:block}.rep-card{margin-bottom:16px}</style>');
    win.document.write('<\/head><body>');
    win.document.write(content);
    win.document.write('<\/body><\/html>');
    win.document.close();
    win.print();
}

document.addEventListener('livewire:initialized', function() {
    Livewire.on('export-excel', function(data) {
        window.location.href = '/reports/export/' + data.report
            + '?from=' + data.dateFrom
            + '&to=' + data.dateTo;
    });
});
</script>
</x-filament-panels::page>