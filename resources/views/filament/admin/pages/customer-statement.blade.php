<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{
    --ocs-bg:#08111f;
    --ocs-panel:#0d1728;
    --ocs-panel-2:#101c30;
    --ocs-border:#26354d;
    --ocs-border-soft:rgba(255,255,255,.07);
    --ocs-text:#f7f9fc;
    --ocs-muted:#8090a8;
    --ocs-purple:#8b5cf6;
    --ocs-green:#34d399;
    --ocs-red:#fb7185;
    --ocs-amber:#fbbf24;
    --ocs-blue:#60a5fa;
}
.statement-page{display:flex;flex-direction:column;gap:13px;padding-bottom:30px;direction:rtl;color:var(--ocs-text)}
.ocs-card{background:linear-gradient(180deg,var(--ocs-panel-2),var(--ocs-panel));border:1px solid var(--ocs-border);border-radius:15px;box-shadow:0 14px 40px rgba(0,0,0,.14)}
.ocs-hero{padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:18px}
.ocs-hero-main{display:flex;align-items:center;gap:13px}
.ocs-hero-mark{width:44px;height:44px;border:1px solid rgba(139,92,246,.45);border-radius:12px;background:linear-gradient(145deg,rgba(139,92,246,.22),rgba(139,92,246,.08));display:grid;place-items:center;color:#c4b5fd;font-size:12px;font-weight:950;letter-spacing:.8px}
.ocs-hero h1{margin:0;font-size:22px;font-weight:950;color:#fff}
.ocs-hero p{margin:4px 0 0;font-size:12px;color:var(--ocs-muted)}
.ocs-hero-chip{display:inline-flex;align-items:center;gap:7px;border:1px solid rgba(139,92,246,.3);background:rgba(139,92,246,.08);color:#b9a8ff;border-radius:999px;padding:7px 11px;font-size:10px;font-weight:900}
.ocs-hero-chip:before{content:"";width:6px;height:6px;border-radius:50%;background:var(--ocs-purple);box-shadow:0 0 0 3px rgba(139,92,246,.1)}
.ocs-filters{padding:15px 16px}
.ocs-filter-grid{display:grid;grid-template-columns:minmax(280px,1.6fr) 190px 190px auto auto;gap:10px;align-items:end}
.ocs-field label{display:block;font-size:11px;color:#91a0b8;font-weight:800;margin:0 0 6px}
.ocs-field select,.ocs-field input{width:100%;height:44px;background:#0a1424;border:1px solid #2a3a55;border-radius:9px;padding:0 12px;color:#edf2f8;font-size:13px;outline:none}
.ocs-field select:focus,.ocs-field input:focus{border-color:rgba(139,92,246,.75);box-shadow:0 0 0 3px rgba(139,92,246,.10)}
.ocs-btn{height:44px;border-radius:9px;padding:0 16px;font-size:12px;font-weight:900;cursor:pointer;white-space:nowrap}
.ocs-btn-main{border:1px solid rgba(139,92,246,.48);background:linear-gradient(135deg,#8b5cf6,#6d4aff);color:#fff;box-shadow:0 8px 22px rgba(109,74,255,.18)}
.ocs-btn-pdf{border:1px solid rgba(52,211,153,.28);background:rgba(52,211,153,.08);color:#69e5b5}
.ocs-profile{padding:15px 16px}
.ocs-section-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:11px}
.ocs-section-title{font-size:14px;font-weight:950;color:#f7f9fc}
.ocs-section-sub{font-size:10px;color:#65758d}
.ocs-profile-grid{display:grid;grid-template-columns:1.45fr repeat(3,1fr);gap:9px}
.ocs-info{min-height:70px;background:#0a1423;border:1px solid #1f2d43;border-radius:10px;padding:12px 14px}
.ocs-info.primary{background:linear-gradient(135deg,rgba(139,92,246,.11),#0a1423);border-color:rgba(139,92,246,.34)}
.ocs-info-label{font-size:10px;font-weight:750;color:#667993;margin-bottom:6px}
.ocs-info-value{font-size:14px;font-weight:950;color:#f5f7fb;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ocs-info.primary .ocs-info-value{color:#c4b5fd;font-size:16px}
.ocs-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:9px}
.ocs-kpi{position:relative;overflow:hidden;min-height:104px;border:1px solid var(--ocs-border);border-radius:13px;padding:14px 15px;background:linear-gradient(180deg,#111e33,#0d1829)}
.ocs-kpi:after{content:"";position:absolute;right:-34px;bottom:-48px;width:105px;height:105px;border:18px solid rgba(255,255,255,.025);border-radius:50%}
.ocs-kpi-top{display:flex;justify-content:space-between;align-items:center}
.ocs-kpi-label{font-size:11px;color:#9aa8bb;font-weight:850}
.ocs-code{font-size:8px;font-weight:900;letter-spacing:.5px;padding:4px 6px;border:1px solid #2a3b57;border-radius:999px;color:#637793}
.ocs-kpi-value{margin-top:11px;font-size:22px;line-height:1;font-weight:950;direction:ltr;text-align:right}
.ocs-kpi-note{margin-top:6px;font-size:10px;color:#62748d}
.ocs-kpi.opening .ocs-kpi-value{color:var(--ocs-blue)}
.ocs-kpi.sales .ocs-kpi-value{color:var(--ocs-red)}
.ocs-kpi.payments .ocs-kpi-value{color:var(--ocs-green)}
.ocs-kpi.balance .ocs-kpi-value{color:var(--ocs-amber)}
.ocs-mini-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:9px}
.ocs-mini{background:#0b1525;border:1px solid #203049;border-radius:10px;padding:11px 13px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.ocs-mini span:first-child{font-size:10px;color:#6f829c;font-weight:800}
.ocs-mini span:last-child{font-size:13px;color:#e9eef7;font-weight:950;white-space:nowrap}
.ocs-mini.return span:last-child{color:#fbbf24}.ocs-mini.refund span:last-child{color:#93c5fd}.ocs-mini.net.positive span:last-child{color:#fb7185}.ocs-mini.net.negative span:last-child{color:#34d399}
.ocs-status-pill{display:inline-flex!important;align-items:center;gap:6px;padding:5px 8px;border-radius:999px;border:1px solid #2b3b54;background:#111d30;font-size:10px!important}
.ocs-status-pill:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
.ocs-table-card{overflow:hidden}
.ocs-table-head{padding:14px 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #213149}
.ocs-table-title{font-size:14px;font-weight:950;color:#f5f7fb}
.ocs-table-meta{font-size:10px;color:#667a95}
.ocs-table-scroll{overflow:auto}
.ocs-table{width:100%;border-collapse:collapse;min-width:980px;font-size:12px}
.ocs-table th{padding:11px 13px;background:#091321;border-bottom:1px solid #223149;text-align:right;color:#71839d;font-size:10px;font-weight:900;white-space:nowrap}
.ocs-table td{padding:12px 13px;border-bottom:1px solid rgba(35,49,72,.72);color:#d7dfeb}
.ocs-table tbody tr:hover td{background:rgba(139,92,246,.035)}
.ocs-table tr.opening td{background:rgba(96,165,250,.035)}
.ocs-table tr.sale td{background:rgba(251,113,133,.018)}
.ocs-table tr.payment td{background:rgba(52,211,153,.018)}
.ocs-table tr.sale_return td{background:rgba(251,191,36,.025)}
.ocs-table tr.refund td{background:rgba(96,165,250,.018)}
.ocs-table tr.reversal td{background:rgba(196,181,253,.018)}
.ocs-date{white-space:nowrap;color:#72839b!important}
.ocs-type{display:inline-flex;padding:5px 8px;border-radius:999px;font-size:9px;font-weight:900;border:1px solid transparent;white-space:nowrap}
.ocs-type.opening{color:#93c5fd;background:rgba(96,165,250,.08);border-color:rgba(96,165,250,.18)}
.ocs-type.sale{color:#fda4af;background:rgba(251,113,133,.08);border-color:rgba(251,113,133,.18)}
.ocs-type.payment{color:#6ee7b7;background:rgba(52,211,153,.08);border-color:rgba(52,211,153,.18)}
.ocs-type.sale_return{color:#fde68a;background:rgba(251,191,36,.08);border-color:rgba(251,191,36,.18)}
.ocs-type.refund{color:#93c5fd;background:rgba(96,165,250,.08);border-color:rgba(96,165,250,.18)}
.ocs-type.reversal{color:#c4b5fd;background:rgba(139,92,246,.08);border-color:rgba(139,92,246,.18)}
.ocs-desc{font-weight:800;color:#edf2f8!important}
.ocs-status{display:inline-flex;margin-right:6px;padding:3px 6px;border-radius:5px;font-size:8px;font-weight:900}
.ocs-ref{font-family:ui-monospace,SFMono-Regular,Menlo,monospace!important;font-size:11px}
.ocs-ref a{color:#a78bfa;text-decoration:none;font-weight:850}
.ocs-ref a:hover{text-decoration:underline}
.ocs-money{font-weight:950;white-space:nowrap;direction:ltr;text-align:right}
.ocs-debit{color:#fb7185!important}.ocs-credit{color:#34d399!important}
.ocs-balance{font-weight:950!important;white-space:nowrap;direction:ltr;text-align:right}
.ocs-table tfoot td{background:rgba(139,92,246,.06);border-top:2px solid rgba(139,92,246,.22);font-weight:950}
.ocs-empty{padding:58px 20px;text-align:center}
.ocs-empty-mark{width:50px;height:50px;border:1px solid #26354c;border-radius:13px;background:#0b1525;display:grid;place-items:center;margin:0 auto 11px;color:#71839b;font-size:11px;font-weight:950;letter-spacing:.7px}
.ocs-empty-title{font-size:14px;color:#a7b3c5;font-weight:900}
.ocs-empty-sub{font-size:11px;color:#586a83;margin-top:5px}
@media(max-width:1100px){
    .ocs-filter-grid{grid-template-columns:1fr 1fr}
    .ocs-profile-grid,.ocs-kpis,.ocs-mini-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:650px){
    .ocs-hero{align-items:flex-start;flex-direction:column}
    .ocs-filter-grid,.ocs-profile-grid,.ocs-kpis,.ocs-mini-grid{grid-template-columns:1fr}
    .ocs-hero h1{font-size:19px}
}
</style>

<div class="statement-page">
    <div class="ocs-card ocs-hero">
        <div class="ocs-hero-main">
            <div class="ocs-hero-mark">AR</div>
            <div>
                <h1>كشف حساب العميل</h1>
                <p>متابعة المديونية والمبيعات والتحصيلات والمرتجعات وردود العملاء من القيود المرحلة</p>
            </div>
        </div>
        <div class="ocs-hero-chip">ORION ERP · CUSTOMER LEDGER</div>
    </div>

    <div class="ocs-card ocs-filters">
        <div class="ocs-filter-grid">
            <div class="ocs-field">
                <label>العميل</label>
                <select wire:model="customerId">
                    <option value="">اختر عميلاً...</option>
                    @foreach(\App\Models\Customer::where('is_active', true)->orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ocs-field">
                <label>من تاريخ</label>
                <input type="date" wire:model="dateFrom">
            </div>
            <div class="ocs-field">
                <label>إلى تاريخ</label>
                <input type="date" wire:model="dateTo">
            </div>
            <button type="button" wire:click="loadStatement" class="ocs-btn ocs-btn-main">عرض الكشف</button>
            @if($customer)
                <button type="button" wire:click="exportPdf" class="ocs-btn ocs-btn-pdf">تصدير PDF</button>
            @endif
        </div>
    </div>

    @if($customer)
        <div class="ocs-card ocs-profile">
            <div class="ocs-section-head">
                <div class="ocs-section-title">بيانات العميل</div>
                <div class="ocs-section-sub">CUSTOMER PROFILE</div>
            </div>
            <div class="ocs-profile-grid">
                <div class="ocs-info primary">
                    <div class="ocs-info-label">اسم العميل</div>
                    <div class="ocs-info-value">{{ $customer->name }}</div>
                </div>
                <div class="ocs-info">
                    <div class="ocs-info-label">الهاتف</div>
                    <div class="ocs-info-value">{{ $customer->phone ?: '-' }}</div>
                </div>
                <div class="ocs-info">
                    <div class="ocs-info-label">نوع العميل</div>
                    <div class="ocs-info-value">{{ $customer->type === 'company' ? 'شركة' : 'فرد' }}</div>
                </div>
                <div class="ocs-info">
                    <div class="ocs-info-label">الرقم الضريبي</div>
                    <div class="ocs-info-value">{{ $customer->tax_number ?: '-' }}</div>
                </div>
            </div>
        </div>

        <div class="ocs-kpis">
            <div class="ocs-kpi opening">
                <div class="ocs-kpi-top"><span class="ocs-kpi-label">رصيد أول المدة</span><span class="ocs-code">OPEN</span></div>
                <div class="ocs-kpi-value">{{ number_format(abs($openingBalance), 2) }} ر.س</div>
                <div class="ocs-kpi-note">
                    {{ $openingBalance > 0 ? 'مستحق من العميل' : ($openingBalance < 0 ? 'رصيد لصالح العميل' : 'لا يوجد رصيد افتتاحي') }}
                </div>
            </div>

            <div class="ocs-kpi sales">
                <div class="ocs-kpi-top"><span class="ocs-kpi-label">إجمالي المبيعات</span><span class="ocs-code">SALE</span></div>
                <div class="ocs-kpi-value">{{ number_format($totalSales, 2) }} ر.س</div>
                <div class="ocs-kpi-note">فواتير البيع المؤثرة خلال الفترة</div>
            </div>

            <div class="ocs-kpi payments">
                <div class="ocs-kpi-top"><span class="ocs-kpi-label">إجمالي التحصيلات</span><span class="ocs-code">PAID</span></div>
                <div class="ocs-kpi-value">{{ number_format($totalPayments, 2) }} ر.س</div>
                <div class="ocs-kpi-note">المبالغ المحصلة على فواتير البيع</div>
            </div>

            <div class="ocs-kpi balance">
                <div class="ocs-kpi-top"><span class="ocs-kpi-label">الرصيد الحالي</span><span class="ocs-code">BAL</span></div>
                <div class="ocs-kpi-value">{{ number_format(abs($closingBalance), 2) }} ر.س</div>
                <div class="ocs-kpi-note">
                    {{ $closingBalance > 0 ? 'مستحق من العميل' : ($closingBalance < 0 ? 'رصيد لصالح العميل' : 'الحساب مسدد') }}
                </div>
            </div>
        </div>

        <div class="ocs-mini-grid">
            <div class="ocs-mini return">
                <span>مرتجعات البيع</span>
                <span>{{ number_format($totalReturns, 2) }} ر.س</span>
            </div>
            <div class="ocs-mini refund">
                <span>ردود مبالغ للعملاء</span>
                <span>{{ number_format($totalCustomerRefunds, 2) }} ر.س</span>
            </div>
            <div class="ocs-mini">
                <span>عدد الحركات</span>
                <span>{{ $transactionCount }}</span>
            </div>
            <div class="ocs-mini net {{ $netMovement > 0 ? 'positive' : ($netMovement < 0 ? 'negative' : '') }}">
                <span>صافي حركة الفترة</span>
                <span>{{ $netMovement > 0 ? '+' : '' }}{{ number_format($netMovement, 2) }} ر.س</span>
            </div>
            <div class="ocs-mini">
                <span>حالة الحساب</span>
                <span class="ocs-status-pill" style="color:{{ $closingBalance > 0 ? '#fbbf24' : ($closingBalance < 0 ? '#60a5fa' : '#34d399') }}">
                    {{ $closingBalance > 0 ? 'مستحق' : ($closingBalance < 0 ? 'رصيد عميل' : 'مسدد') }}
                </span>
            </div>
        </div>

        <div class="ocs-card ocs-table-card">
            <div class="ocs-table-head">
                <div class="ocs-table-title">حركات حساب العميل</div>
                <div class="ocs-table-meta">{{ $dateFrom }} → {{ $dateTo }} · {{ count($statement) }} سطر</div>
            </div>

            @if(empty($statement))
                <div class="ocs-empty">
                    <div class="ocs-empty-mark">AR</div>
                    <div class="ocs-empty-title">لا توجد حركات في الفترة المحددة</div>
                    <div class="ocs-empty-sub">الحساب لا يحتوي قيود مبيعات أو تحصيلات أو مرتجعات أو ردود مؤثرة خلال هذا النطاق الزمني.</div>
                </div>
            @else
                <div class="ocs-table-scroll">
                    <table class="ocs-table">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>نوع الحركة</th>
                                <th>البيان</th>
                                <th>المرجع</th>
                                <th>مدين</th>
                                <th>دائن</th>
                                <th>الرصيد بعد الحركة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statement as $row)
                                @php
                                    $statusMap = [
                                        'paid' => ['مدفوعة', '#34d399', 'rgba(52,211,153,.08)'],
                                        'confirmed' => ['مؤكدة', '#60a5fa', 'rgba(96,165,250,.08)'],
                                        'partial' => ['جزئي', '#fbbf24', 'rgba(251,191,36,.08)'],
                                    ];
                                    $statusData = !empty($row['status']) ? ($statusMap[$row['status']] ?? [$row['status'], '#94a3b8', 'rgba(148,163,184,.08)']) : null;
                                @endphp
                                <tr class="{{ $row['type'] }}">
                                    <td class="ocs-date">{{ $row['date'] }}</td>
                                    <td><span class="ocs-type {{ $row['type'] }}">{{ $row['type_label'] }}</span></td>
                                    <td class="ocs-desc">
                                        {{ $row['description'] }}
                                        @if($statusData)
                                            <span class="ocs-status" style="color:{{ $statusData[1] }};background:{{ $statusData[2] }}">{{ $statusData[0] }}</span>
                                        @endif
                                    </td>
                                    <td class="ocs-ref">
                                        @if(!empty($row['invoice_id']))
                                            <a href="{{ \App\Filament\Admin\Resources\Invoices\InvoiceResource::getUrl('view', ['record' => $row['invoice_id']]) }}">
                                                {{ $row['reference'] }}
                                            </a>
                                        @else
                                            <span style="color:#65758d">{{ $row['reference'] }}</span>
                                        @endif
                                    </td>
                                    <td class="ocs-money ocs-debit">{{ $row['debit'] > 0 ? number_format($row['debit'], 2).' ر.س' : '-' }}</td>
                                    <td class="ocs-money ocs-credit">{{ $row['credit'] > 0 ? number_format($row['credit'], 2).' ر.س' : '-' }}</td>
                                    <td class="ocs-balance" style="color:{{ $row['balance'] > 0 ? '#fbbf24' : ($row['balance'] < 0 ? '#60a5fa' : '#34d399') }}">
                                        {{ number_format(abs($row['balance']), 2) }} ر.س
                                        <small style="font-size:8px;opacity:.65;margin-right:4px">
                                            {{ $row['balance'] > 0 ? 'مستحق' : ($row['balance'] < 0 ? 'لصالح العميل' : 'مسدد') }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">إجمالي حركات الفترة</td>
                                <td class="ocs-money ocs-debit">{{ number_format($totalDebit, 2) }} ر.س</td>
                                <td class="ocs-money ocs-credit">{{ number_format($totalCredit, 2) }} ر.س</td>
                                <td class="ocs-balance" style="color:{{ $closingBalance > 0 ? '#fbbf24' : ($closingBalance < 0 ? '#60a5fa' : '#34d399') }}">
                                    {{ number_format(abs($closingBalance), 2) }} ر.س
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
</x-filament-panels::page>