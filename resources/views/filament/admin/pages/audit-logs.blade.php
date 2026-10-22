<x-filament-panels::page>
@php
    $logs = $this->getLogs();
    $stats = $this->getStats();
    $modelOptions = $this->getModelOptions();
    $eventOptions = $this->getEventOptions();
    $selectedLog = $this->getSelectedLog();
@endphp

<style>
    .fi-header{display:none!important}
    .orion-audit,.orion-audit *{box-sizing:border-box;font-family:'Cairo',sans-serif}
    .orion-audit{
        --oa-bg:#08111f;--oa-panel:#0c1727;--oa-panel2:#101c2f;--oa-panel3:#121f35;
        --oa-border:#22324c;--oa-border2:#192940;--oa-text:#f4f7fb;--oa-muted:#71839d;
        --oa-muted2:#4f627d;--oa-purple:#8b5cf6;--oa-purple2:#a78bfa;--oa-green:#34d399;
        --oa-blue:#60a5fa;--oa-red:#fb7185;--oa-amber:#fbbf24;--oa-cyan:#67e8f9;
        direction:rtl;color:var(--oa-text);width:100%;padding-bottom:24px
    }
    .oa-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:16px}
    .oa-title-wrap{display:flex;align-items:center;gap:13px}
    .oa-title-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;color:#c4b5fd;background:linear-gradient(145deg,rgba(124,58,237,.35),rgba(76,29,149,.12));border:1px solid rgba(139,92,246,.45);box-shadow:0 14px 35px rgba(76,29,149,.18)}
    .oa-title-icon svg{width:24px;height:24px}
    .oa-kicker{font-size:9px;letter-spacing:1.8px;color:#7968df;font-weight:950;margin-bottom:4px}
    .oa-title{font-size:27px;line-height:1.15;font-weight:950;margin:0;color:#f5f7fb}
    .oa-subtitle{font-size:10px;color:#6f819b;margin-top:5px;max-width:680px}
    .oa-head-actions{display:flex;gap:7px;align-items:center;flex-wrap:wrap}
    .oa-action{height:36px;border-radius:9px;padding:0 12px;display:inline-flex;align-items:center;gap:7px;border:1px solid #263853;background:#0d1929;color:#9aacbf;font-size:9px;font-weight:900;cursor:pointer;transition:.18s}
    .oa-action:hover{border-color:#5742a6;color:#d5caff;background:#121d32}
    .oa-action svg{width:14px;height:14px}
    .oa-action.primary{border-color:rgba(139,92,246,.45);background:linear-gradient(135deg,#6d43e0,#8456ef);color:white}
    .oa-live{display:flex;align-items:center;gap:6px;border:1px solid rgba(52,211,153,.18);background:rgba(52,211,153,.055);color:#74ddb9;border-radius:999px;padding:7px 10px;font-size:8px;font-weight:900}
    .oa-live-dot{width:6px;height:6px;border-radius:50%;background:#34d399;box-shadow:0 0 0 4px rgba(52,211,153,.08)}
    .oa-kpis{display:grid;grid-template-columns:repeat(5,1fr);gap:9px;margin-bottom:11px}
    .oa-kpi{min-height:101px;border:1px solid #21324d;border-radius:13px;background:linear-gradient(145deg,#0f1a2c,#0a1422);padding:13px 14px;position:relative;overflow:hidden}
    .oa-kpi:after{content:"";position:absolute;width:85px;height:85px;border-radius:50%;left:-31px;top:-35px;background:radial-gradient(circle,rgba(139,92,246,.16),transparent 68%)}
    .oa-kpi-code{font-size:7px;color:#566b88;font-weight:950;letter-spacing:1px}
    .oa-kpi-label{font-size:9px;color:#7b8da5;font-weight:850;margin-top:4px}
    .oa-kpi-value{font-size:24px;line-height:1.1;font-weight:950;margin-top:9px;direction:ltr;text-align:right}
    .oa-kpi-foot{font-size:7px;color:#52657e;margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .oa-green{color:var(--oa-green)}.oa-blue{color:var(--oa-blue)}.oa-purple{color:#b6a5ff}.oa-red{color:var(--oa-red)}.oa-amber{color:var(--oa-amber)}
    .oa-filter-panel{border:1px solid #22334f;border-radius:13px;background:linear-gradient(145deg,#0d1728,#0a1422);overflow:hidden;margin-bottom:11px}
    .oa-filter-top{display:grid;grid-template-columns:minmax(260px,1.6fr) repeat(2,minmax(150px,.55fr)) auto;gap:8px;padding:10px}
    .oa-search-wrap{position:relative}.oa-search-wrap svg{position:absolute;right:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#536883}
    .oa-input,.oa-select{width:100%;height:38px;border-radius:9px;border:1px solid #273a57;background:#0b1627;color:#d7e0eb;padding:0 11px;font-size:9px;font-weight:700;outline:none}
    .oa-search{padding-right:36px}.oa-input:focus,.oa-select:focus{border-color:#6f52d9;box-shadow:0 0 0 3px rgba(139,92,246,.07)}
    .oa-filter-bottom{display:flex;gap:8px;align-items:end;padding:0 10px 10px;flex-wrap:wrap}
    .oa-field{min-width:145px}.oa-field label{display:block;color:#526681;font-size:7px;font-weight:900;margin:0 3px 4px}
    .oa-filter-spacer{flex:1}
    .oa-segment{display:flex;border:1px solid #263954;border-radius:9px;padding:3px;background:#091422;height:38px}
    .oa-segment button{border:0;background:transparent;color:#61748e;min-width:60px;border-radius:6px;font-size:8px;font-weight:900;cursor:pointer}
    .oa-segment button.active{background:#192743;color:#c7bcff;box-shadow:inset 0 0 0 1px rgba(139,92,246,.22)}
    .oa-reset{height:38px;border-radius:9px;border:1px solid #263954;background:#0b1627;color:#73859e;padding:0 11px;font-size:8px;font-weight:900;cursor:pointer}
    .oa-panel{border:1px solid #21324c;border-radius:14px;background:linear-gradient(145deg,#0c1626,#091320);overflow:hidden}
    .oa-panel-head{min-height:48px;padding:10px 13px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #1a2a41}
    .oa-panel-title{font-size:10px;font-weight:950}.oa-panel-sub{font-size:7px;color:#576b86;margin-top:2px}
    .oa-panel-count{border:1px solid rgba(139,92,246,.18);background:rgba(139,92,246,.06);color:#9d8cf1;border-radius:7px;padding:5px 8px;font-size:7px;font-weight:950}
    .oa-table-wrap{overflow:auto}
    .oa-table{width:100%;border-collapse:collapse;min-width:1050px}
    .oa-table th{height:37px;text-align:right;padding:0 11px;background:#0c1523;color:#576a84;border-bottom:1px solid #1a2a40;font-size:7px;font-weight:950;white-space:nowrap}
    .oa-table td{padding:9px 11px;border-bottom:1px solid #14243a;vertical-align:top;font-size:8px;color:#a9b6c8}
    .oa-table tbody tr{transition:.16s}.oa-table tbody tr:hover{background:#0f1b2d}
    .oa-time{white-space:nowrap}.oa-date{font-size:8px;color:#9aaabd;font-weight:850}.oa-clock{font-size:7px;color:#556b87;margin-top:2px;direction:ltr;text-align:right}.oa-ago{font-size:6px;color:#3d526d;margin-top:2px}
    .oa-user{display:flex;align-items:center;gap:8px}.oa-avatar{width:29px;height:29px;border-radius:8px;display:grid;place-items:center;background:linear-gradient(145deg,#1d2d48,#111d30);border:1px solid #2b4061;color:#b8a7ff;font-size:9px;font-weight:950;flex:none}.oa-user-name{font-weight:900;color:#dce4ee;font-size:8px}.oa-user-sub{font-size:6px;color:#50647f;margin-top:2px}
    .oa-badge{display:inline-flex;align-items:center;gap:5px;padding:5px 7px;border-radius:7px;font-size:7px;font-weight:950;white-space:nowrap;border:1px solid transparent}.oa-badge svg{width:11px;height:11px}
    .oa-badge.created{background:rgba(52,211,153,.08);color:#52ddb0;border-color:rgba(52,211,153,.16)}
    .oa-badge.updated{background:rgba(96,165,250,.08);color:#72b4fa;border-color:rgba(96,165,250,.16)}
    .oa-badge.deleted{background:rgba(251,113,133,.08);color:#ff8394;border-color:rgba(251,113,133,.16)}
    .oa-badge.restored{background:rgba(167,139,250,.09);color:#b6a3fb;border-color:rgba(167,139,250,.18)}
    .oa-badge.auth{background:rgba(251,191,36,.07);color:#f4c85b;border-color:rgba(251,191,36,.15)}
    .oa-badge.session{background:rgba(103,232,249,.07);color:#77dfea;border-color:rgba(103,232,249,.15)}
    .oa-badge.default{background:#101d2f;color:#8496ad;border-color:#233854}
    .oa-model{font-size:8px;color:#8ea0b7;font-weight:850;white-space:nowrap}.oa-model-code{font-size:6px;color:#465b76;margin-top:2px;direction:ltr;text-align:right}
    .oa-record{color:#a78bfa;font-weight:950;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;white-space:nowrap}
    .oa-changes{min-width:270px}.oa-change{display:grid;grid-template-columns:78px minmax(0,1fr);gap:7px;padding:4px 0;border-bottom:1px dashed #1b2c44}.oa-change:last-child{border-bottom:0}.oa-change-label{font-size:6px;color:#50647f;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.oa-diff{font-size:7px;direction:ltr;text-align:right;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.oa-old{color:#df7182;text-decoration:line-through;opacity:.82}.oa-arrow{color:#3f5571;padding:0 4px}.oa-new{color:#4ed9ac}.oa-more{font-size:6px;color:#8c7be4;margin-top:4px;font-weight:900}
    .oa-ip{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:7px;color:#536984;direction:ltr;white-space:nowrap}
    .oa-details-btn{width:30px;height:30px;border-radius:8px;display:grid;place-items:center;border:1px solid #263a57;background:#101b2d;color:#8673df;cursor:pointer}.oa-details-btn:hover{border-color:#694bd0;color:#c5b9ff;background:#151f34}.oa-details-btn svg{width:14px;height:14px}
    .oa-empty{padding:70px 20px;text-align:center}.oa-empty-icon{width:52px;height:52px;border-radius:14px;margin:0 auto 10px;display:grid;place-items:center;background:#101c2e;border:1px solid #22354f;color:#546b88}.oa-empty-icon svg{width:24px}.oa-empty-title{font-size:11px;font-weight:950;color:#8798ad}.oa-empty-sub{font-size:8px;color:#4f627c;margin-top:4px}
    .oa-pager{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border-top:1px solid #1a2a40;background:#0a1421}.oa-pager-info{font-size:7px;color:#566a84}.oa-pager-actions{display:flex;gap:6px}.oa-page-btn{height:30px;border-radius:7px;padding:0 10px;border:1px solid #263954;background:#101b2c;color:#8979db;font-size:7px;font-weight:900;cursor:pointer}.oa-page-btn:disabled{opacity:.35;cursor:not-allowed}
    .oa-timeline{padding:10px 13px 14px}.oa-tl-row{display:grid;grid-template-columns:92px 18px minmax(0,1fr);gap:9px;position:relative;min-height:88px}.oa-tl-time{padding-top:3px;text-align:left;direction:ltr;color:#60748d;font-size:7px}.oa-tl-line{position:relative}.oa-tl-line:before{content:"";position:absolute;top:14px;bottom:-9px;right:8px;width:1px;background:#20324c}.oa-tl-row:last-child .oa-tl-line:before{display:none}.oa-tl-dot{position:absolute;top:6px;right:2px;width:13px;height:13px;border-radius:50%;background:#17253a;border:3px solid #8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.08)}.oa-tl-card{border:1px solid #1e304a;border-radius:10px;background:#0d1828;padding:9px 10px;margin-bottom:8px;cursor:pointer;transition:.15s}.oa-tl-card:hover{border-color:#3b366d;background:#101b2e}.oa-tl-main{display:flex;justify-content:space-between;gap:12px}.oa-tl-title{font-size:8px;color:#dce5ef;font-weight:900}.oa-tl-sub{font-size:7px;color:#5a6f89;margin-top:3px}.oa-tl-changes{margin-top:7px;padding-top:7px;border-top:1px solid #192a41;font-size:7px;color:#71849c}
    .oa-drawer{position:fixed;inset:0;z-index:1400;background:rgba(2,7,14,.76);backdrop-filter:blur(5px);display:flex;justify-content:flex-start}.oa-drawer-panel{width:min(850px,96vw);height:100%;background:#081320;border-right:1px solid #2a3d5a;box-shadow:25px 0 85px rgba(0,0,0,.55);overflow:auto}.oa-drawer-head{position:sticky;top:0;z-index:4;display:flex;justify-content:space-between;align-items:center;padding:13px 15px;background:rgba(12,23,39,.96);backdrop-filter:blur(12px);border-bottom:1px solid #21334e}.oa-drawer-kicker{font-size:7px;color:#765fda;letter-spacing:1.5px;font-weight:950}.oa-drawer-title{font-size:14px;font-weight:950;margin-top:3px}.oa-close{width:34px;height:34px;border-radius:9px;border:1px solid #2b3e5c;background:#101c2d;color:#8394aa;display:grid;place-items:center;cursor:pointer}.oa-close svg{width:15px}.oa-drawer-body{padding:13px}.oa-meta-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:10px}.oa-meta{border:1px solid #1f314b;border-radius:10px;background:#0d1828;padding:9px}.oa-meta span{display:block;font-size:6px;color:#50657f}.oa-meta strong{display:block;font-size:8px;color:#d2dbe7;margin-top:4px;overflow-wrap:anywhere}.oa-drawer-section{border:1px solid #20314a;border-radius:11px;background:#0c1726;overflow:hidden;margin-top:9px}.oa-drawer-section-head{padding:9px 11px;border-bottom:1px solid #1a2b42;display:flex;justify-content:space-between;align-items:center}.oa-drawer-section-title{font-size:9px;font-weight:950}.oa-tag{font-size:6px;color:#7968dc;border:1px solid rgba(139,92,246,.18);background:rgba(139,92,246,.06);border-radius:6px;padding:4px 6px;font-weight:950}.oa-full-diff{padding:3px 11px}.oa-full-row{display:grid;grid-template-columns:150px 1fr 22px 1fr;gap:8px;align-items:start;padding:9px 0;border-bottom:1px solid #182941}.oa-full-row:last-child{border-bottom:0}.oa-full-field{font-size:7px;color:#778ba4;font-weight:900}.oa-full-old,.oa-full-new{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:7px;word-break:break-word;direction:ltr;text-align:right;border-radius:6px;padding:5px 6px}.oa-full-old{background:rgba(251,113,133,.045);color:#d87987}.oa-full-new{background:rgba(52,211,153,.045);color:#56d6ad}.oa-full-arrow{color:#415775;text-align:center;padding-top:5px}.oa-json{padding:10px 11px;background:#09121d;color:#6f829c;font:7px/1.8 ui-monospace,SFMono-Regular,Menlo,monospace;direction:ltr;text-align:left;white-space:pre-wrap;word-break:break-word;max-height:280px;overflow:auto}
    @media(max-width:1180px){.oa-kpis{grid-template-columns:repeat(3,1fr)}.oa-filter-top{grid-template-columns:1fr 1fr}.oa-meta-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:760px){.oa-head{flex-direction:column}.oa-head-actions{width:100%}.oa-kpis{grid-template-columns:1fr 1fr}.oa-filter-top{grid-template-columns:1fr}.oa-field{flex:1;min-width:130px}.oa-full-row{grid-template-columns:1fr}.oa-full-arrow{transform:rotate(90deg)}.oa-meta-grid{grid-template-columns:1fr 1fr}.oa-tl-row{grid-template-columns:70px 18px minmax(0,1fr)}}
    @media print{.fi-sidebar,.fi-topbar,.oa-head-actions,.oa-filter-panel,.oa-details-btn,.oa-pager{display:none!important}.orion-audit{color:#111}.oa-panel,.oa-kpi{break-inside:avoid}}
</style>

<div class="orion-audit">
    <div class="oa-head">
        <div class="oa-title-wrap">
            <div class="oa-title-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 4.5 6v5.2c0 4.6 3.1 8.8 7.5 9.8 4.4-1 7.5-5.2 7.5-9.8V6L12 3Z"/><path d="M9 12.2 11 14l4-4.2"/></svg>
            </div>
            <div>
                <div class="oa-kicker">ORION CONTROL CENTER · AUDIT TRAIL</div>
                <h1 class="oa-title">سجل العمليات</h1>
                <div class="oa-subtitle">مركز رقابي لتتبع الإنشاء والتعديل والحذف وتغييرات السجلات مع تفاصيل المستخدم والوقت وعنوان IP.</div>
            </div>
        </div>

        <div class="oa-head-actions">
            <div class="oa-live"><span class="oa-live-dot"></span> Audit Trail Active</div>
            <button type="button" wire:click="$refresh" class="oa-action">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6v5h-5"/><path d="M19 11a7.5 7.5 0 1 0 1 5"/></svg>
                تحديث
            </button>
            <button type="button" onclick="window.print()" class="oa-action">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
                طباعة
            </button>
            <button type="button" wire:click="resetFilters" class="oa-action primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 5h18M7 12h10M10 19h4"/></svg>
                إعادة ضبط
            </button>
        </div>
    </div>

    <div class="oa-kpis">
        <div class="oa-kpi">
            <div class="oa-kpi-code">TOTAL LOGS</div>
            <div class="oa-kpi-label">السجلات المطابقة</div>
            <div class="oa-kpi-value oa-purple">{{ number_format($stats['total']) }}</div>
            <div class="oa-kpi-foot">وفق الفلاتر الحالية</div>
        </div>
        <div class="oa-kpi">
            <div class="oa-kpi-code">TODAY</div>
            <div class="oa-kpi-label">عمليات اليوم</div>
            <div class="oa-kpi-value oa-green">{{ number_format($stats['today']) }}</div>
            <div class="oa-kpi-foot">{{ now()->format('d/m/Y') }}</div>
        </div>
        <div class="oa-kpi">
            <div class="oa-kpi-code">UPDATES</div>
            <div class="oa-kpi-label">عمليات التعديل</div>
            <div class="oa-kpi-value oa-blue">{{ number_format($stats['updates']) }}</div>
            <div class="oa-kpi-foot">Updated records</div>
        </div>
        <div class="oa-kpi">
            <div class="oa-kpi-code">DELETES</div>
            <div class="oa-kpi-label">عمليات الحذف</div>
            <div class="oa-kpi-value oa-red">{{ number_format($stats['deletes']) }}</div>
            <div class="oa-kpi-foot">تحتاج متابعة رقابية</div>
        </div>
        <div class="oa-kpi">
            <div class="oa-kpi-code">TOP USER</div>
            <div class="oa-kpi-label">الأكثر نشاطًا</div>
            <div class="oa-kpi-value oa-amber" style="font-size:17px;direction:rtl;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $stats['top_user'] }}</div>
            <div class="oa-kpi-foot">{{ number_format($stats['top_user_count']) }} عملية</div>
        </div>
    </div>

    <div class="oa-filter-panel">
        <div class="oa-filter-top">
            <div class="oa-search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
                <input class="oa-input oa-search" type="text" wire:model.live.debounce.350ms="search" placeholder="ابحث بالمستخدم، السجل، النموذج، العملية أو IP...">
            </div>

            <select class="oa-select" wire:model.live="filterEvent">
                <option value="">كل العمليات</option>
                @foreach($eventOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select class="oa-select" wire:model.live="filterModel">
                <option value="">كل النماذج</option>
                @foreach($modelOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <div class="oa-segment">
                <button type="button" wire:click="setViewMode('table')" class="{{ $viewMode === 'table' ? 'active' : '' }}">جدول</button>
                <button type="button" wire:click="setViewMode('timeline')" class="{{ $viewMode === 'timeline' ? 'active' : '' }}">Timeline</button>
            </div>
        </div>

        <div class="oa-filter-bottom">
            <div class="oa-field">
                <label>من تاريخ</label>
                <input class="oa-input" type="date" wire:model.live="dateFrom">
            </div>
            <div class="oa-field">
                <label>إلى تاريخ</label>
                <input class="oa-input" type="date" wire:model.live="dateTo">
            </div>
            <div class="oa-field" style="min-width:110px">
                <label>عدد السجلات</label>
                <select class="oa-select" wire:model.live="perPage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="oa-filter-spacer"></div>
            @if($search || $filterEvent || $filterModel || $dateFrom || $dateTo)
                <button type="button" wire:click="resetFilters" class="oa-reset">× مسح الفلاتر</button>
            @endif
        </div>
    </div>

    <div class="oa-panel">
        <div class="oa-panel-head">
            <div>
                <div class="oa-panel-title">{{ $viewMode === 'table' ? 'سجل النشاط التفصيلي' : 'المخطط الزمني للعمليات' }}</div>
                <div class="oa-panel-sub">كل حدث قابل للفتح لمراجعة Before / After والبيانات الخام</div>
            </div>
            <div class="oa-panel-count">{{ number_format($logs->total()) }} LOGS</div>
        </div>

        @if($logs->isEmpty())
            <div class="oa-empty">
                <div class="oa-empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3 4.5 6v5.2c0 4.6 3.1 8.8 7.5 9.8 4.4-1 7.5-5.2 7.5-9.8V6L12 3Z"/><path d="M9 12h6"/></svg>
                </div>
                <div class="oa-empty-title">لا توجد سجلات مطابقة</div>
                <div class="oa-empty-sub">جرّب تغيير الفلاتر أو نطاق التاريخ.</div>
            </div>
        @elseif($viewMode === 'timeline')
            <div class="oa-timeline">
                @foreach($logs as $log)
                    @php $changes = $this->changedFields($log); @endphp
                    <div class="oa-tl-row" wire:key="timeline-log-{{ $log->id }}">
                        <div class="oa-tl-time">
                            <div>{{ $log->created_at?->format('d/m/Y') }}</div>
                            <strong style="color:#9aacc0">{{ $log->created_at?->format('H:i:s') }}</strong>
                        </div>
                        <div class="oa-tl-line"><span class="oa-tl-dot"></span></div>
                        <div class="oa-tl-card" wire:click="openLog({{ $log->id }})">
                            <div class="oa-tl-main">
                                <div>
                                    <div class="oa-tl-title">{{ $log->user_name ?: 'System' }} · {{ $this->eventLabel($log->event) }} · {{ $log->model_label ?: $this->modelLabelFromType($log->auditable_type) }}</div>
                                    <div class="oa-tl-sub">{{ $log->auditable_label ?: ('#'.$log->id) }} · {{ $log->ip_address ?: 'IP غير مسجل' }}</div>
                                </div>
                                <span class="oa-badge {{ $this->eventClass($log->event) }}">{{ $this->eventLabel($log->event) }}</span>
                            </div>
                            @if(count($changes))
                                <div class="oa-tl-changes">{{ count($changes) }} حقل تم تغييره · اضغط لعرض التفاصيل الكاملة</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="oa-table-wrap">
                <table class="oa-table">
                    <thead>
                        <tr>
                            <th>التوقيت</th>
                            <th>المستخدم</th>
                            <th>العملية</th>
                            <th>النموذج</th>
                            <th>السجل</th>
                            <th>ملخص التغييرات</th>
                            <th>IP</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            @php
                                $changes = $this->changedFields($log);
                                $initial = mb_substr($log->user_name ?: 'S', 0, 1);
                            @endphp
                            <tr wire:key="audit-log-{{ $log->id }}">
                                <td class="oa-time">
                                    <div class="oa-date">{{ $log->created_at?->format('d/m/Y') }}</div>
                                    <div class="oa-clock">{{ $log->created_at?->format('H:i:s') }}</div>
                                    <div class="oa-ago">{{ $log->created_at?->diffForHumans() }}</div>
                                </td>
                                <td>
                                    <div class="oa-user">
                                        <div class="oa-avatar">{{ $initial }}</div>
                                        <div>
                                            <div class="oa-user-name">{{ $log->user_name ?: 'System' }}</div>
                                            <div class="oa-user-sub">{{ $log->user?->roles?->first()?->name ?? ($log->user_id ? 'User #'.$log->user_id : 'SYSTEM') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="oa-badge {{ $this->eventClass($log->event) }}">{{ $this->eventLabel($log->event) }}</span>
                                </td>
                                <td>
                                    <div class="oa-model">{{ $log->model_label ?: $this->modelLabelFromType($log->auditable_type) }}</div>
                                    <div class="oa-model-code">{{ class_basename($log->auditable_type ?: 'System') }}</div>
                                </td>
                                <td>
                                    <div class="oa-record">{{ $log->auditable_label ?: ('#'.$log->id) }}</div>
                                </td>
                                <td class="oa-changes">
                                    @forelse(array_slice($changes, 0, 3) as $change)
                                        <div class="oa-change">
                                            <div class="oa-change-label" title="{{ $change['field'] }}">{{ $change['label'] }}</div>
                                            <div class="oa-diff">
                                                <span class="oa-old">{{ $this->formatAuditValue($change['old'], 24) }}</span>
                                                <span class="oa-arrow">→</span>
                                                <span class="oa-new">{{ $this->formatAuditValue($change['new'], 24) }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <span style="color:#3f536d;font-size:7px">لا توجد قيم Before / After</span>
                                    @endforelse

                                    @if(count($changes) > 3)
                                        <div class="oa-more">+ {{ count($changes) - 3 }} تغييرات أخرى</div>
                                    @endif
                                </td>
                                <td><span class="oa-ip">{{ $log->ip_address ?: '—' }}</span></td>
                                <td>
                                    <button type="button" class="oa-details-btn" wire:click="openLog({{ $log->id }})" title="تفاصيل العملية">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(!$logs->isEmpty())
            <div class="oa-pager">
                <div class="oa-pager-info">
                    عرض {{ $logs->firstItem() }}–{{ $logs->lastItem() }} من {{ number_format($logs->total()) }} سجل · صفحة {{ $logs->currentPage() }} من {{ $logs->lastPage() }}
                </div>
                <div class="oa-pager-actions">
                    <button type="button" class="oa-page-btn" wire:click="previousPage" @disabled($logs->onFirstPage())>السابق</button>
                    <button type="button" class="oa-page-btn" wire:click="nextPage" @disabled(!$logs->hasMorePages())>التالي</button>
                </div>
            </div>
        @endif
    </div>

    @if($selectedLog)
        @php $fullChanges = $this->changedFields($selectedLog); @endphp
        <div class="oa-drawer" wire:click.self="closeLog">
            <div class="oa-drawer-panel">
                <div class="oa-drawer-head">
                    <div>
                        <div class="oa-drawer-kicker">AUDIT LOG #{{ $selectedLog->id }}</div>
                        <div class="oa-drawer-title">{{ $this->eventLabel($selectedLog->event) }} · {{ $selectedLog->auditable_label ?: $this->modelLabelFromType($selectedLog->auditable_type) }}</div>
                    </div>
                    <button type="button" wire:click="closeLog" class="oa-close">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="oa-drawer-body">
                    <div class="oa-meta-grid">
                        <div class="oa-meta"><span>المستخدم</span><strong>{{ $selectedLog->user_name ?: 'System' }}</strong></div>
                        <div class="oa-meta"><span>العملية</span><strong>{{ $this->eventLabel($selectedLog->event) }}</strong></div>
                        <div class="oa-meta"><span>النموذج</span><strong>{{ $selectedLog->model_label ?: $this->modelLabelFromType($selectedLog->auditable_type) }}</strong></div>
                        <div class="oa-meta"><span>السجل</span><strong>{{ $selectedLog->auditable_label ?: '—' }}</strong></div>
                        <div class="oa-meta"><span>التاريخ والوقت</span><strong>{{ $selectedLog->created_at?->format('d/m/Y H:i:s') }}</strong></div>
                        <div class="oa-meta"><span>IP Address</span><strong style="direction:ltr;text-align:right">{{ $selectedLog->ip_address ?: '—' }}</strong></div>
                        <div class="oa-meta"><span>Record ID</span><strong>{{ $selectedLog->auditable_id ?? '—' }}</strong></div>
                        <div class="oa-meta"><span>Log ID</span><strong>{{ $selectedLog->id }}</strong></div>
                    </div>

                    <div class="oa-drawer-section">
                        <div class="oa-drawer-section-head">
                            <div class="oa-drawer-section-title">التغييرات · Before / After</div>
                            <span class="oa-tag">{{ count($fullChanges) }} FIELDS</span>
                        </div>
                        <div class="oa-full-diff">
                            @forelse($fullChanges as $change)
                                <div class="oa-full-row">
                                    <div class="oa-full-field">{{ $change['label'] }}<div style="font-size:6px;color:#425873;margin-top:2px">{{ $change['field'] }}</div></div>
                                    <div class="oa-full-old">{{ $this->formatAuditValue($change['old'], 500) }}</div>
                                    <div class="oa-full-arrow">→</div>
                                    <div class="oa-full-new">{{ $this->formatAuditValue($change['new'], 500) }}</div>
                                </div>
                            @empty
                                <div style="padding:20px;text-align:center;color:#52677f;font-size:8px">لا توجد قيم مسجلة للمقارنة.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="oa-drawer-section">
                        <div class="oa-drawer-section-head">
                            <div class="oa-drawer-section-title">Raw Audit Payload</div>
                            <span class="oa-tag">JSON</span>
                        </div>
                        <div class="oa-json">{{ json_encode([
                            'id' => $selectedLog->id,
                            'event' => $selectedLog->event,
                            'user_name' => $selectedLog->user_name,
                            'auditable_type' => $selectedLog->auditable_type,
                            'auditable_id' => $selectedLog->auditable_id ?? null,
                            'auditable_label' => $selectedLog->auditable_label,
                            'ip_address' => $selectedLog->ip_address,
                            'old_values' => $selectedLog->old_values,
                            'new_values' => $selectedLog->new_values,
                            'created_at' => optional($selectedLog->created_at)->toDateTimeString(),
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
</x-filament-panels::page>