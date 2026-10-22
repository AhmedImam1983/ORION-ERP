<x-filament-panels::page>
@php
    $changeOperating = ($showComparison && abs($previousOperating) >= 0.01)
        ? (($operating - $previousOperating) / abs($previousOperating)) * 100
        : null;
    $changeInvesting = ($showComparison && abs($previousInvesting) >= 0.01)
        ? (($investing - $previousInvesting) / abs($previousInvesting)) * 100
        : null;
    $changeFinancing = ($showComparison && abs($previousFinancing) >= 0.01)
        ? (($financing - $previousFinancing) / abs($previousFinancing)) * 100
        : null;
    $changeNet = ($showComparison && abs($previousNetCashFlow) >= 0.01)
        ? (($netCashFlow - $previousNetCashFlow) / abs($previousNetCashFlow)) * 100
        : null;

    $categoryMeta = [
        'operating' => ['التدفقات من الأنشطة التشغيلية', 'OPERATING', 'green'],
        'investing' => ['التدفقات من الأنشطة الاستثمارية', 'INVESTING', 'red'],
        'financing' => ['التدفقات من الأنشطة التمويلية', 'FINANCING', 'purple'],
        'unclassified' => ['حركات تحتاج مراجعة', 'REVIEW', 'amber'],
    ];

    $chartValues = [
        ['key'=>'operating','label'=>'التشغيل','value'=>$operating,'class'=>'operating'],
        ['key'=>'investing','label'=>'الاستثمار','value'=>$investing,'class'=>'investing'],
        ['key'=>'financing','label'=>'التمويل','value'=>$financing,'class'=>'financing'],
        ['key'=>'net','label'=>'صافي التغير','value'=>$netCashFlow,'class'=>'net'],
    ];
    $chartMax = max(1, abs($operating), abs($investing), abs($financing), abs($netCashFlow));

    $trendMovements = array_reverse($movements ?? []);
    $trendByDate = [];
    $trendRunning = (float) $openingCash;
    foreach ($trendMovements as $trendMovement) {
        $trendRunning += (float) ($trendMovement['amount'] ?? 0);
        $trendDate = $trendMovement['date_iso'] ?? '';
        if ($trendDate !== '') {
            $trendByDate[$trendDate] = $trendRunning;
        }
    }
    if (empty($trendByDate)) {
        $trendByDate[$dateFrom] = (float) $openingCash;
        $trendByDate[$dateTo] = (float) $closingCash;
    } elseif (!array_key_exists($dateFrom, $trendByDate)) {
        $trendByDate = [$dateFrom => (float) $openingCash] + $trendByDate;
    }
    $trendByDate[$dateTo] = (float) $closingCash;
    $trendByDate = array_slice($trendByDate, -7, null, true);
    $trendDates = array_keys($trendByDate);
    $trendValues = array_values($trendByDate);
    $trendMin = min($trendValues);
    $trendMax = max($trendValues);
    if (abs($trendMax - $trendMin) < 0.01) {
        $trendMax += 1;
        $trendMin -= 1;
    }
    $svgW = 640;
    $svgH = 180;
    $padX = 24;
    $padY = 18;
    $usableW = $svgW - ($padX * 2);
    $usableH = $svgH - ($padY * 2);
    $trendPointsArr = [];
    $trendCount = count($trendValues);
    foreach ($trendValues as $i => $trendValue) {
        $x = $padX + (($trendCount <= 1 ? 0 : $i / ($trendCount - 1)) * $usableW);
        $y = $padY + (($trendMax - $trendValue) / ($trendMax - $trendMin) * $usableH);
        $trendPointsArr[] = round($x, 2) . ',' . round($y, 2);
    }
    $trendPoints = implode(' ', $trendPointsArr);
    $trendAreaPoints = $padX . ',' . ($svgH - $padY) . ' ' . $trendPoints . ' ' . ($svgW - $padX) . ',' . ($svgH - $padY);
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap');
*{box-sizing:border-box}
.fi-header,.fi-page-header{display:none!important}
.cf2,.cf2 *{font-family:'Cairo',sans-serif!important}
.cf2{
    --bg:#07101d;--panel:#0b1526;--panel2:#0f1b30;--line:#20314d;--line2:#182940;
    --text:#eef4ff;--muted:#7d90aa;--muted2:#566b86;--purple:#8b5cf6;--purple2:#a78bfa;
    --green:#39d4a6;--red:#fb5f72;--amber:#f7be4d;--blue:#52c8ff;
    direction:rtl;color:var(--text);width:100%;padding:2px 0 22px
}
.cf2 button,.cf2 input,.cf2 select{font-family:'Cairo',sans-serif!important}
.cf2-shell{width:100%;max-width:1580px;margin:0 auto}
.cf2-top{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:14px}
.cf2-title-wrap{display:flex;align-items:center;gap:12px}
.cf2-title-icon{width:46px;height:46px;border-radius:12px;background:linear-gradient(145deg,#6d28d9,#8b5cf6);display:grid;place-items:center;box-shadow:0 12px 30px rgba(139,92,246,.25);border:1px solid rgba(255,255,255,.1)}
.cf2-title-icon svg{width:24px;height:24px;stroke:#fff}
.cf2-kicker{font-size:9px;letter-spacing:1.4px;color:#8d78ee;font-weight:900;text-transform:uppercase}
.cf2-title{font-size:27px;font-weight:950;line-height:1.2;margin-top:3px}
.cf2-sub{font-size:11px;color:var(--muted);margin-top:4px}
.cf2-actions{display:flex;gap:8px;direction:ltr;align-items:center;flex-wrap:wrap}
.cf2-btn{height:38px;padding:0 15px;border-radius:9px;border:1px solid var(--line);background:#0d192b;color:#b9c6d7;font-size:10px;font-weight:900;cursor:pointer;transition:.18s;display:flex;align-items:center;gap:7px}
.cf2-btn:hover{transform:translateY(-1px);border-color:#4c3d75}.cf2-btn svg{width:15px;height:15px;stroke:currentColor}
.cf2-btn.purple{background:linear-gradient(135deg,#6d28d9,#8b5cf6);border-color:#8b5cf6;color:#fff}.cf2-btn.green{background:#0b2b26;border-color:#1d6656;color:#6ce1bf}.cf2-btn.ghost{background:#10192b}
.cf2-toolbar{display:flex;justify-content:space-between;align-items:end;gap:14px;margin-bottom:14px;padding:12px 13px;background:linear-gradient(180deg,#0b1627,#091321);border:1px solid var(--line2);border-radius:12px}
.cf2-filters{display:flex;gap:8px;align-items:end;flex-wrap:wrap}.cf2-field{min-width:135px}.cf2-field label{display:block;font-size:8px;color:var(--muted2);font-weight:800;margin:0 4px 5px}
.cf2-input,.cf2-select{height:36px;border:1px solid #243854;background:#101c31;color:#d9e3f2;border-radius:9px;padding:0 10px;outline:0;font-size:9px;min-width:135px}.cf2-input::-webkit-calendar-picker-indicator{filter:invert(1);opacity:.6}
.cf2-check{height:36px;border:1px solid #243854;background:#101c31;border-radius:9px;padding:0 10px;display:flex;align-items:center;gap:7px;font-size:8px;color:#8ea1ba;font-weight:800}.cf2-check input{accent-color:#8b5cf6}
.cf2-periods{display:flex;gap:6px;flex-wrap:wrap}.cf2-period{height:32px;padding:0 12px;border-radius:8px;border:1px solid #22344f;background:#0f1a2d;color:#6f829d;font-size:8px;font-weight:900;cursor:pointer}.cf2-period.active{background:#24184b;border-color:#7253d2;color:#c8baff}
.cf2-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}
.cf2-kpi{position:relative;height:145px;border:1px solid #273b59;border-radius:14px;overflow:hidden;background:#0d1728;isolation:isolate;box-shadow:0 12px 30px rgba(0,0,0,.11)}
.cf2-kpi img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:-3;filter:saturate(.85) contrast(1.08);opacity:.7}
.cf2-kpi:before{content:'';position:absolute;inset:0;z-index:-2;background:linear-gradient(90deg,rgba(5,12,23,.94) 4%,rgba(5,12,23,.72) 52%,rgba(5,12,23,.25) 100%)}
.cf2-kpi:after{content:'';position:absolute;inset:0;z-index:-1;background:linear-gradient(180deg,rgba(7,16,29,.08),rgba(7,16,29,.46))}
.cf2-kpi-body{padding:15px 16px;height:100%;display:flex;flex-direction:column;justify-content:center;max-width:72%}
.cf2-kpi-code{font-size:7px;letter-spacing:1.2px;color:#9a83ff;font-weight:950}.cf2-kpi-label{font-size:12px;font-weight:800;color:#edf3fb;margin-top:5px}.cf2-kpi-value{font-size:25px;font-weight:950;line-height:1.15;margin-top:7px;direction:ltr;text-align:right;white-space:nowrap}.cf2-kpi-value.pos{color:#fff}.cf2-kpi-value.neg{color:#fff}
.cf2-kpi-foot{font-size:8px;color:#abb7c8;margin-top:8px}.cf2-kpi-foot .up{color:var(--green);font-weight:900}.cf2-kpi-foot .down{color:var(--red);font-weight:900}
.cf2-main{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;align-items:stretch}.cf2-panel{background:linear-gradient(180deg,#0b1526,#091321);border:1px solid var(--line2);border-radius:14px;overflow:hidden;box-shadow:0 12px 28px rgba(0,0,0,.1)}
.cf2-panel-head{padding:13px 15px;border-bottom:1px solid #182941;display:flex;justify-content:space-between;align-items:center;gap:12px}.cf2-panel-title{font-size:14px;font-weight:950}.cf2-panel-sub{font-size:8px;color:var(--muted);margin-top:3px}.cf2-tag{font-size:7px;color:#9f8af7;border:1px solid #3b315d;background:#17162d;padding:5px 8px;border-radius:999px;font-weight:900}
.cf2-statement{padding:10px 13px 13px}.cf2-statement-head,.cf2-statement-row,.cf2-statement-section{display:grid;grid-template-columns:minmax(0,1fr) 115px 115px;align-items:center}.cf2-statement-head{height:34px;padding:0 11px;background:#121c31;border:1px solid #1f304a;border-radius:8px 8px 0 0;color:#7e90a9;font-size:8px;font-weight:900}.cf2-statement-head>div:not(:first-child){text-align:center}
.cf2-statement-section{min-height:32px;margin-top:7px;padding:0 11px;border-radius:7px;font-size:9px;font-weight:950;cursor:pointer}.cf2-statement-section.green{background:linear-gradient(90deg,rgba(30,161,121,.20),rgba(30,161,121,.06));border:1px solid rgba(57,212,166,.2)}.cf2-statement-section.red{background:linear-gradient(90deg,rgba(185,49,68,.22),rgba(185,49,68,.06));border:1px solid rgba(251,95,114,.2)}.cf2-statement-section.purple{background:linear-gradient(90deg,rgba(117,76,220,.24),rgba(117,76,220,.06));border:1px solid rgba(167,139,250,.22)}.cf2-statement-section.amber{background:linear-gradient(90deg,rgba(190,139,38,.2),rgba(190,139,38,.05));border:1px solid rgba(247,190,77,.2)}
.cf2-statement-section .amount,.cf2-statement-row .amount{text-align:center;direction:ltr;font-weight:900}.cf2-statement-section .current.green-text{color:var(--green)}.cf2-statement-section .current.red-text{color:var(--red)}.cf2-statement-section .current.purple-text{color:#b19aff}.cf2-statement-row{min-height:31px;padding:0 11px;border-bottom:1px solid #14243a;font-size:8px}.cf2-statement-row:hover{background:#0f1b2e}.cf2-row-name{color:#c5d0df}.cf2-row-code{display:inline-block;min-width:38px;color:#7f6cdc;font-weight:950}.cf2-prev{color:#889ab2;text-align:center;direction:ltr}.cf2-current{text-align:center;direction:ltr;color:#e9eff8;font-weight:850}.cf2-empty{padding:12px;text-align:center;color:#526882;font-size:8px}
.cf2-analysis{padding:12px 15px 15px}.cf2-chart-area{position:relative;height:340px;margin-top:4px;border-left:1px solid #263a56;border-bottom:1px solid #263a56;background-image:linear-gradient(to top,rgba(73,93,121,.16) 1px,transparent 1px);background-size:100% 20%;overflow:visible}.cf2-midline{position:absolute;left:0;right:0;top:50%;height:1px;background:#334b6d}.cf2-y-label{position:absolute;left:-4px;transform:translateX(-100%);font-size:8px;color:#73869f;direction:ltr}.cf2-y-label.top{top:-4px}.cf2-y-label.mid{top:calc(50% - 6px)}.cf2-y-label.bottom{bottom:-4px}.cf2-bars{height:100%;display:grid;grid-template-columns:repeat(4,1fr);gap:26px;padding:0 26px}.cf2-bar-col{position:relative;height:100%}.cf2-bar{position:absolute;left:19%;right:19%;border-radius:4px 4px 2px 2px;min-height:4px;box-shadow:0 8px 18px rgba(0,0,0,.18)}.cf2-bar.pos{bottom:50%}.cf2-bar.neg{top:50%}.cf2-bar.operating{background:linear-gradient(180deg,#8b5cf6,#6d28d9)}.cf2-bar.investing,.cf2-bar.financing{background:linear-gradient(180deg,#fb5f72,#c83c52)}.cf2-bar.net{background:linear-gradient(180deg,#a78bfa,#7c3aed)}.cf2-bar-value{position:absolute;left:0;right:0;text-align:center;font-size:10px;font-weight:950;direction:ltr;color:#f2f6ff;white-space:nowrap}.cf2-bar.pos .cf2-bar-value{top:-22px}.cf2-bar.neg .cf2-bar-value{bottom:-22px}.cf2-bar-label{position:absolute;bottom:-29px;left:0;right:0;text-align:center;font-size:9px;color:#c2cedf;font-weight:850}.cf2-chart-foot{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:41px;padding-top:10px;border-top:1px solid #18283e}.cf2-chart-foot-item span{display:block;color:#60738e;font-size:7px}.cf2-chart-foot-item strong{display:block;margin-top:3px;font-size:10px;direction:ltr;text-align:right}
.cf2-bottom{display:grid;grid-template-columns:1.25fr .72fr .95fr;gap:12px;margin-bottom:12px}.cf2-trend{padding:12px 15px 14px}.cf2-trend-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}.cf2-trend-value{font-size:16px;font-weight:950;direction:ltr}.cf2-trend svg{width:100%;height:180px;overflow:visible}.cf2-trend-grid{stroke:#1e304a;stroke-width:1}.cf2-trend-area{fill:url(#cf2Area)}.cf2-trend-line{fill:none;stroke:#8b5cf6;stroke-width:4;stroke-linecap:round;stroke-linejoin:round}.cf2-trend-dot{fill:#ddd4ff;stroke:#8b5cf6;stroke-width:3}.cf2-trend-labels{display:flex;justify-content:space-between;color:#5f738f;font-size:7px;margin-top:-2px;direction:ltr}
.cf2-cash{padding:11px 13px}.cf2-cash-row{display:grid;grid-template-columns:60px minmax(0,1fr) 115px;gap:7px;align-items:center;min-height:39px;border-bottom:1px solid #17263b;font-size:8px}.cf2-cash-row:last-child{border-bottom:0}.cf2-cash-code{color:#8874e8;font-weight:950}.cf2-cash-name{color:#c3cedd}.cf2-cash-bal{text-align:left;direction:ltr;font-weight:950}.cf2-cash-total{margin:9px 0 0;padding:9px 10px;background:linear-gradient(135deg,#4c1d95,#7c3aed);border-radius:8px;display:flex;justify-content:space-between;font-size:9px;font-weight:950}
.cf2-alerts{padding:10px 12px}.cf2-alert{display:grid;grid-template-columns:34px minmax(0,1fr) 32px;gap:9px;align-items:center;min-height:52px;padding:8px;border-bottom:1px solid #17263b}.cf2-alert:last-child{border-bottom:0}.cf2-alert-icon{width:31px;height:31px;border-radius:8px;display:grid;place-items:center}.cf2-alert-icon svg{width:17px;height:17px;stroke:currentColor}.cf2-alert-icon.good{background:#0c2a24;color:#4bd8b1}.cf2-alert-icon.warn{background:#2a2414;color:#f7c657}.cf2-alert-icon.bad{background:#2d171e;color:#ff7488}.cf2-alert-title{font-size:9px;font-weight:950}.cf2-alert-text{font-size:7px;color:#72849d;margin-top:3px;line-height:1.6}.cf2-alert-count{width:24px;height:24px;border-radius:50%;display:grid;place-items:center;background:#17334b;color:#d7e4f4;font-size:9px;font-weight:950}
.cf2-recon{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;padding:10px 13px}.cf2-recon-item{background:#0f1b2e;border:1px solid #1d304a;border-radius:9px;padding:9px}.cf2-recon-item span{display:block;font-size:7px;color:#61748f}.cf2-recon-item strong{display:block;margin-top:4px;font-size:10px;direction:ltr;text-align:right}.cf2-recon-item.status{background:#0d2622;border-color:#1f6657}.cf2-recon-item.status.bad{background:#2a171d;border-color:#6e3542}.cf2-recon-item.status strong{color:var(--green)}.cf2-recon-item.status.bad strong{color:var(--red)}
.cf2-activity{margin-top:12px}.cf2-activity-head,.cf2-activity-row{display:grid;grid-template-columns:85px 95px minmax(220px,1fr) 110px 115px;gap:8px;align-items:center}.cf2-activity-head{min-height:34px;padding:0 13px;background:#101b2e;border-block:1px solid #1b2c45;color:#667a95;font-size:7px;font-weight:950}.cf2-activity-row{min-height:42px;padding:7px 13px;border-bottom:1px solid #15253b;font-size:8px}.cf2-activity-row:hover{background:#0f1b2e}.cf2-ref{color:#9d88f6;font-weight:950;text-decoration:none}.cf2-desc{color:#bdc9d9}.cf2-category{font-size:7px;font-weight:900;color:#8a9ab0}.cf2-flow{text-align:left;direction:ltr;font-weight:950}.cf2-in{color:var(--green)}.cf2-out{color:var(--red)}
.cf2-drawer{position:fixed;inset:0;z-index:1200;background:rgba(2,7,14,.76);backdrop-filter:blur(6px);display:flex;justify-content:flex-start}.cf2-drawer-panel{width:min(840px,95vw);height:100%;background:#091422;border-right:1px solid #2b3d5b;box-shadow:22px 0 80px rgba(0,0,0,.55);overflow:auto}.cf2-drawer-head{position:sticky;top:0;z-index:4;padding:15px 16px;background:#0d1829;border-bottom:1px solid #21314a;display:flex;justify-content:space-between}.cf2-drawer-title{font-size:15px;font-weight:950}.cf2-drawer-sub{font-size:8px;color:#687b94;margin-top:4px}.cf2-close{width:32px;height:32px;border-radius:8px;border:1px solid #2b3e5b;background:#101c30;color:#91a2bb;cursor:pointer}.cf2-drawer-row{display:grid;grid-template-columns:80px 105px 105px minmax(180px,1fr) 110px;gap:8px;align-items:center;min-width:720px;padding:10px 13px;border-bottom:1px solid #17263b;font-size:8px}.cf2-drawer-row.head{position:sticky;top:63px;background:#0d1829;color:#657892;font-size:7px;font-weight:950;z-index:3}
@media(max-width:1280px){.cf2-kpis{grid-template-columns:repeat(2,1fr)}.cf2-main{grid-template-columns:1fr}.cf2-bottom{grid-template-columns:1fr 1fr}.cf2-alert-panel{grid-column:1/-1}.cf2-toolbar{align-items:flex-start;flex-direction:column}.cf2-periods{order:2}.cf2-filters{width:100%}}
@media(max-width:760px){.cf2-top{align-items:flex-start;flex-direction:column}.cf2-actions{direction:rtl}.cf2-kpis,.cf2-bottom{grid-template-columns:1fr}.cf2-title{font-size:23px}.cf2-kpi{height:132px}.cf2-main{display:block}.cf2-panel{margin-bottom:10px}.cf2-filters{display:grid;grid-template-columns:1fr 1fr}.cf2-field,.cf2-input,.cf2-select{min-width:0;width:100%}.cf2-check{grid-column:1/-1}.cf2-recon{grid-template-columns:1fr 1fr}.cf2-activity{overflow:auto}.cf2-activity-head,.cf2-activity-row{min-width:720px}.cf2-statement-head,.cf2-statement-row,.cf2-statement-section{grid-template-columns:minmax(0,1fr) 90px 90px}.cf2-bars{gap:8px;padding:0 4px}}
</style>

<div class="cf2">
<div class="cf2-shell">
    <div class="cf2-top">
        <div class="cf2-title-wrap">
            <div class="cf2-title-icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M6 6l3 12m9-12-3 12M5 18h8m-4-8H5l-2 5h8l-2-5Zm10 0h-4l-2 5h8l-2-5Z"/></svg>
            </div>
            <div>
                <div class="cf2-kicker">FINANCIAL STATEMENTS · CASH FLOW</div>
                <div class="cf2-title">قائمة التدفقات النقدية</div>
                <div class="cf2-sub">Cash Flow Statement · حركة النقدية والبنوك من القيود المرحلة فقط</div>
            </div>
        </div>

        @if($loaded)
        <div class="cf2-actions">
            <button wire:click="exportExcel" class="cf2-btn purple">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/></svg>
                تصدير Excel
            </button>
            <button wire:click="exportPdf" class="cf2-btn green">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/></svg>
                تصدير PDF
            </button>
            <button type="button" class="cf2-btn ghost" onclick="document.getElementById('cf2-statement')?.scrollIntoView({behavior:'smooth',block:'start'})">عرض تفصيلي</button>
        </div>
        @endif
    </div>

    <div class="cf2-toolbar">
        <div class="cf2-filters">
            <div class="cf2-field"><label>من تاريخ</label><input wire:model="dateFrom" type="date" class="cf2-input"></div>
            <div class="cf2-field"><label>إلى تاريخ</label><input wire:model="dateTo" type="date" class="cf2-input"></div>
            <div class="cf2-field"><label>طريقة العرض</label><select wire:model="viewMode" class="cf2-select"><option value="detailed">تفصيلي</option><option value="summary">ملخص</option></select></div>
            <label class="cf2-check"><input type="checkbox" wire:model="showComparison"> مقارنة بنفس الفترة من العام السابق</label>
            <button wire:click="loadReport" class="cf2-btn purple">عرض التقرير</button>
        </div>
        <div class="cf2-periods">
            <button wire:click="setPeriod('today')" class="cf2-period {{ $quickPeriod === 'today' ? 'active' : '' }}">اليوم</button>
            <button wire:click="setPeriod('month')" class="cf2-period {{ $quickPeriod === 'month' ? 'active' : '' }}">هذا الشهر</button>
            <button wire:click="setPeriod('quarter')" class="cf2-period {{ $quickPeriod === 'quarter' ? 'active' : '' }}">الربع الحالي</button>
            <button wire:click="setPeriod('year')" class="cf2-period {{ $quickPeriod === 'year' ? 'active' : '' }}">السنة الحالية</button>
        </div>
    </div>

    <div class="cf2-kpis">
        <div class="cf2-kpi">
            <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=900&q=82" alt="" loading="lazy">
            <div class="cf2-kpi-body">
                <div class="cf2-kpi-code">OPERATING</div><div class="cf2-kpi-label">التدفق من التشغيل</div>
                <div class="cf2-kpi-value {{ $operating >= 0 ? 'pos':'neg' }}">{{ $operating >= 0 ? '+' : '' }}{{ number_format($operating,2) }} <span style="font-size:11px">ر.س</span></div>
                <div class="cf2-kpi-foot">@if($changeOperating !== null)<span class="{{ $changeOperating >= 0 ? 'up':'down' }}">{{ $changeOperating >= 0 ? '▲':'▼' }} {{ number_format(abs($changeOperating),1) }}%</span> مقارنة بالفترة السابقة @else من الأنشطة التشغيلية @endif</div>
            </div>
        </div>
        <div class="cf2-kpi">
            <img src="https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&w=900&q=82" alt="" loading="lazy">
            <div class="cf2-kpi-body">
                <div class="cf2-kpi-code">INVESTING</div><div class="cf2-kpi-label">التدفق من الاستثمار</div>
                <div class="cf2-kpi-value">{{ $investing >= 0 ? '+' : '' }}{{ number_format($investing,2) }} <span style="font-size:11px">ر.س</span></div>
                <div class="cf2-kpi-foot">@if($changeInvesting !== null)<span class="{{ $changeInvesting >= 0 ? 'up':'down' }}">{{ $changeInvesting >= 0 ? '▲':'▼' }} {{ number_format(abs($changeInvesting),1) }}%</span> مقارنة بالفترة السابقة @else شراء وبيع الأصول @endif</div>
            </div>
        </div>
        <div class="cf2-kpi">
            <img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=82" alt="" loading="lazy">
            <div class="cf2-kpi-body">
                <div class="cf2-kpi-code">FINANCING</div><div class="cf2-kpi-label">التدفق من التمويل</div>
                <div class="cf2-kpi-value">{{ $financing >= 0 ? '+' : '' }}{{ number_format($financing,2) }} <span style="font-size:11px">ر.س</span></div>
                <div class="cf2-kpi-foot">@if($changeFinancing !== null)<span class="{{ $changeFinancing >= 0 ? 'up':'down' }}">{{ $changeFinancing >= 0 ? '▲':'▼' }} {{ number_format(abs($changeFinancing),1) }}%</span> مقارنة بالفترة السابقة @else قروض ورأس المال @endif</div>
            </div>
        </div>
        <div class="cf2-kpi">
            <img src="https://images.unsplash.com/photo-1579621970795-87facc2f976d?auto=format&fit=crop&w=900&q=82" alt="" loading="lazy">
            <div class="cf2-kpi-body">
                <div class="cf2-kpi-code">NET CHANGE</div><div class="cf2-kpi-label">صافي التغير في النقدية</div>
                <div class="cf2-kpi-value">{{ $netCashFlow >= 0 ? '+' : '' }}{{ number_format($netCashFlow,2) }} <span style="font-size:11px">ر.س</span></div>
                <div class="cf2-kpi-foot">@if($changeNet !== null)<span class="{{ $changeNet >= 0 ? 'up':'down' }}">{{ $changeNet >= 0 ? '▲':'▼' }} {{ number_format(abs($changeNet),1) }}%</span> مقارنة بالفترة السابقة @else تشغيل + استثمار + تمويل @endif</div>
            </div>
        </div>
    </div>

    <div class="cf2-main" id="cf2-statement">
        <div class="cf2-panel">
            <div class="cf2-panel-head">
                <div><div class="cf2-panel-title">قائمة التدفقات النقدية</div><div class="cf2-panel-sub">الفترة الحالية مقابل الفترة السابقة · Direct Method</div></div>
                <span class="cf2-tag">STATEMENT</span>
            </div>
            <div class="cf2-statement">
                <div class="cf2-statement-head"><div>البيان</div><div>الفترة الحالية</div><div>الفترة السابقة</div></div>
                @foreach(['operating','investing','financing'] as $category)
                    @php
                        $currentTotal = $$category;
                        $prevVar = 'previous'.ucfirst($category);
                        $previousTotal = $$prevVar;
                        $rows = $grouped[$category] ?? [];
                        $textClass = $category === 'operating' ? 'green-text' : ($category === 'investing' ? 'red-text' : 'purple-text');
                    @endphp
                    <div class="cf2-statement-section {{ $categoryMeta[$category][2] }}" wire:click="openCategory('{{ $category }}')">
                        <div>{{ $categoryMeta[$category][0] }}</div>
                        <div class="amount current {{ $textClass }}">{{ number_format($currentTotal,2) }}</div>
                        <div class="amount prev">{{ $showComparison ? number_format($previousTotal,2) : '-' }}</div>
                    </div>
                    @if($viewMode === 'detailed')
                        @forelse($rows as $row)
                            <div class="cf2-statement-row">
                                <div class="cf2-row-name"><span class="cf2-row-code">{{ $row['code'] ?: '—' }}</span>{{ $row['name'] }}</div>
                                <div class="cf2-current">{{ number_format($row['amount'],2) }}</div>
                                <div class="cf2-prev">{{ $row['count'] }} حركة</div>
                            </div>
                        @empty
                            <div class="cf2-empty">لا توجد حركة نقدية مصنفة في هذا القسم خلال الفترة.</div>
                        @endforelse
                    @endif
                @endforeach
                <div class="cf2-statement-section green">
                    <div>صافي التغير في النقدية</div>
                    <div class="amount current green-text">{{ number_format($netCashFlow,2) }}</div>
                    <div class="amount prev">{{ $showComparison ? number_format($previousNetCashFlow,2) : '-' }}</div>
                </div>
                @if(abs($unclassified) >= 0.01 || $unclassifiedCount > 0)
                    <div class="cf2-statement-section amber" wire:click="openCategory('unclassified')">
                        <div>حركات تحتاج مراجعة وتصنيف</div><div class="amount current">{{ number_format($unclassified,2) }}</div><div class="amount prev">{{ $unclassifiedCount }} حركة</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="cf2-panel">
            <div class="cf2-panel-head">
                <div><div class="cf2-panel-title">تحليل التدفقات النقدية</div><div class="cf2-panel-sub">صافي أثر التشغيل والاستثمار والتمويل على النقدية</div></div>
                <span class="cf2-tag">CASH FLOW</span>
            </div>
            <div class="cf2-analysis">
                <div class="cf2-chart-area">
                    <div class="cf2-y-label top">+{{ number_format($chartMax,0) }}</div>
                    <div class="cf2-y-label mid">0</div>
                    <div class="cf2-y-label bottom">-{{ number_format($chartMax,0) }}</div>
                    <div class="cf2-midline"></div>
                    <div class="cf2-bars">
                        @foreach($chartValues as $bar)
                            @php $height = max(2.5, (abs($bar['value']) / $chartMax) * 43); @endphp
                            <div class="cf2-bar-col">
                                <div class="cf2-bar {{ $bar['class'] }} {{ $bar['value'] >= 0 ? 'pos':'neg' }}" style="height:{{ $height }}%">
                                    <div class="cf2-bar-value">{{ $bar['value'] >= 0 ? '+' : '' }}{{ number_format($bar['value'],0) }}</div>
                                </div>
                                <div class="cf2-bar-label">{{ $bar['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="cf2-chart-foot">
                    <div class="cf2-chart-foot-item"><span>رصيد أول الفترة</span><strong>{{ number_format($openingCash,2) }} ر.س</strong></div>
                    <div class="cf2-chart-foot-item"><span>التغير الفعلي</span><strong class="{{ $actualCashChange >= 0 ? 'cf2-in':'cf2-out' }}">{{ number_format($actualCashChange,2) }} ر.س</strong></div>
                    <div class="cf2-chart-foot-item"><span>تحويلات داخلية مستبعدة</span><strong>{{ number_format($internalTransferVolume,2) }} ر.س</strong></div>
                    <div class="cf2-chart-foot-item"><span>رصيد آخر الفترة</span><strong>{{ number_format($closingCash,2) }} ر.س</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="cf2-bottom">
        <div class="cf2-panel">
            <div class="cf2-panel-head"><div><div class="cf2-panel-title">تطور الرصيد النقدي</div><div class="cf2-panel-sub">التغير التراكمي خلال الفترة المحددة</div></div><span class="cf2-tag">TREND</span></div>
            <div class="cf2-trend">
                <div class="cf2-trend-top"><span style="font-size:8px;color:#6d8099">Closing Cash</span><strong class="cf2-trend-value">{{ number_format($closingCash,2) }} ر.س</strong></div>
                <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" preserveAspectRatio="none" aria-label="Cash trend">
                    <defs><linearGradient id="cf2Area" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#8b5cf6" stop-opacity=".28"/><stop offset="100%" stop-color="#8b5cf6" stop-opacity="0"/></linearGradient></defs>
                    <line class="cf2-trend-grid" x1="0" y1="45" x2="640" y2="45"/><line class="cf2-trend-grid" x1="0" y1="90" x2="640" y2="90"/><line class="cf2-trend-grid" x1="0" y1="135" x2="640" y2="135"/>
                    <polygon class="cf2-trend-area" points="{{ $trendAreaPoints }}"/>
                    <polyline class="cf2-trend-line" points="{{ $trendPoints }}"/>
                    @foreach($trendPointsArr as $point)
                        @php [$px,$py] = explode(',', $point); @endphp
                        <circle class="cf2-trend-dot" cx="{{ $px }}" cy="{{ $py }}" r="4"/>
                    @endforeach
                </svg>
                <div class="cf2-trend-labels">
                    @foreach($trendDates as $trendDate)<span>{{ date('d/m', strtotime($trendDate)) }}</span>@endforeach
                </div>
            </div>
        </div>

        <div class="cf2-panel">
            <div class="cf2-panel-head"><div><div class="cf2-panel-title">رصيد النقدية</div><div class="cf2-panel-sub">Cash & Cash Equivalents</div></div><span class="cf2-tag">GL</span></div>
            <div class="cf2-cash">
                @forelse($cashPositions as $row)
                    <div class="cf2-cash-row"><div class="cf2-cash-code">{{ $row['code'] }}</div><div class="cf2-cash-name">{{ $row['name'] }}</div><div class="cf2-cash-bal">{{ number_format($row['balance'],2) }}</div></div>
                @empty
                    <div class="cf2-empty">لا توجد حسابات نقدية معرفة.</div>
                @endforelse
                <div class="cf2-cash-total"><span>الإجمالي</span><strong>{{ number_format($closingCash,2) }} ر.س</strong></div>
            </div>
        </div>

        <div class="cf2-panel cf2-alert-panel">
            <div class="cf2-panel-head"><div><div class="cf2-panel-title">تنبيهات وتوصيات</div><div class="cf2-panel-sub">Controls على جودة التقرير</div></div><span class="cf2-tag">CHECKS</span></div>
            <div class="cf2-alerts">
                <div class="cf2-alert">
                    <div class="cf2-alert-icon {{ $isReconciled ? 'good':'bad' }}"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4L19 6"/></svg></div>
                    <div><div class="cf2-alert-title">{{ $isReconciled ? 'التسوية سليمة' : 'فرق تسوية يحتاج مراجعة' }}</div><div class="cf2-alert-text">الفرق الحالي {{ number_format($reconciliationDifference,2) }} ر.س.</div></div>
                    <div class="cf2-alert-count">{{ $isReconciled ? '0':'1' }}</div>
                </div>
                <div class="cf2-alert">
                    <div class="cf2-alert-icon {{ $unclassifiedCount === 0 ? 'good':'warn' }}"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 4.6 2.7 18a2 2 0 0 0 1.74 3h15.12a2 2 0 0 0 1.74-3L13.7 4.6a2 2 0 0 0-3.4 0Z"/></svg></div>
                    <div><div class="cf2-alert-title">الحركات غير المصنفة</div><div class="cf2-alert-text">أي حركة لا نعرف طرفها تظهر هنا بدل ما تختفي من القائمة.</div></div>
                    <div class="cf2-alert-count">{{ $unclassifiedCount }}</div>
                </div>
                <div class="cf2-alert">
                    <div class="cf2-alert-icon good"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h11m0 0-3-3m3 3-3 3M17 17H6m0 0 3 3m-3-3 3-3"/></svg></div>
                    <div><div class="cf2-alert-title">التحويلات الداخلية مستبعدة</div><div class="cf2-alert-text">{{ $internalTransferCount }} قيد بين حسابات النقدية لم يؤثر على صافي Cash Flow.</div></div>
                    <div class="cf2-alert-count">{{ $internalTransferCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="cf2-panel">
        <div class="cf2-panel-head"><div><div class="cf2-panel-title">تسوية النقدية</div><div class="cf2-panel-sub">Opening Cash + Classified Cash Flow = Closing Cash</div></div><span class="cf2-tag">RECONCILIATION</span></div>
        <div class="cf2-recon">
            <div class="cf2-recon-item"><span>رصيد أول الفترة</span><strong>{{ number_format($openingCash,2) }} ر.س</strong></div>
            <div class="cf2-recon-item"><span>Operating</span><strong class="{{ $operating >= 0 ? 'cf2-in':'cf2-out' }}">{{ number_format($operating,2) }}</strong></div>
            <div class="cf2-recon-item"><span>Investing + Financing</span><strong>{{ number_format($investing + $financing,2) }}</strong></div>
            <div class="cf2-recon-item"><span>رصيد آخر الفترة</span><strong>{{ number_format($closingCash,2) }} ر.س</strong></div>
            <div class="cf2-recon-item status {{ $isReconciled ? '':'bad' }}"><span>النتيجة</span><strong>{{ $isReconciled ? 'متسوية · 0.00' : 'فرق '.number_format($reconciliationDifference,2) }}</strong></div>
        </div>
    </div>

    <div class="cf2-panel cf2-activity">
        <div class="cf2-panel-head"><div><div class="cf2-panel-title">أحدث الحركات النقدية</div><div class="cf2-panel-sub">آخر القيود المؤثرة على Cash & Cash Equivalents</div></div><span class="cf2-tag">{{ $cashEntriesCount }} CASH ENTRIES</span></div>
        <div class="cf2-activity-head"><div>التاريخ</div><div>القيد</div><div>الطرف المقابل / البيان</div><div>التصنيف</div><div>التدفق</div></div>
        @forelse(array_slice($movements,0,8) as $row)
            <div class="cf2-activity-row">
                <div>{{ $row['date'] }}</div>
                <div><a class="cf2-ref" href="{{ \App\Filament\Admin\Resources\JournalEntries\JournalEntryResource::getUrl('view',['record'=>$row['journal_entry_id']]) }}">{{ $row['entry_number'] }}</a></div>
                <div class="cf2-desc">{{ $row['account_code'] }} · {{ $row['account_name'] }}<br><span style="color:#60738d;font-size:7px">{{ $row['description'] }}</span></div>
                <div class="cf2-category">{{ $categoryMeta[$row['category']][1] ?? 'REVIEW' }}</div>
                <div class="cf2-flow {{ $row['amount'] >= 0 ? 'cf2-in':'cf2-out' }}">{{ $row['amount'] >= 0 ? '+' : '' }}{{ number_format($row['amount'],2) }}</div>
            </div>
        @empty
            <div class="cf2-empty">لا توجد حركات نقدية خلال الفترة المحددة.</div>
        @endforelse
    </div>
</div>
</div>

@if($selectedCategory)
<div class="cf2-drawer" wire:key="cash-flow-category-{{ $selectedCategory }}">
    <div class="cf2-drawer-panel">
        <div class="cf2-drawer-head">
            <div><div class="cf2-drawer-title">{{ $selectedCategoryLabel }}</div><div class="cf2-drawer-sub">Drill-down إلى القيود المرحلة المؤثرة على التدفق النقدي</div></div>
            <button wire:click="closeCategory" class="cf2-close">×</button>
        </div>
        <div style="overflow:auto">
            <div class="cf2-drawer-row head"><div>التاريخ</div><div>رقم القيد</div><div>الحساب</div><div>البيان</div><div>التدفق</div></div>
            @forelse($selectedMovements as $row)
                <div class="cf2-drawer-row">
                    <div>{{ $row['date'] }}</div>
                    <div><a class="cf2-ref" href="{{ \App\Filament\Admin\Resources\JournalEntries\JournalEntryResource::getUrl('view',['record'=>$row['journal_entry_id']]) }}">{{ $row['entry_number'] }}</a></div>
                    <div><span class="cf2-row-code">{{ $row['account_code'] ?: '—' }}</span> {{ $row['account_name'] }}</div>
                    <div class="cf2-desc">{{ $row['description'] }}</div>
                    <div class="cf2-flow {{ $row['amount'] >= 0 ? 'cf2-in':'cf2-out' }}">{{ $row['amount'] >= 0 ? '+' : '' }}{{ number_format($row['amount'],2) }} ر.س</div>
                </div>
            @empty
                <div class="cf2-empty">لا توجد حركات.</div>
            @endforelse
        </div>
    </div>
</div>
@endif
</x-filament-panels::page>
