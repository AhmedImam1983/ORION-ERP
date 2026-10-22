<x-filament-panels::page>
@php
    $summary = $this->getUiSummary();
    $quick = request()->query('quick');

    $quickUrl = static function (?string $value): string {
        if ($value === null) {
            return \App\Filament\Admin\Resources\FinancialTransfers\FinancialTransferResource::getUrl('index');
        }

        return \App\Filament\Admin\Resources\FinancialTransfers\FinancialTransferResource::getUrl(
            'index',
            ['quick' => $value]
        );
    };
@endphp

<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}

.orion-ft-page{
    direction:rtl;
    --orion-bg:#08111f;
    --orion-panel:#0d1728;
    --orion-panel-2:#111b2e;
    --orion-panel-3:#151f33;
    --orion-border:#26344c;
    --orion-border-soft:#1c2940;
    --orion-text:#f8fafc;
    --orion-muted:#8ea0b8;
    --orion-muted-2:#5f7189;
    --orion-purple:#8b5cf6;
    --orion-purple-2:#a78bfa;
    --orion-green:#34d399;
    --orion-red:#f87171;
    --orion-blue:#60a5fa;
    color:var(--orion-text);
}

/* ===== Header ===== */
.orion-ft-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:20px;
    margin-bottom:22px;
}
.orion-ft-kicker{
    font-size:10px;
    font-weight:950;
    letter-spacing:.9px;
    color:var(--orion-purple-2);
    text-transform:uppercase;
    margin-bottom:7px;
}
.orion-ft-title{
    margin:0;
    font-size:30px;
    line-height:1.1;
    color:var(--orion-text);
    font-weight:950;
}
.orion-ft-subtitle{
    margin-top:8px;
    font-size:12px;
    color:var(--orion-muted);
}
.orion-ft-meta{
    display:flex;
    align-items:center;
    gap:8px;
    margin-top:11px;
    flex-wrap:wrap;
}
.orion-ft-meta-chip{
    display:inline-flex;
    align-items:center;
    gap:7px;
    height:28px;
    padding:0 10px;
    border-radius:999px;
    border:1px solid rgba(52,211,153,.22);
    background:rgba(52,211,153,.08);
    color:#94e7c7;
    font-size:10px;
    font-weight:850;
}
.orion-ft-meta-dot{
    width:7px;height:7px;border-radius:50%;
    background:var(--orion-green);
    box-shadow:0 0 0 4px rgba(52,211,153,.10);
}
.orion-ft-create{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    min-height:44px;
    padding:0 18px;
    border-radius:11px;
    text-decoration:none!important;
    color:#fff!important;
    font-size:13px;
    font-weight:950;
    background:linear-gradient(135deg,#9b5cf6,#6d28d9);
    border:1px solid rgba(167,139,250,.3);
    box-shadow:0 9px 24px rgba(109,40,217,.24);
    transition:.18s ease;
}
.orion-ft-create:hover{
    transform:translateY(-1px);
    box-shadow:0 12px 28px rgba(109,40,217,.3);
}

/* ===== KPI ===== */
.orion-ft-kpis{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:13px;
    margin-bottom:15px;
}
.orion-ft-kpi{
    position:relative;
    overflow:hidden;
    min-height:116px;
    padding:16px 17px;
    border-radius:15px;
    border:1px solid var(--orion-border);
    background:linear-gradient(145deg,var(--orion-panel-2),#0b1525);
    box-shadow:0 14px 30px rgba(0,0,0,.11);
}
.orion-ft-kpi::before{
    content:'';
    position:absolute;
    inset-block:17px;
    right:0;
    width:3px;
    background:linear-gradient(180deg,#a78bfa,#7c3aed);
    border-radius:3px 0 0 3px;
}
.orion-ft-kpi.green::before{background:linear-gradient(180deg,#5ee0ad,#16a36f)}
.orion-ft-kpi.red::before{background:linear-gradient(180deg,#fb7185,#dc2626)}
.orion-ft-kpi.blue::before{background:linear-gradient(180deg,#7dd3fc,#3b82f6)}

.orion-ft-kpi-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
}
.orion-ft-kpi-label{
    color:#b6c1d2;
    font-size:11px;
    font-weight:850;
}
.orion-ft-kpi-code{
    padding:4px 8px;
    border:1px solid #22324a;
    border-radius:999px;
    background:rgba(255,255,255,.025);
    color:#61738c;
    font-size:9px;
    font-weight:950;
    letter-spacing:.6px;
}
.orion-ft-kpi-value{
    margin-top:12px;
    font-size:26px;
    line-height:1;
    color:var(--orion-purple-2);
    font-weight:950;
}
.orion-ft-kpi.green .orion-ft-kpi-value{color:var(--orion-green)}
.orion-ft-kpi.red .orion-ft-kpi-value{color:#fb7185}
.orion-ft-kpi.blue .orion-ft-kpi-value{color:#7dd3fc}
.orion-ft-kpi-note{
    margin-top:7px;
    color:var(--orion-muted-2);
    font-size:10px;
}

/* ===== Quick filters ===== */
.orion-ft-filterbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:14px;
    padding:12px;
    border:1px solid var(--orion-border-soft);
    border-radius:13px;
    background:linear-gradient(145deg,var(--orion-panel),#0b1422);
}
.orion-ft-filter-label{
    font-size:11px;
    font-weight:900;
    color:#cbd5e1;
}
.orion-ft-filters{
    display:flex;
    gap:7px;
    flex-wrap:wrap;
}
.orion-ft-chip{
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
.orion-ft-chip:hover{
    color:#d8ccff!important;
    border-color:#6d4bc8;
    background:rgba(139,92,246,.08);
}
.orion-ft-chip.active{
    color:#efe9ff!important;
    border-color:rgba(167,139,250,.55);
    background:linear-gradient(135deg,rgba(139,92,246,.24),rgba(109,40,217,.18));
    box-shadow:inset 0 0 0 1px rgba(167,139,250,.05);
}

/* ===== Table shell ===== */
.orion-ft-table-shell{
    padding:0;
    border:1px solid var(--orion-border);
    border-radius:15px;
    overflow:hidden;
    background:linear-gradient(145deg,var(--orion-panel),#0a1422);
    box-shadow:0 14px 34px rgba(0,0,0,.13);
}
.orion-ft-table-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:15px 17px;
    border-bottom:1px solid var(--orion-border-soft);
}
.orion-ft-table-title{
    font-size:16px;
    font-weight:950;
    color:var(--orion-text);
}
.orion-ft-table-sub{
    margin-top:4px;
    font-size:10px;
    color:var(--orion-muted-2);
}
.orion-ft-live{
    display:inline-flex;
    align-items:center;
    gap:7px;
    height:26px;
    padding:0 9px;
    border-radius:999px;
    border:1px solid rgba(52,211,153,.22);
    background:rgba(52,211,153,.08);
    color:#8fe4c1;
    font-size:9px;
    font-weight:900;
}

/* Filament table skin — presentation only */
.orion-ft-table .fi-ta,
.orion-ft-table .fi-ta-ctn{
    background:transparent!important;
    box-shadow:none!important;
    border:0!important;
    border-radius:0!important;
}
.orion-ft-table .fi-ta-header{
    background:#0b1525!important;
    border-bottom:1px solid var(--orion-border-soft)!important;
}
.orion-ft-table .fi-ta-header-toolbar{
    padding:11px 14px!important;
}
.orion-ft-table .fi-input-wrp,
.orion-ft-table .fi-ta-search-field .fi-input-wrp{
    background:#0a1423!important;
    border:1px solid #2a3954!important;
    box-shadow:none!important;
    border-radius:9px!important;
}
.orion-ft-table .fi-input{
    color:#dbe5f3!important;
}
.orion-ft-table .fi-ta-table thead tr{
    background:#0b1525!important;
}
.orion-ft-table .fi-ta-header-cell{
    background:#0b1525!important;
    border-color:var(--orion-border-soft)!important;
    color:#71829c!important;
    font-size:10px!important;
    font-weight:900!important;
}
.orion-ft-table .fi-ta-row{
    background:#0f192b!important;
    border-color:var(--orion-border-soft)!important;
    transition:.15s ease;
}
.orion-ft-table .fi-ta-row:nth-child(even){
    background:#101b2e!important;
}
.orion-ft-table .fi-ta-row:hover{
    background:#14213a!important;
}
.orion-ft-table .fi-ta-cell{
    border-color:var(--orion-border-soft)!important;
    color:#dce5f2!important;
}
.orion-ft-table .fi-ta-text-item-label{
    color:#dce5f2!important;
    font-size:11px!important;
}
.orion-ft-table .fi-ta-content-grid{
    background:transparent!important;
}
.orion-ft-table .fi-ta-footer{
    background:#0b1525!important;
    border-top:1px solid var(--orion-border-soft)!important;
}
.orion-ft-table .fi-pagination{
    color:#9aaac0!important;
}
.orion-ft-table .fi-link{
    font-weight:850;
}
.orion-ft-table .fi-btn{
    border-radius:8px!important;
}

/* Responsive */
@media(max-width:1100px){
    .orion-ft-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:760px){
    .orion-ft-head{flex-direction:column}
    .orion-ft-create{width:100%}
    .orion-ft-kpis{grid-template-columns:1fr}
    .orion-ft-filterbar{align-items:flex-start}
    .orion-ft-filters{width:100%}
    .orion-ft-chip{flex:1;min-width:110px}
    .orion-ft-title{font-size:25px}
}
</style>

<div class="orion-ft-page" lang="ar-SA">

    <div class="orion-ft-head">
        <div>
            <div class="orion-ft-kicker">TREASURY · BANK · TRANSFER CENTER</div>
            <h1 class="orion-ft-title">التحويلات المالية</h1>
            <div class="orion-ft-subtitle">
                مركز موحّد لمراجعة التحويلات بين الخزائن والبنوك مع ربط كل حركة بقيدها المحاسبي.
            </div>

            <div class="orion-ft-meta">
                <span class="orion-ft-meta-chip">
                    <span class="orion-ft-meta-dot"></span>
                    تشغيل محاسبي مباشر · SAR
                </span>
            </div>
        </div>

        @if(\App\Filament\Admin\Resources\FinancialTransfers\FinancialTransferResource::canCreate())
            <a class="orion-ft-create"
               href="{{ \App\Filament\Admin\Resources\FinancialTransfers\FinancialTransferResource::getUrl('create') }}">
                <span>+</span>
                <span>تحويل مالي جديد</span>
            </a>
        @endif
    </div>

    <div class="orion-ft-kpis">
        <div class="orion-ft-kpi">
            <div class="orion-ft-kpi-top">
                <div class="orion-ft-kpi-label">إجمالي التحويلات</div>
                <div class="orion-ft-kpi-code">TOTAL</div>
            </div>
            <div class="orion-ft-kpi-value">{{ number_format($summary['total_count']) }}</div>
            <div class="orion-ft-kpi-note">كل التحويلات المسجلة في النظام</div>
        </div>

        <div class="orion-ft-kpi blue">
            <div class="orion-ft-kpi-top">
                <div class="orion-ft-kpi-label">تحويلات اليوم</div>
                <div class="orion-ft-kpi-code">TODAY</div>
            </div>
            <div class="orion-ft-kpi-value">{{ number_format($summary['today_count']) }}</div>
            <div class="orion-ft-kpi-note">
                بقيمة {{ number_format($summary['today_amount'], 2) }} ر.س
            </div>
        </div>

        <div class="orion-ft-kpi green">
            <div class="orion-ft-kpi-top">
                <div class="orion-ft-kpi-label">التحويلات المرحلة</div>
                <div class="orion-ft-kpi-code">POST</div>
            </div>
            <div class="orion-ft-kpi-value">{{ number_format($summary['posted_count']) }}</div>
            <div class="orion-ft-kpi-note">
                قيمة مرحّلة {{ number_format($summary['posted_amount'], 2) }} ر.س
            </div>
        </div>

        <div class="orion-ft-kpi red">
            <div class="orion-ft-kpi-top">
                <div class="orion-ft-kpi-label">التحويلات المعكوسة</div>
                <div class="orion-ft-kpi-code">REV</div>
            </div>
            <div class="orion-ft-kpi-value">{{ number_format($summary['reversed_count']) }}</div>
            <div class="orion-ft-kpi-note">تحويلات تم عكسها بقيد مستقل</div>
        </div>
    </div>

    <div class="orion-ft-filterbar">
        <div class="orion-ft-filter-label">عرض سريع</div>

        <div class="orion-ft-filters">
            <a class="orion-ft-chip {{ !$quick ? 'active' : '' }}" href="{{ $quickUrl(null) }}">الكل</a>
            <a class="orion-ft-chip {{ $quick === 'today' ? 'active' : '' }}" href="{{ $quickUrl('today') }}">اليوم</a>
            <a class="orion-ft-chip {{ $quick === 'posted' ? 'active' : '' }}" href="{{ $quickUrl('posted') }}">مرحّل</a>
            <a class="orion-ft-chip {{ $quick === 'reversed' ? 'active' : '' }}" href="{{ $quickUrl('reversed') }}">معكوس</a>
            <a class="orion-ft-chip {{ $quick === 'treasury_treasury' ? 'active' : '' }}" href="{{ $quickUrl('treasury_treasury') }}">خزينة ↔ خزينة</a>
            <a class="orion-ft-chip {{ $quick === 'treasury_bank' ? 'active' : '' }}" href="{{ $quickUrl('treasury_bank') }}">خزينة ↔ بنك</a>
            <a class="orion-ft-chip {{ $quick === 'bank_bank' ? 'active' : '' }}" href="{{ $quickUrl('bank_bank') }}">بنك ↔ بنك</a>
        </div>
    </div>

    <div class="orion-ft-table-shell">
        <div class="orion-ft-table-head">
            <div>
                <div class="orion-ft-table-title">سجل التحويلات</div>
                <div class="orion-ft-table-sub">
                    جميع التحويلات المالية المربوطة بقيود اليومية مع الحفاظ على مسار العكس الحالي.
                </div>
            </div>

            <span class="orion-ft-live">
                <span class="orion-ft-meta-dot"></span>
                LIVE ACCOUNTING
            </span>
        </div>

        <div class="orion-ft-table">
            {{ $this->table }}
        </div>
    </div>

</div>
</x-filament-panels::page>
