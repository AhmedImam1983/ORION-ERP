<x-filament-panels::page>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
*{box-sizing:border-box}
:root{
    --oc-bg:#07101d;
    --oc-panel:#0b1627;
    --oc-panel-2:#0e1b30;
    --oc-panel-3:#111f35;
    --oc-line:#21334f;
    --oc-line-soft:rgba(255,255,255,.065);
    --oc-text:#f4f7fb;
    --oc-muted:#7d8da6;
    --oc-muted-2:#536681;
    --oc-purple:#8b5cf6;
    --oc-purple-2:#a78bfa;
    --oc-green:#27d17f;
    --oc-blue:#4b9cff;
    --oc-amber:#f4a81d;
    --oc-red:#ff647c;
    --oc-cyan:#22d3ee;
}
[x-cloak]{display:none!important}
.fi-page{max-width:none!important}
.orion-command,.orion-command *{font-family:'Cairo',sans-serif!important}
.orion-command{direction:rtl;color:var(--oc-text);width:100%;padding:0 20px 36px}
.oc-svg{width:18px;height:18px;display:block;stroke:currentColor}
.oc-card{background:linear-gradient(145deg,rgba(14,27,48,.98),rgba(8,18,33,.98));border:1px solid var(--oc-line);border-radius:15px;box-shadow:0 18px 46px rgba(0,0,0,.12)}
.oc-kicker{font-size:9px;letter-spacing:.08em;color:#71839e;font-weight:800}
.oc-section-title{font-size:15px;font-weight:950;color:#f3f6fb}
.oc-section-sub{font-size:8px;color:#677b97;margin-top:3px}
.oc-muted{color:var(--oc-muted)}
.oc-green{color:var(--oc-green)!important}.oc-blue{color:var(--oc-blue)!important}.oc-purple{color:#b69cff!important}.oc-amber{color:var(--oc-amber)!important}.oc-red{color:var(--oc-red)!important}

/* Header */
.oc-head{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:15px;padding:3px 1px}
.oc-title{font-size:28px;line-height:1.15;font-weight:950;letter-spacing:-.03em;color:#fff}
.oc-subtitle{font-size:11px;color:#7789a4;margin-top:6px}
.oc-head-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.oc-date,.oc-health{display:inline-flex;align-items:center;gap:8px;height:38px;padding:0 12px;border:1px solid #213553;border-radius:10px;background:#0b1729;color:#899bb4;font-size:9px;font-weight:800}
.oc-health{color:#6ee7b7}.oc-health-dot{width:7px;height:7px;border-radius:999px;background:var(--oc-green);box-shadow:0 0 10px rgba(39,209,127,.6)}
.oc-head-btn{height:38px;padding:0 14px;border:1px solid rgba(139,92,246,.62);border-radius:10px;background:linear-gradient(135deg,#8056ed,#6942d7);color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:9px;font-weight:900;box-shadow:0 9px 24px rgba(112,73,232,.2)}

/* KPI row */
.oc-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin-bottom:12px}
.oc-kpi{position:relative;overflow:hidden;min-height:150px;padding:16px 16px 12px;background:linear-gradient(150deg,#0e1c31,#091526);border:1px solid #223754;border-radius:16px}
.oc-kpi:before{content:"";position:absolute;width:110px;height:110px;border-radius:50%;left:-50px;bottom:-62px;opacity:.16;filter:blur(2px)}
.oc-kpi.green:before{background:var(--oc-green)}.oc-kpi.blue:before{background:var(--oc-blue)}.oc-kpi.amber:before{background:var(--oc-amber)}.oc-kpi.purple:before{background:var(--oc-purple)}.oc-kpi.cyan:before{background:var(--oc-cyan)}
.oc-kpi-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.oc-kpi-label{font-size:10px;color:#9aabc1;font-weight:800}
.oc-kpi-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:10px;background:#12243c;border:1px solid #2a4163}
.oc-kpi.green .oc-kpi-icon{color:#55e5a0;background:rgba(39,209,127,.08);border-color:rgba(39,209,127,.18)}
.oc-kpi.blue .oc-kpi-icon{color:#75b6ff;background:rgba(75,156,255,.08);border-color:rgba(75,156,255,.18)}
.oc-kpi.amber .oc-kpi-icon{color:#ffc657;background:rgba(244,168,29,.08);border-color:rgba(244,168,29,.18)}
.oc-kpi.purple .oc-kpi-icon{color:#bba7ff;background:rgba(139,92,246,.08);border-color:rgba(139,92,246,.2)}
.oc-kpi.cyan .oc-kpi-icon{color:#67e8f9;background:rgba(34,211,238,.08);border-color:rgba(34,211,238,.18)}
.oc-kpi-value{font-size:23px;line-height:1.15;font-weight:950;margin-top:15px;direction:ltr;text-align:right;white-space:nowrap}
.oc-kpi-currency{font-size:11px;margin-inline-start:4px;color:#d9e2ef}
.oc-trend{display:flex;align-items:center;gap:5px;margin-top:10px;font-size:8px;font-weight:900}
.oc-trend.up{color:#42dc91}.oc-trend.down{color:#ff7d8f}.oc-trend.neutral{color:#8294ac}
.oc-trend svg{width:12px;height:12px}
.oc-spark{height:30px;margin-top:7px;opacity:.78}
.oc-spark svg{width:100%;height:100%}

/* Core grid */
.oc-core{display:grid;grid-template-columns:270px minmax(0,1fr) 265px;grid-template-areas:"alerts chart side";gap:11px;align-items:stretch;margin-bottom:11px;direction:ltr}
.oc-core>*{direction:rtl;min-width:0}
.oc-alert-panel{grid-area:alerts}
.oc-chart-panel{grid-area:chart;min-width:0;overflow:hidden}
.oc-side{grid-area:side;display:flex;flex-direction:column;gap:11px;min-width:0;overflow:hidden}
.oc-panel-head{height:58px;padding:11px 14px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--oc-line-soft)}
.oc-panel-link{font-size:8px;color:#ad99ff;text-decoration:none;font-weight:900}
.oc-badge{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;border:1px solid rgba(139,92,246,.2);background:rgba(139,92,246,.07);color:#b7a3ff;font-size:8px;font-weight:900}

/* Alerts */
.oc-alert-list{padding:10px 12px 12px}
.oc-alert{display:grid;grid-template-columns:36px minmax(0,1fr) 38px;gap:9px;align-items:center;padding:11px 0;border-bottom:1px solid var(--oc-line-soft);text-decoration:none}
.oc-alert:last-child{border-bottom:0}
.oc-alert-icon{width:34px;height:34px;border-radius:9px;display:grid;place-items:center;background:#101e33;border:1px solid #263a59;color:#889bb7}
.oc-alert.danger .oc-alert-icon{color:#ff778c;background:rgba(255,100,124,.07);border-color:rgba(255,100,124,.16)}
.oc-alert.warning .oc-alert-icon{color:#f7b84a;background:rgba(244,168,29,.07);border-color:rgba(244,168,29,.17)}
.oc-alert.info .oc-alert-icon{color:#64b0ff;background:rgba(75,156,255,.07);border-color:rgba(75,156,255,.17)}
.oc-alert.purple .oc-alert-icon{color:#ad96ff;background:rgba(139,92,246,.07);border-color:rgba(139,92,246,.17)}
.oc-alert-title{font-size:9px;color:#e3eaf3;font-weight:900}
.oc-alert-hint{font-size:7px;color:#657893;margin-top:3px;line-height:1.5}
.oc-alert-count{font-size:18px;font-weight:950;text-align:left;direction:ltr}
.oc-alert.danger .oc-alert-count{color:var(--oc-red)}.oc-alert.warning .oc-alert-count{color:var(--oc-amber)}.oc-alert.info .oc-alert-count{color:var(--oc-blue)}.oc-alert.purple .oc-alert-count{color:#ad96ff}.oc-alert.ok .oc-alert-count{color:var(--oc-green)}
.oc-alert-footer{padding:9px 12px 12px}.oc-alert-footer a{font-size:8px;color:#a994ff;text-decoration:none;font-weight:900}

/* Chart */
.oc-chart-toolbar{display:flex;align-items:center;gap:13px}
.oc-legend{display:flex;align-items:center;gap:11px;font-size:8px;color:#7587a2}
.oc-legend span{display:inline-flex;align-items:center;gap:5px}.oc-legend i{width:8px;height:8px;border-radius:2px;display:inline-block;background:#8b5cf6}.oc-legend .prev i{background:#4a5b75}
.oc-chart{height:288px;padding:2px 8px 0;direction:ltr;min-width:0;max-width:100%;overflow:hidden;position:relative}
.oc-chart .apexcharts-canvas,.oc-chart .apexcharts-svg,.oc-chart foreignObject{max-width:100%!important;overflow:hidden!important}
.oc-chart .apexcharts-canvas{left:0!important}
.oc-chart-bottom{display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-top:1px solid var(--oc-line-soft)}
.oc-chart-stat{padding:11px 13px;text-align:center;border-left:1px solid var(--oc-line-soft)}
.oc-chart-stat:last-child{border-left:0}.oc-chart-stat span{font-size:7px;color:#657893;display:block}.oc-chart-stat strong{font-size:12px;color:#e9eef6;display:block;margin-top:3px}

/* Quick actions */
.oc-quick-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:12px}
.oc-quick{min-height:69px;padding:10px;border-radius:11px;border:1px solid #223754;background:#0d1b2f;text-decoration:none;display:flex;align-items:center;gap:9px;transition:.16s}
.oc-quick:hover{transform:translateY(-1px);border-color:#7856df;background:#11213a}
.oc-quick-icon{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;background:rgba(139,92,246,.1);border:1px solid rgba(139,92,246,.17);color:#b49dff;flex:none}
.oc-quick:nth-child(2) .oc-quick-icon{color:#4de29a;background:rgba(39,209,127,.08);border-color:rgba(39,209,127,.16)}
.oc-quick:nth-child(3) .oc-quick-icon{color:#ffbd4c;background:rgba(244,168,29,.08);border-color:rgba(244,168,29,.16)}
.oc-quick:nth-child(4) .oc-quick-icon{color:#63adff;background:rgba(75,156,255,.08);border-color:rgba(75,156,255,.16)}
.oc-quick:nth-child(5) .oc-quick-icon{color:#5ee4f6;background:rgba(34,211,238,.08);border-color:rgba(34,211,238,.16)}
.oc-quick:nth-child(6) .oc-quick-icon{color:#ff7ea1;background:rgba(255,100,124,.08);border-color:rgba(255,100,124,.16)}
.oc-quick strong{font-size:8px;color:#e7edf6;display:block}.oc-quick small{font-size:6px;color:#657893;display:block;margin-top:2px}

/* Financial */
.oc-fin-list{padding:7px 13px 11px}
.oc-fin-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px 0;border-bottom:1px solid var(--oc-line-soft)}
.oc-fin-row:last-child{border-bottom:0}.oc-fin-name{display:flex;align-items:center;gap:7px;font-size:8px;color:#8698b0}.oc-fin-name svg{width:14px;height:14px}.oc-fin-value{font-size:9px;font-weight:900;direction:ltr;color:#e2e9f2}

/* Activity */
.oc-activity-panel{margin-bottom:11px}
.oc-activity-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:0;padding:4px 8px 11px}
.oc-activity-item{position:relative;padding:13px 12px 24px;border-left:1px solid var(--oc-line-soft);min-width:0}
.oc-activity-item:last-child{border-left:0}
.oc-act-top{display:flex;align-items:center;gap:8px}
.oc-act-icon{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;background:rgba(139,92,246,.09);border:1px solid rgba(139,92,246,.18);color:#ad96ff;flex:none}
.oc-act-title{font-size:8px;font-weight:900;color:#e6edf6;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.oc-act-sub{font-size:7px;color:#61738e;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.oc-act-amount{font-size:10px;font-weight:950;margin-top:11px;direction:ltr;color:#f0f4fa}
.oc-act-line{position:absolute;left:12px;right:12px;bottom:13px;height:1px;background:#2a3b56}.oc-act-line:after{content:"";position:absolute;width:7px;height:7px;border-radius:50%;right:0;top:-3px;background:#7956e6;box-shadow:0 0 0 3px rgba(121,86,230,.11)}

/* Backup */
.oc-admin{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 14px}
.oc-admin-text strong{display:block;font-size:9px;color:#eef3f8}.oc-admin-text span{font-size:7px;color:#6d809b}.oc-admin-actions{display:flex;gap:7px}
.oc-admin-actions button{height:31px;padding:0 10px;border-radius:8px;font-size:8px;font-weight:900;cursor:pointer}.oc-backup{border:0;background:#754de1;color:#fff}.oc-restore{border:1px solid rgba(244,168,29,.24);background:rgba(244,168,29,.06);color:#f7bd55}

/* Cashier */
.oc-cash-session{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px;margin-bottom:11px;border-color:rgba(39,209,127,.24)}
.oc-cash-session.closed{border-color:rgba(244,168,29,.22)}.oc-cash-title{font-size:12px;font-weight:950}.oc-cash-meta{display:flex;gap:13px;flex-wrap:wrap;margin-top:5px;font-size:8px;color:#6e809a}.oc-cash-meta strong{color:#e1e8f1}.oc-cash-state{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;font-size:8px;font-weight:900;color:#5fe6aa;border:1px solid rgba(39,209,127,.2);background:rgba(39,209,127,.06)}
.oc-cash-state.closed{color:#f7bd55;border-color:rgba(244,168,29,.2);background:rgba(244,168,29,.06)}
.oc-cash-actions{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:11px}.oc-cash-action{padding:14px;border-radius:13px;border:1px solid #223754;background:#0d1a2d;text-decoration:none;display:flex;align-items:center;gap:10px}.oc-cash-action:hover{border-color:#7453dc}.oc-cash-action .oc-quick-icon{width:38px;height:38px}.oc-cash-action strong{display:block;font-size:10px;color:#edf2f8}.oc-cash-action small{display:block;font-size:7px;color:#667994;margin-top:2px}
.oc-cash-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:9px;margin-bottom:11px}.oc-cash-kpi{padding:13px;border-radius:12px;border:1px solid #21334e;background:#0b1728}.oc-cash-kpi span{font-size:8px;color:#7487a2}.oc-cash-kpi strong{display:block;font-size:17px;margin-top:5px;direction:ltr;text-align:right}
.oc-cash-grid{display:grid;grid-template-columns:1fr 1fr;gap:11px}

/* Modal */
.oc-modal{position:fixed;inset:0;z-index:9999;background:rgba(2,7,15,.82);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;direction:rtl}
.oc-modal-box{width:500px;max-width:94vw;border:1px solid #2b3e5c;border-radius:17px;background:#0b1525;padding:20px;box-shadow:0 28px 90px rgba(0,0,0,.5)}
.oc-modal-title{font-size:14px;font-weight:950}.oc-modal-warning{margin:8px 0 11px;padding:9px 10px;border-radius:9px;border:1px solid rgba(255,100,124,.18);background:rgba(255,100,124,.07);color:#ff8899;font-size:8px}
.oc-backup-file{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px 10px;margin-bottom:6px;border:1px solid #20334f;border-radius:9px;background:#0e1b2f}.oc-backup-file button{border:1px solid rgba(255,100,124,.18);background:rgba(255,100,124,.07);color:#ff8395;border-radius:7px;padding:6px 9px;font-size:8px;font-weight:900;cursor:pointer}.oc-close{width:100%;height:34px;margin-top:8px;border:1px solid #263a58;border-radius:8px;background:transparent;color:#8395ae;font-size:8px;font-weight:900;cursor:pointer}

@media(max-width:1350px){
    .oc-kpis{grid-template-columns:repeat(3,1fr)}
    .oc-core{grid-template-columns:240px minmax(0,1fr);grid-template-areas:"alerts chart" "side side"}
    .oc-side{display:grid;grid-template-columns:1fr 1fr}
    .oc-activity-grid{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:1000px){
    .oc-core{grid-template-columns:1fr;grid-template-areas:"chart" "alerts" "side"}
    .oc-kpis{grid-template-columns:1fr 1fr}
    .oc-activity-grid{grid-template-columns:1fr 1fr}
    .oc-cash-kpis{grid-template-columns:1fr 1fr}
}
@media(max-width:700px){
    .orion-command{padding:0 8px 25px}
    .oc-head{align-items:flex-start;flex-direction:column}.oc-title{font-size:23px}.oc-head-right{width:100%}
    .oc-kpis,.oc-side,.oc-quick-grid,.oc-activity-grid,.oc-cash-actions,.oc-cash-kpis,.oc-cash-grid{grid-template-columns:1fr}
    .oc-chart-bottom{grid-template-columns:1fr}.oc-chart-stat{border-left:0;border-bottom:1px solid var(--oc-line-soft)}
    .oc-activity-item{border-left:0;border-bottom:1px solid var(--oc-line-soft)}
    .oc-cash-session{align-items:flex-start;flex-direction:column}
}
</style>

@if($showRestore)
<div class="oc-modal">
    <div class="oc-modal-box">
        <div class="oc-modal-title">استعادة نسخة احتياطية</div>
        <div class="oc-modal-warning">الاستعادة تستبدل البيانات الحالية. تأكد من اختيار النسخة الصحيحة قبل المتابعة.</div>
        @forelse($backupFiles as $f)
            <div class="oc-backup-file">
                <div>
                    <div style="font-size:9px;font-weight:900;color:#eef3f8">{{ $f['name'] }}</div>
                    <div style="font-size:7px;color:#657892;margin-top:2px">{{ $f['date'] }} · {{ $f['size'] }} KB</div>
                </div>
                <button wire:click="restoreBackup('{{ $f['name'] }}')" onclick="confirm('هل أنت متأكد من استعادة هذه النسخة؟')||event.stopImmediatePropagation()">استعادة</button>
            </div>
        @empty
            <div style="padding:25px;text-align:center;color:#667994;font-size:9px">لا توجد نسخ احتياطية</div>
        @endforelse
        <button wire:click="$set('showRestore',false)" class="oc-close">إغلاق</button>
    </div>
</div>
@endif

<div class="orion-command">
    <div class="oc-head">
        <div>
            <div class="oc-kicker">{{ $isCashier ? 'ORION POS OPERATIONS' : ($isBranchUser ? 'ORION BRANCH OPERATIONS' : 'ORION EXECUTIVE COMMAND CENTER') }}</div>
            <div class="oc-title">{{ $isCashier ? 'لوحة تشغيل الكاشير' : ($isBranchUser ? 'لوحة الفرع' : 'مركز القيادة') }}</div>
            <div class="oc-subtitle">{{ $isCashier ? 'متابعة الجلسة والمبيعات والتحصيل للمستخدم الحالي' : ($isBranchUser ? 'ملخص المبيعات والمستحقات داخل نطاق فرعك' : 'نظرة تنفيذية شاملة على الأداء المالي والتشغيلي اليوم') }}</div>
        </div>
        <div class="oc-head-right">
            <div class="oc-health"><span class="oc-health-dot"></span> النظام يعمل</div>
            <div class="oc-date">
                <svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
                {{ now()->locale('ar')->isoFormat('dddd، D MMMM YYYY') }}
            </div>
            @if(!$isCashier && !$isBranchUser)
                <a href="{{ route('filament.admin.resources.invoices.create') }}" class="oc-head-btn">
                    <svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>
                    فاتورة جديدة
                </a>
            @endif
        </div>
    </div>

    @if($isCashier)
        <div class="oc-card oc-cash-session {{ $cashierSession ? '' : 'closed' }}">
            <div>
                <div class="oc-cash-title">{{ $cashierSession ? 'جلسة الكاشير الحالية' : 'لا توجد جلسة كاشير مفتوحة' }}</div>
                @if($cashierSession)
                    <div class="oc-cash-meta">
                        <span>الجلسة <strong>{{ $cashierSession['number'] }}</strong></span>
                        <span>الفرع <strong>{{ $cashierSession['branch'] }}</strong></span>
                        <span>وقت الفتح <strong>{{ $cashierSession['opened_at'] }}</strong></span>
                        <span>رصيد البداية <strong>{{ number_format($cashierSession['opening_cash'],2) }} ر.س</strong></span>
                    </div>
                @else
                    <div class="oc-cash-meta">افتح نقطة البيع لبدء جلسة جديدة وتسجيل المبيعات على الكاشير الحالي.</div>
                @endif
            </div>
            <span class="oc-cash-state {{ $cashierSession ? '' : 'closed' }}">
                <span class="oc-health-dot" style="{{ $cashierSession ? '' : 'background:#f4a81d;box-shadow:none' }}"></span>
                {{ $cashierSession ? 'جلسة مفتوحة' : 'تحتاج فتح جلسة' }}
            </span>
        </div>

        <div class="oc-cash-actions">
            <a href="{{ url('/admin/pos-page') }}" class="oc-cash-action">
                <div class="oc-quick-icon">
                    <svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                </div>
                <div><strong>{{ $cashierSession ? 'فتح نقطة البيع' : 'فتح جلسة الكاشير' }}</strong><small>POS · البيع والتحصيل</small></div>
            </a>
            <a href="{{ route('filament.admin.resources.invoices.create') }}" class="oc-cash-action">
                <div class="oc-quick-icon">
                    <svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18H6zM9 8h6M9 12h6M9 16h4"/></svg>
                </div>
                <div><strong>فاتورة جديدة</strong><small>إنشاء فاتورة بيع مباشرة</small></div>
            </a>
            <a href="{{ url('/admin/pos-page') }}" class="oc-cash-action">
                <div class="oc-quick-icon">
                    <svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 7H5v4"/><path d="M5 11c1.4-4 6.1-6.1 10-4.4A7 7 0 1 1 8 18"/></svg>
                </div>
                <div><strong>مرتجع بيع</strong><small>إدارة المرتجعات من POS</small></div>
            </a>
        </div>

        <div class="oc-cash-kpis">
            <div class="oc-cash-kpi"><span>مبيعاتي اليوم</span><strong class="oc-green">{{ number_format($totalSalesToday,2) }} ر.س</strong></div>
            <div class="oc-cash-kpi"><span>المحصّل اليوم</span><strong class="oc-blue">{{ number_format($collectedToday,2) }} ر.س</strong></div>
            <div class="oc-cash-kpi"><span>مبيعات الجلسة</span><strong class="oc-purple">{{ number_format($cashierSessionSales,2) }} ر.س</strong></div>
            <div class="oc-cash-kpi"><span>النقد المتوقع بالصندوق</span><strong class="oc-amber">{{ number_format($cashierExpectedCash,2) }} ر.س</strong></div>
        </div>

        <div class="oc-cash-kpis">
            <div class="oc-cash-kpi"><span>نقدي</span><strong class="oc-green">{{ number_format($cashierCash,2) }} ر.س</strong></div>
            <div class="oc-cash-kpi"><span>بطاقة</span><strong class="oc-blue">{{ number_format($cashierCard,2) }} ر.س</strong></div>
            <div class="oc-cash-kpi"><span>محفظة</span><strong class="oc-purple">{{ number_format($cashierWallet,2) }} ر.س</strong></div>
            <div class="oc-cash-kpi"><span>مرتجعات</span><strong class="oc-red">{{ number_format($cashierRefunds,2) }} ر.س</strong></div>
        </div>

        <div class="oc-cash-grid">
            <div class="oc-card">
                <div class="oc-panel-head"><div><div class="oc-section-title">فواتيري اليوم</div><div class="oc-section-sub">آخر الفواتير الخاصة بالكاشير الحالي</div></div><a class="oc-panel-link" href="{{ route('filament.admin.resources.invoices.index') }}">كل الفواتير</a></div>
                <div class="oc-alert-list">
                    @forelse($recentInvoices as $inv)
                        <div class="oc-fin-row">
                            <div>
                                <div style="font-size:9px;font-weight:900">{{ $inv->invoice_number }}</div>
                                <div style="font-size:7px;color:#657893;margin-top:2px">{{ $inv->customer?->name ?? 'عميل نقدي' }} · {{ $inv->created_at?->format('H:i') }}</div>
                            </div>
                            <div class="oc-fin-value">{{ number_format($inv->total,2) }} ر.س</div>
                        </div>
                    @empty
                        <div style="padding:30px;text-align:center;color:#657893;font-size:9px">لا توجد فواتير لك اليوم</div>
                    @endforelse
                </div>
            </div>
            <div class="oc-card">
                <div class="oc-panel-head"><div><div class="oc-section-title">آخر عمليات الجلسة</div><div class="oc-section-sub">حركة الدفع والمرتجعات</div></div><a class="oc-panel-link" href="{{ url('/admin/pos-sessions') }}">الجلسات</a></div>
                <div class="oc-alert-list">
                    @forelse($cashierRecentTransactions as $tx)
                        <div class="oc-fin-row">
                            <div>
                                <div style="font-size:9px;font-weight:900">{{ $tx['number'] }}</div>
                                <div style="font-size:7px;color:#657893;margin-top:2px">{{ strtoupper($tx['method'] ?? '-') }} · {{ $tx['type'] === 'return' ? 'مرتجع' : 'بيع' }} · {{ $tx['time'] }}</div>
                            </div>
                            <div class="oc-fin-value {{ $tx['type'] === 'return' ? 'oc-red' : 'oc-green' }}">{{ number_format($tx['amount'],2) }} ر.س</div>
                        </div>
                    @empty
                        <div style="padding:30px;text-align:center;color:#657893;font-size:9px">{{ $cashierSession ? 'لا توجد عمليات في الجلسة بعد' : 'افتح جلسة لبدء تسجيل العمليات' }}</div>
                    @endforelse
                </div>
            </div>
        </div>

    @elseif($isBranchUser)
        <div class="oc-kpis">
            <div class="oc-kpi green">
                <div class="oc-kpi-top"><div><div class="oc-kpi-label">مبيعات اليوم داخل فرعك</div></div></div>
                <div class="oc-kpi-value oc-green">{{ number_format($totalSalesToday,2) }} <span class="oc-kpi-currency">ر.س</span></div>
            </div>
            <div class="oc-kpi blue">
                <div class="oc-kpi-top"><div><div class="oc-kpi-label">التحصيل اليوم داخل فرعك</div></div></div>
                <div class="oc-kpi-value oc-blue">{{ number_format($collectedToday,2) }} <span class="oc-kpi-currency">ر.س</span></div>
            </div>
            <div class="oc-kpi purple">
                <div class="oc-kpi-top"><div><div class="oc-kpi-label">فواتير البيع في نطاق فرعك</div></div></div>
                <div class="oc-kpi-value oc-purple">{{ number_format($totalInvoices) }}</div>
            </div>
            <div class="oc-kpi amber">
                <div class="oc-kpi-top"><div><div class="oc-kpi-label">مستحقات العملاء في فرعك</div></div></div>
                <div class="oc-kpi-value oc-amber">{{ number_format($totalDebts,2) }} <span class="oc-kpi-currency">ر.س</span></div>
                <div class="oc-trend neutral">{{ number_format($unpaidCount) }} فاتورة غير مسددة بالكامل</div>
            </div>
        </div>
        <div class="oc-card" style="margin-top:16px;padding:20px;">
            <div class="oc-section-title">نطاق البيانات</div>
            <div class="oc-section-sub">هذه اللوحة تعرض ملخص الفواتير المرتبطة بفرع حسابك فقط. تقارير الأرباح والخسائر والميزانية الحالية تعتمد على قيود محاسبية غير مرتبطة مباشرة بفرع في قاعدة البيانات، لذلك لا نعرضها في هذه اللوحة المحدودة.</div>
            @if(auth()->user()?->can('view_invoices'))
                <div style="margin-top:14px;"><a class="oc-panel-link" href="{{ route('filament.admin.resources.invoices.index') }}">عرض فواتير الفرع المسموح بها</a></div>
            @endif
        </div>

    @else
        @php
            $trendClass = fn($v) => $v > 0 ? 'up' : ($v < 0 ? 'down' : 'neutral');
            $trendArrow = fn($v) => $v >= 0 ? 'M5 12l7-7 7 7M12 5v14' : 'M5 12l7 7 7-7M12 19V5';
            $sevenTotal = collect($chartRows)->sum('value');
            $previousSevenTotal = collect($previousChartRows)->sum('value');
            $sevenChange = abs($previousSevenTotal) < .01 ? ($sevenTotal > 0 ? 100 : 0) : round((($sevenTotal-$previousSevenTotal)/abs($previousSevenTotal))*100,1);
            $bestDay = collect($chartRows)->sortByDesc('value')->first();
            $dailyAvg = count($chartRows) ? $sevenTotal / count($chartRows) : 0;
        @endphp

        <div class="oc-kpis">
            <div class="oc-kpi green">
                <div class="oc-kpi-top">
                    <div><div class="oc-kpi-label">إيرادات اليوم</div></div>
                    <div class="oc-kpi-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/><path d="M5 7l4-3 4 3 5-4"/></svg></div>
                </div>
                <div class="oc-kpi-value oc-green">{{ number_format($totalSalesToday,2) }} <span class="oc-kpi-currency">ر.س</span></div>
                <div class="oc-trend {{ $trendClass($salesChangePct) }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $trendArrow($salesChangePct) }}"/></svg>
                    {{ number_format(abs($salesChangePct),1) }}% مقارنة بأمس
                </div>
                <div class="oc-spark"><svg viewBox="0 0 220 35" preserveAspectRatio="none"><path d="M0 29 C20 21,32 31,47 20 S78 14,92 23 S120 28,137 14 S165 11,180 20 S202 13,220 9" fill="none" stroke="#27d17f" stroke-width="2"/><path d="M0 35 L0 29 C20 21,32 31,47 20 S78 14,92 23 S120 28,137 14 S165 11,180 20 S202 13,220 9 L220 35Z" fill="rgba(39,209,127,.08)"/></svg></div>
            </div>

            <div class="oc-kpi blue">
                <div class="oc-kpi-top">
                    <div><div class="oc-kpi-label">التحصيل اليوم</div></div>
                    <div class="oc-kpi-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M7 10h5M16 10h1M7 14h3"/></svg></div>
                </div>
                <div class="oc-kpi-value oc-blue">{{ number_format($collectedToday,2) }} <span class="oc-kpi-currency">ر.س</span></div>
                <div class="oc-trend {{ $trendClass($collectionChangePct) }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $trendArrow($collectionChangePct) }}"/></svg>
                    {{ number_format(abs($collectionChangePct),1) }}% مقارنة بأمس
                </div>
                <div class="oc-spark"><svg viewBox="0 0 220 35" preserveAspectRatio="none"><path d="M0 26 C18 22,33 16,47 22 S74 30,92 18 S118 12,137 20 S160 26,181 14 S204 19,220 7" fill="none" stroke="#4b9cff" stroke-width="2"/></svg></div>
            </div>

            <div class="oc-kpi amber">
                <div class="oc-kpi-top">
                    <div><div class="oc-kpi-label">مصروفات الشهر</div></div>
                    <div class="oc-kpi-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18H6zM9 8h6M9 12h6M9 16h4"/></svg></div>
                </div>
                <div class="oc-kpi-value oc-amber">{{ number_format($monthExpenses,2) }} <span class="oc-kpi-currency">ر.س</span></div>
                <div class="oc-trend {{ $expensesChangePct <= 0 ? 'up' : 'down' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $trendArrow(-$expensesChangePct) }}"/></svg>
                    {{ number_format(abs($expensesChangePct),1) }}% مقارنة بالفترة السابقة
                </div>
                <div class="oc-spark"><svg viewBox="0 0 220 35" preserveAspectRatio="none"><path d="M0 18 C20 12,35 24,52 17 S82 8,96 18 S125 31,143 16 S170 12,189 22 S205 13,220 15" fill="none" stroke="#f4a81d" stroke-width="2"/></svg></div>
            </div>

            <div class="oc-kpi purple">
                <div class="oc-kpi-top">
                    <div><div class="oc-kpi-label">صافي الأداء</div></div>
                    <div class="oc-kpi-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 18l5-5 4 3 7-9"/><path d="M15 7h5v5"/></svg></div>
                </div>
                <div class="oc-kpi-value {{ $monthNetProfit >= 0 ? 'oc-green' : 'oc-red' }}">{{ number_format($monthNetProfit,2) }} <span class="oc-kpi-currency">ر.س</span></div>
                <div class="oc-trend {{ $trendClass($netChangePct) }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $trendArrow($netChangePct) }}"/></svg>
                    هامش {{ number_format($monthNetMargin,1) }}% · تغير {{ number_format(abs($netChangePct),1) }}%
                </div>
                <div class="oc-spark"><svg viewBox="0 0 220 35" preserveAspectRatio="none"><path d="M0 28 C19 25,31 18,49 23 S79 27,94 15 S125 23,142 12 S168 18,184 10 S205 16,220 6" fill="none" stroke="#8b5cf6" stroke-width="2"/></svg></div>
            </div>

            <div class="oc-kpi cyan">
                <div class="oc-kpi-top">
                    <div><div class="oc-kpi-label">قيمة المخزون</div></div>
                    <div class="oc-kpi-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 8l9-5 9 5-9 5-9-5zM3 12l9 5 9-5M3 16l9 5 9-5"/></svg></div>
                </div>
                <div class="oc-kpi-value oc-cyan">{{ number_format($inventoryValue,2) }} <span class="oc-kpi-currency">ر.س</span></div>
                <div class="oc-trend neutral">{{ number_format($totalProducts) }} صنف مسجل · بالتكلفة</div>
                <div class="oc-spark"><svg viewBox="0 0 220 35" preserveAspectRatio="none"><path d="M0 24 C20 20,40 27,58 18 S90 11,105 17 S130 24,149 15 S177 11,195 18 S210 11,220 12" fill="none" stroke="#22d3ee" stroke-width="2"/></svg></div>
            </div>
        </div>

        <div class="oc-core">
            <div class="oc-card oc-alert-panel">
                <div class="oc-panel-head">
                    <div><div class="oc-section-title">تنبيهات تحتاج تدخل</div><div class="oc-section-sub">أهم البنود التي تحتاج متابعة الآن</div></div>
                    <span class="oc-badge">{{ collect($adminAlerts)->where('severity','!=','ok')->count() }}</span>
                </div>
                <div class="oc-alert-list">
                    @foreach($adminAlerts as $alert)
                        <a href="{{ $alert['url'] }}" class="oc-alert {{ $alert['severity'] }}">
                            <div class="oc-alert-icon">
                                @if($alert['icon']==='box')
                                    <svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7l8-4 8 4-8 4-8-4zM4 7v10l8 4 8-4V7M12 11v10"/></svg>
                                @elseif($alert['icon']==='user')
                                    <svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c1-5 4-7 8-7s7 2 8 7"/></svg>
                                @elseif($alert['icon']==='cart')
                                    <svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h2l2 11h10l3-8H6"/><circle cx="9" cy="19" r="1.5"/><circle cx="17" cy="19" r="1.5"/></svg>
                                @else
                                    <svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l4 4v16H6zM14 2v5h5M9 12h6M9 16h6"/></svg>
                                @endif
                            </div>
                            <div><div class="oc-alert-title">{{ $alert['label'] }}</div><div class="oc-alert-hint">{{ $alert['hint'] }}</div></div>
                            <div class="oc-alert-count">{{ $alert['value'] }}</div>
                        </a>
                    @endforeach
                </div>
                <div class="oc-alert-footer"><a href="{{ route('filament.admin.pages.reports-page') }}">فتح مركز التقارير</a></div>
            </div>

            <div class="oc-card oc-chart-panel">
                <div class="oc-panel-head">
                    <div><div class="oc-section-title">المبيعات · آخر 7 أيام</div><div class="oc-section-sub">مقارنة اتجاه المبيعات مع الفترة السابقة</div></div>
                    <div class="oc-chart-toolbar">
                        <div class="oc-legend"><span><i></i> الفترة الحالية</span><span class="prev"><i></i> الفترة السابقة</span></div>
                        <span class="oc-badge">7 أيام</span>
                    </div>
                </div>
                <div class="oc-chart" id="orion-executive-sales-chart"></div>
                <div class="oc-chart-bottom">
                    <div class="oc-chart-stat"><span>مقارنة الفترة</span><strong class="{{ $sevenChange >= 0 ? 'oc-green' : 'oc-red' }}">{{ $sevenChange >= 0 ? '+' : '' }}{{ number_format($sevenChange,1) }}%</strong></div>
                    <div class="oc-chart-stat"><span>أعلى يوم</span><strong>{{ $bestDay['label'] ?? '-' }} · {{ number_format($bestDay['value'] ?? 0,0) }} ر.س</strong></div>
                    <div class="oc-chart-stat"><span>متوسط يومي</span><strong>{{ number_format($dailyAvg,0) }} ر.س</strong></div>
                </div>
            </div>

            <div class="oc-side">
                <div class="oc-card">
                    <div class="oc-panel-head"><div><div class="oc-section-title">إجراءات سريعة</div><div class="oc-section-sub">أكثر العمليات استخدامًا</div></div></div>
                    <div class="oc-quick-grid">
                        <a class="oc-quick" href="{{ route('filament.admin.resources.invoices.create') }}"><div class="oc-quick-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18H6zM9 8h6M9 12h6M9 16h4"/></svg></div><div><strong>فاتورة مبيعات</strong><small>إنشاء فاتورة جديدة</small></div></a>
                        <a class="oc-quick" href="{{ url('/admin/pos-page') }}"><div class="oc-quick-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg></div><div><strong>نقطة البيع</strong><small>فتح شاشة POS</small></div></a>
                        <a class="oc-quick" href="{{ url('/admin/expenses-page') }}"><div class="oc-quick-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16v12H4zM7 4h10v3M8 12h8"/></svg></div><div><strong>مصروف جديد</strong><small>المصروفات اليومية</small></div></a>
                        <a class="oc-quick" href="{{ url('/admin/purchase-orders/create') }}"><div class="oc-quick-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h2l2 11h10l3-8H6"/><path d="M9 19h.01M17 19h.01"/></svg></div><div><strong>طلب شراء</strong><small>إنشاء طلب جديد</small></div></a>
                        <a class="oc-quick" href="{{ url('/admin/stock-movements') }}"><div class="oc-quick-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 7h11l-3-3M17 17H6l3 3M18 7l-3 3M6 17l3-3"/></svg></div><div><strong>حركة مخزون</strong><small>متابعة التحركات</small></div></a>
                        <a class="oc-quick" href="{{ route('filament.admin.pages.reports-page') }}"><div class="oc-quick-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/></svg></div><div><strong>التقارير</strong><small>مركز التقارير</small></div></a>
                    </div>
                </div>

                <div class="oc-card">
                    <div class="oc-panel-head"><div><div class="oc-section-title">الموقف المالي</div><div class="oc-section-sub">لقطة سريعة للأرصدة والالتزامات</div></div></div>
                    <div class="oc-fin-list">
                        <div class="oc-fin-row"><div class="oc-fin-name"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M7 10h5"/></svg>الخزائن</div><div class="oc-fin-value oc-green">{{ number_format($treasuryBalance,2) }} ر.س</div></div>
                        <div class="oc-fin-row"><div class="oc-fin-name"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10h18M5 10v8M9 10v8M15 10v8M19 10v8M2 19h20M4 7l8-4 8 4v3H4z"/></svg>البنوك</div><div class="oc-fin-value oc-blue">{{ number_format($bankBalance,2) }} ر.س</div></div>
                        <div class="oc-fin-row"><div class="oc-fin-name"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c1-5 4-7 8-7s7 2 8 7"/></svg>العملاء المدينون</div><div class="oc-fin-value oc-amber">{{ number_format($totalDebts,2) }} ر.س</div></div>
                        <div class="oc-fin-row"><div class="oc-fin-name"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="19" r="1.5"/><circle cx="18" cy="19" r="1.5"/></svg>الموردون الدائنون</div><div class="oc-fin-value oc-red">{{ number_format($supplierPayables,2) }} ر.س</div></div>
                        <div class="oc-fin-row"><div class="oc-fin-name"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 12h16M12 4v16"/></svg>إجمالي السيولة</div><div class="oc-fin-value oc-purple">{{ number_format($cashPosition,2) }} ر.س</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="oc-card oc-activity-panel">
            <div class="oc-panel-head">
                <div><div class="oc-section-title">آخر العمليات</div><div class="oc-section-sub">أحدث فواتير البيع المسجلة في النظام</div></div>
                <a class="oc-panel-link" href="{{ route('filament.admin.resources.invoices.index') }}">عرض الكل</a>
            </div>
            <div class="oc-activity-grid">
                @forelse($recentInvoices->take(5) as $inv)
                    <div class="oc-activity-item">
                        <div class="oc-act-top">
                            <div class="oc-act-icon"><svg class="oc-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l4 4v16H6zM14 2v5h5M9 12h6M9 16h6"/></svg></div>
                            <div style="min-width:0"><div class="oc-act-title">فاتورة مبيعات · {{ $inv->invoice_number }}</div><div class="oc-act-sub">{{ $inv->customer?->name ?? 'عميل نقدي' }} · {{ $inv->created_at?->diffForHumans() }}</div></div>
                        </div>
                        <div class="oc-act-amount">{{ number_format($inv->total,2) }} ر.س</div>
                        <div class="oc-act-line"></div>
                    </div>
                @empty
                    <div style="grid-column:1/-1;padding:30px;text-align:center;color:#657893;font-size:9px">لا توجد عمليات حديثة</div>
                @endforelse
            </div>
        </div>

        @if(auth()->user()->hasAnyRole(['super_admin','admin']))
            <div class="oc-card oc-admin">
                <div class="oc-admin-text"><strong>النسخ الاحتياطي للنظام</strong><span>آخر نسخة: <b class="oc-green">{{ $lastBackup }}</b></span></div>
                <div class="oc-admin-actions"><button wire:click="makeBackup" class="oc-backup">نسخة الآن</button><button wire:click="openRestore" class="oc-restore">استعادة</button></div>
            </div>
        @endif
    @endif
</div>

@if(!$isCashier && !$isBranchUser)
<script>
(function(){
    const el = document.querySelector('#orion-executive-sales-chart');
    if (!el || typeof ApexCharts === 'undefined') return;

    if (el.__orionChart) {
        try { el.__orionChart.destroy(); } catch(e) {}
    }

    const labels = @json(collect($chartRows)->pluck('label')->values());
    const current = @json(collect($chartRows)->pluck('value')->values());
    const previous = @json(collect($previousChartRows)->pluck('value')->values());

    const containerWidth = Math.max(320, Math.floor(el.getBoundingClientRect().width || el.parentElement?.getBoundingClientRect().width || 640));

    const chart = new ApexCharts(el,{
        chart:{
            type:'line',
            height:282,
            width: containerWidth,
            toolbar:{show:false},
            background:'transparent',
            fontFamily:'Cairo',
            parentHeightOffset:0,
            redrawOnParentResize:true,
            redrawOnWindowResize:true,
            animations:{enabled:true,speed:450}
        },
        series:[
            {name:'الفترة الحالية',type:'column',data:current},
            {name:'الفترة السابقة',type:'line',data:previous}
        ],
        colors:['#8b5cf6','#536681'],
        stroke:{width:[0,2],curve:'smooth',dashArray:[0,5]},
        plotOptions:{bar:{borderRadius:5,columnWidth:'34%'}},
        dataLabels:{enabled:false},
        fill:{opacity:[.92,.9]},
        markers:{size:[0,0]},
        grid:{borderColor:'rgba(255,255,255,.055)',strokeDashArray:5,padding:{left:6,right:8}},
        xaxis:{
            categories:labels,
            labels:{style:{colors:'#687b97',fontFamily:'Cairo',fontSize:'9px'}},
            axisBorder:{show:false},axisTicks:{show:false}
        },
        yaxis:{
            min:0,
            labels:{
                style:{colors:'#536681',fontFamily:'Cairo',fontSize:'8px'},
                formatter:v=>Number(v).toLocaleString('en-US')
            }
        },
        tooltip:{
            theme:'dark',
            shared:true,
            y:{formatter:v=>Number(v).toLocaleString('en-US',{maximumFractionDigits:2})+' ر.س'}
        },
        legend:{show:false}
    });

    chart.render();

    const syncChart = () => {
        const w = Math.max(320, Math.floor(el.getBoundingClientRect().width || el.parentElement?.getBoundingClientRect().width || 640));
        try { chart.updateOptions({ chart: { width: w } }, false, false, false); } catch (e) {}
    };

    setTimeout(syncChart, 60);
    setTimeout(syncChart, 240);
    window.addEventListener('resize', syncChart);

    if (typeof ResizeObserver !== 'undefined') {
        const ro = new ResizeObserver(syncChart);
        ro.observe(el);
        if (el.parentElement) ro.observe(el.parentElement);
        el.__orionChartObserver = ro;
    }

    el.__orionChart = chart;
})();
</script>
@endif
</x-filament-panels::page>
