<x-filament-panels::page>
@php
    $summary = $this->getUiSummary();
    $quick = request()->query('quick');

    $quickUrl = static fn (?string $value) => $value === null
        ? \App\Filament\Admin\Resources\AccountingReversals\AccountingReversalResource::getUrl('index')
        : \App\Filament\Admin\Resources\AccountingReversals\AccountingReversalResource::getUrl(
            'index',
            ['quick' => $value]
        );
@endphp

<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}

.orion-rev-page{
    direction:rtl;
    --or-bg:#08111f;
    --or-panel:#0d1728;
    --or-panel2:#111b2e;
    --or-border:#26344c;
    --or-soft:#1c2940;
    --or-text:#f8fafc;
    --or-muted:#8ea0b8;
    --or-muted2:#5f7189;
    --or-purple:#8b5cf6;
    --or-purple2:#a78bfa;
    --or-green:#34d399;
    --or-red:#f87171;
    --or-blue:#60a5fa;
    --or-amber:#fbbf24;
    color:var(--or-text);
}

/* Header */
.or-rev-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:20px;
    margin-bottom:22px;
}
.or-rev-kicker{
    font-size:10px;
    font-weight:950;
    letter-spacing:.9px;
    color:#fb8b9a;
    margin-bottom:7px;
}
.or-rev-title{
    margin:0;
    font-size:30px;
    line-height:1.1;
    color:var(--or-text);
    font-weight:950;
}
.or-rev-sub{
    margin-top:8px;
    max-width:820px;
    font-size:12px;
    line-height:1.8;
    color:var(--or-muted);
}
.or-rev-meta{
    display:flex;
    align-items:center;
    gap:8px;
    margin-top:11px;
    flex-wrap:wrap;
}
.or-rev-chip-safe{
    display:inline-flex;
    align-items:center;
    gap:7px;
    height:29px;
    padding:0 10px;
    border-radius:999px;
    border:1px solid rgba(248,113,113,.20);
    background:rgba(248,113,113,.07);
    color:#ffadb6;
    font-size:10px;
    font-weight:850;
}
.or-rev-dot{
    width:7px;
    height:7px;
    border-radius:50%;
    background:var(--or-red);
    box-shadow:0 0 0 4px rgba(248,113,113,.10);
}
.or-rev-readonly{
    display:flex;
    flex-direction:column;
    align-items:flex-end;
    gap:4px;
    min-width:180px;
    padding:12px 14px;
    border:1px solid var(--or-border);
    border-radius:12px;
    background:linear-gradient(145deg,var(--or-panel),#0a1422);
}
.or-rev-readonly strong{
    font-size:12px;
    color:#dce6f4;
}
.or-rev-readonly span{
    font-size:10px;
    color:var(--or-muted2);
}

/* KPIs */
.or-rev-kpis{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:13px;
    margin-bottom:15px;
}
.or-rev-kpi{
    position:relative;
    overflow:hidden;
    min-height:116px;
    padding:16px 17px;
    border-radius:15px;
    border:1px solid var(--or-border);
    background:linear-gradient(145deg,var(--or-panel2),#0b1525);
    box-shadow:0 14px 30px rgba(0,0,0,.11);
}
.or-rev-kpi::before{
    content:'';
    position:absolute;
    inset-block:17px;
    right:0;
    width:3px;
    background:linear-gradient(180deg,#fb7185,#dc2626);
}
.or-rev-kpi.purple::before{background:linear-gradient(180deg,#a78bfa,#7c3aed)}
.or-rev-kpi.green::before{background:linear-gradient(180deg,#5ee0ad,#16a36f)}
.or-rev-kpi.blue::before{background:linear-gradient(180deg,#7dd3fc,#3b82f6)}
.or-rev-kpi-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
}
.or-rev-kpi-label{
    color:#b6c1d2;
    font-size:11px;
    font-weight:850;
}
.or-rev-code{
    padding:4px 8px;
    border:1px solid #22324a;
    border-radius:999px;
    background:rgba(255,255,255,.025);
    color:#61738c;
    font-size:9px;
    font-weight:950;
}
.or-rev-value{
    margin-top:12px;
    font-size:26px;
    line-height:1;
    color:#fb8290;
    font-weight:950;
}
.or-rev-kpi.purple .or-rev-value{color:var(--or-purple2)}
.or-rev-kpi.green .or-rev-value{color:var(--or-green)}
.or-rev-kpi.blue .or-rev-value{color:#7dd3fc}
.or-rev-note{
    margin-top:7px;
    color:var(--or-muted2);
    font-size:10px;
}

/* Filters */
.or-rev-filterbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:14px;
    padding:12px;
    border:1px solid var(--or-soft);
    border-radius:13px;
    background:linear-gradient(145deg,var(--or-panel),#0b1422);
}
.or-rev-filter-label{
    font-size:11px;
    font-weight:900;
    color:#cbd5e1;
}
.or-rev-filters{
    display:flex;
    gap:7px;
    flex-wrap:wrap;
}
.or-rev-filter{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:32px;
    padding:0 11px;
    border-radius:9px;
    border:1px solid #2a3852;
    background:#0c1626;
    color:#95a5bb!important;
    text-decoration:none!important;
    font-size:10px;
    font-weight:850;
    transition:.15s ease;
}
.or-rev-filter:hover{
    color:#ffd0d5!important;
    border-color:#9d5360;
    background:rgba(248,113,113,.07);
}
.or-rev-filter.active{
    color:#ffe5e8!important;
    border-color:rgba(248,113,113,.48);
    background:linear-gradient(135deg,rgba(248,113,113,.16),rgba(190,24,93,.10));
}

/* Explanation strip */
.or-rev-policy{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:10px;
    margin-bottom:14px;
}
.or-rev-policy-card{
    padding:12px 13px;
    border-radius:11px;
    border:1px solid var(--or-soft);
    background:#0c1626;
}
.or-rev-policy-card b{
    display:block;
    margin-bottom:5px;
    color:#dce5f2;
    font-size:10px;
}
.or-rev-policy-card span{
    color:var(--or-muted2);
    font-size:9px;
    line-height:1.7;
}

/* Table */
.or-rev-shell{
    padding:0;
    border:1px solid var(--or-border);
    border-radius:15px;
    overflow:hidden;
    background:linear-gradient(145deg,var(--or-panel),#0a1422);
    box-shadow:0 14px 34px rgba(0,0,0,.13);
}
.or-rev-shell-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:15px 17px;
    border-bottom:1px solid var(--or-soft);
}
.or-rev-shell-title{
    font-size:16px;
    font-weight:950;
    color:var(--or-text);
}
.or-rev-shell-sub{
    margin-top:4px;
    font-size:10px;
    color:var(--or-muted2);
}
.or-rev-audit{
    display:inline-flex;
    align-items:center;
    gap:7px;
    height:26px;
    padding:0 9px;
    border-radius:999px;
    border:1px solid rgba(96,165,250,.22);
    background:rgba(96,165,250,.07);
    color:#99c7ff;
    font-size:9px;
    font-weight:900;
}

.or-rev-table .fi-ta,
.or-rev-table .fi-ta-ctn{
    background:transparent!important;
    box-shadow:none!important;
    border:0!important;
    border-radius:0!important;
}
.or-rev-table .fi-ta-header,
.or-rev-table .fi-ta-table thead tr{
    background:#0b1525!important;
}
.or-rev-table .fi-ta-header{
    border-bottom:1px solid var(--or-soft)!important;
}
.or-rev-table .fi-ta-header-toolbar{
    padding:11px 14px!important;
}
.or-rev-table .fi-input-wrp,
.or-rev-table .fi-ta-search-field .fi-input-wrp{
    background:#0a1423!important;
    border:1px solid #2a3954!important;
    box-shadow:none!important;
    border-radius:9px!important;
}
.or-rev-table .fi-input{color:#dbe5f3!important}
.or-rev-table .fi-ta-header-cell{
    background:#0b1525!important;
    border-color:var(--or-soft)!important;
    color:#71829c!important;
    font-size:10px!important;
    font-weight:900!important;
}
.or-rev-table .fi-ta-row{
    background:#0f192b!important;
    border-color:var(--or-soft)!important;
    transition:.15s ease;
}
.or-rev-table .fi-ta-row:nth-child(even){background:#101b2e!important}
.or-rev-table .fi-ta-row:hover{background:#14213a!important}
.or-rev-table .fi-ta-cell{
    border-color:var(--or-soft)!important;
    color:#dce5f2!important;
}
.or-rev-table .fi-ta-text-item-label{
    color:#dce5f2!important;
    font-size:11px!important;
}
.or-rev-table .fi-ta-footer{
    background:#0b1525!important;
    border-top:1px solid var(--or-soft)!important;
}

@media(max-width:1100px){
    .or-rev-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:820px){
    .or-rev-policy{grid-template-columns:1fr}
}
@media(max-width:760px){
    .or-rev-head{flex-direction:column}
    .or-rev-readonly{width:100%;align-items:flex-start}
    .or-rev-kpis{grid-template-columns:1fr}
    .or-rev-filterbar{align-items:flex-start}
    .or-rev-filters{width:100%}
    .or-rev-filter{flex:1;min-width:110px}
    .or-rev-title{font-size:25px}
}
</style>

<div class="orion-rev-page" lang="ar-SA">

    <div class="or-rev-head">
        <div>
            <div class="or-rev-kicker">REVERSAL CONTROL · AUDIT TRAIL</div>
            <h1 class="or-rev-title">القيود العكسية</h1>

            <div class="or-rev-sub">
                سجل رقابي مستقل لكل عملية عكس تمت على سند قبض أو سند صرف أو تحويل مالي،
                مع ربط المستند الأصلي بالقيد الأصلي والقيد العكسي دون حذف التاريخ المحاسبي.
            </div>

            <div class="or-rev-meta">
                <span class="or-rev-chip-safe">
                    <span class="or-rev-dot"></span>
                    Audit Trail محفوظ · لا حذف للمستند الأصلي
                </span>
            </div>
        </div>

        <div class="or-rev-readonly">
            <strong>سجل للقراءة والمراجعة</strong>
            <span>يتم إنشاء العكس من المستند المالي الأصلي</span>
        </div>
    </div>

    <div class="or-rev-kpis">
        <div class="or-rev-kpi">
            <div class="or-rev-kpi-top">
                <span class="or-rev-kpi-label">إجمالي عمليات العكس</span>
                <span class="or-rev-code">TOTAL</span>
            </div>
            <div class="or-rev-value">{{ number_format($summary['total']) }}</div>
            <div class="or-rev-note">كل عمليات العكس المسجلة</div>
        </div>

        <div class="or-rev-kpi blue">
            <div class="or-rev-kpi-top">
                <span class="or-rev-kpi-label">عمليات اليوم</span>
                <span class="or-rev-code">TODAY</span>
            </div>
            <div class="or-rev-value">{{ number_format($summary['today']) }}</div>
            <div class="or-rev-note">عمليات عكس بتاريخ اليوم</div>
        </div>

        <div class="or-rev-kpi green">
            <div class="or-rev-kpi-top">
                <span class="or-rev-kpi-label">عكس السندات</span>
                <span class="or-rev-code">VOUCHER</span>
            </div>
            <div class="or-rev-value">
                {{ number_format($summary['receipts'] + $summary['payments']) }}
            </div>
            <div class="or-rev-note">
                قبض {{ $summary['receipts'] }} · صرف {{ $summary['payments'] }}
            </div>
        </div>

        <div class="or-rev-kpi purple">
            <div class="or-rev-kpi-top">
                <span class="or-rev-kpi-label">عكس التحويلات</span>
                <span class="or-rev-code">TRANSFER</span>
            </div>
            <div class="or-rev-value">{{ number_format($summary['transfers']) }}</div>
            <div class="or-rev-note">تحويلات مالية تم عكسها</div>
        </div>
    </div>

    <div class="or-rev-filterbar">
        <div class="or-rev-filter-label">عرض سريع</div>

        <div class="or-rev-filters">
            <a class="or-rev-filter {{ !$quick ? 'active' : '' }}" href="{{ $quickUrl(null) }}">الكل</a>
            <a class="or-rev-filter {{ $quick === 'today' ? 'active' : '' }}" href="{{ $quickUrl('today') }}">اليوم</a>
            <a class="or-rev-filter {{ $quick === 'receipt' ? 'active' : '' }}" href="{{ $quickUrl('receipt') }}">سند قبض</a>
            <a class="or-rev-filter {{ $quick === 'payment' ? 'active' : '' }}" href="{{ $quickUrl('payment') }}">سند صرف</a>
            <a class="or-rev-filter {{ $quick === 'transfer' ? 'active' : '' }}" href="{{ $quickUrl('transfer') }}">تحويل مالي</a>
        </div>
    </div>

    <div class="or-rev-policy">
        <div class="or-rev-policy-card">
            <b>المستند الأصلي محفوظ</b>
            <span>العكس لا يحذف سند القبض أو الصرف أو التحويل المالي من السجل.</span>
        </div>
        <div class="or-rev-policy-card">
            <b>قيد عكسي مستقل</b>
            <span>يظل القيد الأصلي ظاهرًا، ويتم ربطه بقيد عكسي منفصل للمراجعة.</span>
        </div>
        <div class="or-rev-policy-card">
            <b>Traceability كاملة</b>
            <span>رقم العكس والمستند والسبب والمستخدم والقيود تظل مترابطة في سجل واحد.</span>
        </div>
    </div>

    <div class="or-rev-shell">
        <div class="or-rev-shell-head">
            <div>
                <div class="or-rev-shell-title">سجل عمليات العكس</div>
                <div class="or-rev-shell-sub">
                    اضغط على المستند الأصلي أو أحد أرقام القيود للانتقال إلى تفاصيله.
                </div>
            </div>

            <span class="or-rev-audit">
                AUDIT LOG
            </span>
        </div>

        <div class="or-rev-table">
            {{ $this->table }}
        </div>
    </div>

</div>
</x-filament-panels::page>
