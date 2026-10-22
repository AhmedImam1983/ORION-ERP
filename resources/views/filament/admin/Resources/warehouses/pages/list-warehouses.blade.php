<x-filament-panels::page>
    @php
        $stats = $this->getWarehouseStats();
        $resource = \App\Filament\Admin\Resources\Warehouses\WarehouseResource::class;
    @endphp

    <style>
        .fi-header { display:none !important; }

        .orion-wh-list,
        .orion-wh-list * { box-sizing:border-box; font-family:'Cairo',sans-serif; }

        .orion-wh-list {
            --ow-panel:#0c1728;
            --ow-panel-2:#101b2e;
            --ow-border:#21324d;
            --ow-border-soft:#17263c;
            --ow-text:#f4f7fb;
            --ow-muted:#7c8da7;
            --ow-purple:#8b5cf6;
            --ow-green:#34d399;
            --ow-blue:#60a5fa;
            --ow-amber:#fbbf24;
            direction:rtl;
            width:100%;
            color:var(--ow-text);
            padding-bottom:24px;
        }

        .ow-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:18px}
        .ow-breadcrumb{display:flex;align-items:center;gap:7px;color:#60728e;font-size:10px;font-weight:800;margin-bottom:8px}
        .ow-breadcrumb strong{color:#9c88f5}
        .ow-title-row{display:flex;align-items:center;gap:12px}
        .ow-title-icon{width:46px;height:46px;border-radius:13px;display:grid;place-items:center;color:#c0afff;border:1px solid rgba(139,92,246,.38);background:linear-gradient(145deg,rgba(124,58,237,.25),rgba(76,29,149,.12));box-shadow:0 12px 30px rgba(76,29,149,.15)}
        .ow-title-icon svg{width:22px;height:22px}
        .ow-title{margin:0;color:#f5f7fb;font-size:27px;line-height:1.1;font-weight:950}
        .ow-subtitle{margin-top:5px;color:var(--ow-muted);font-size:10px;font-weight:650}
        .ow-create{min-height:40px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 15px;border-radius:10px;text-decoration:none;color:#fff;font-size:10px;font-weight:950;border:1px solid rgba(167,139,250,.45);background:linear-gradient(135deg,#7c3aed,#8b5cf6);box-shadow:0 12px 24px rgba(91,33,182,.2);transition:.18s ease}
        .ow-create:hover{transform:translateY(-1px);filter:brightness(1.06)}
        .ow-create svg{width:15px;height:15px}

        .ow-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:13px}
        .ow-kpi{position:relative;overflow:hidden;min-height:108px;padding:14px 15px;border-radius:13px;border:1px solid var(--ow-border);background:linear-gradient(145deg,#0e192b,#0a1423)}
        .ow-kpi::after{content:'';position:absolute;width:90px;height:90px;left:-28px;bottom:-40px;border-radius:50%;opacity:.12;background:currentColor}
        .ow-kpi.total{color:#a78bfa}.ow-kpi.active{color:#34d399}.ow-kpi.products{color:#60a5fa}.ow-kpi.default{color:#fbbf24}
        .ow-kpi-top{display:flex;align-items:center;justify-content:space-between;gap:8px}
        .ow-kpi-label{color:#8b9ab0;font-size:9px;font-weight:850}
        .ow-kpi-icon{width:29px;height:29px;border-radius:8px;display:grid;place-items:center;color:currentColor;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.025)}
        .ow-kpi-icon svg{width:14px;height:14px}
        .ow-kpi-value{margin-top:7px;color:currentColor;font-size:25px;line-height:1;font-weight:950;direction:ltr;text-align:right}
        .ow-kpi-value.name{font-size:15px;line-height:1.4;direction:rtl;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .ow-kpi-note{margin-top:7px;color:#5f708a;font-size:8px;font-weight:700}

        .ow-table-panel{overflow:hidden;border-radius:14px;border:1px solid var(--ow-border);background:linear-gradient(145deg,#0b1524,#091321);box-shadow:0 18px 50px rgba(0,0,0,.12)}
        .ow-table-panel-head{min-height:56px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 15px;border-bottom:1px solid var(--ow-border-soft)}
        .ow-panel-title{color:#e7edf7;font-size:11px;font-weight:950}
        .ow-panel-sub{margin-top:3px;color:#61738e;font-size:8px;font-weight:650}
        .ow-count-chip{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:999px;border:1px solid rgba(139,92,246,.22);background:rgba(139,92,246,.07);color:#aa96f8;font-size:8px;font-weight:900}
        .ow-count-chip span{width:5px;height:5px;border-radius:50%;background:#8b5cf6;box-shadow:0 0 10px rgba(139,92,246,.7)}

        .orion-wh-list .fi-ta-ctn,.orion-wh-list .fi-section,.orion-wh-list .fi-ta{border:0!important;border-radius:0!important;box-shadow:none!important;background:transparent!important}
        .orion-wh-list .fi-ta-header{padding:11px 14px!important;background:rgba(255,255,255,.015)!important;border-bottom:1px solid #18283f!important}
        .orion-wh-list .fi-ta-table thead{background:#0e192a!important}
        .orion-wh-list .fi-ta-table thead th{color:#657892!important;font-size:9px!important;font-weight:900!important;border-bottom-color:#1b2b43!important}
        .orion-wh-list .fi-ta-row{background:transparent!important;border-bottom:1px solid #15243a!important}
        .orion-wh-list .fi-ta-row:hover{background:rgba(139,92,246,.035)!important}
        .orion-wh-list .fi-ta-cell{color:#c9d3e1!important;font-size:9px!important}
        .orion-wh-list .fi-input-wrp{border-color:#263a59!important;background:#0e1a2d!important;box-shadow:none!important}
        .orion-wh-list .fi-input{color:#dce5ef!important;font-size:9px!important}
        .orion-wh-list .fi-ta-pagination{border-top:1px solid #18283f!important;background:rgba(255,255,255,.01)!important}

        @media(max-width:1000px){.ow-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:680px){.ow-head{align-items:stretch;flex-direction:column}.ow-create{width:100%}.ow-kpis{grid-template-columns:1fr}.ow-title{font-size:23px}}
    </style>

    <div class="orion-wh-list">
        <div class="ow-head">
            <div>
                <div class="ow-breadcrumb"><span>المخازن</span><span>‹</span><strong>دليل المخازن</strong></div>
                <div class="ow-title-row">
                    <div class="ow-title-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.5 12 4l9 5.5V20H3z"/><path d="M7 20v-6h10v6M8 10h.01M12 10h.01M16 10h.01"/></svg>
                    </div>
                    <div>
                        <h1 class="ow-title">المخازن</h1>
                        <div class="ow-subtitle">إدارة مواقع التخزين، حالة كل مخزن، والمسؤولين والأصناف المرتبطة به</div>
                    </div>
                </div>
            </div>

            @if($resource::canCreate())
                <a href="{{ $resource::getUrl('create') }}" class="ow-create">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                    إضافة مخزن جديد
                </a>
            @endif
        </div>

        <div class="ow-kpis">
            <div class="ow-kpi total">
                <div class="ow-kpi-top"><div class="ow-kpi-label">إجمالي المخازن</div><div class="ow-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.5 12 4l9 5.5V20H3z"/><path d="M7 20v-6h10v6"/></svg></div></div>
                <div class="ow-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="ow-kpi-note">كل المخازن المسجلة في النظام</div>
            </div>

            <div class="ow-kpi active">
                <div class="ow-kpi-top"><div class="ow-kpi-label">المخازن النشطة</div><div class="ow-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg></div></div>
                <div class="ow-kpi-value">{{ number_format($stats['active']) }}</div>
                <div class="ow-kpi-note">متاحة حاليًا للعمليات المخزنية</div>
            </div>

            <div class="ow-kpi products">
                <div class="ow-kpi-top"><div class="ow-kpi-label">الأصناف داخل المخازن</div><div class="ow-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4 7 8-4 8 4-8 4z"/><path d="m4 7v10l8 4 8-4V7M12 11v10"/></svg></div></div>
                <div class="ow-kpi-value">{{ number_format($stats['products']) }}</div>
                <div class="ow-kpi-note">إجمالي الأصناف المرتبطة بالمخازن</div>
            </div>

            <div class="ow-kpi default">
                <div class="ow-kpi-top"><div class="ow-kpi-label">المخزن الافتراضي</div><div class="ow-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/></svg></div></div>
                <div class="ow-kpi-value name">{{ $stats['default_name'] }}</div>
                <div class="ow-kpi-note">المستخدم تلقائيًا عند عدم اختيار مخزن</div>
            </div>
        </div>

        <div class="ow-table-panel">
            <div class="ow-table-panel-head">
                <div><div class="ow-panel-title">دليل المخازن</div><div class="ow-panel-sub">بحث وتصفية وعرض تفاصيل كل مخزن من شاشة واحدة</div></div>
                <div class="ow-count-chip"><span></span>{{ number_format($stats['total']) }} مخزن</div>
            </div>
            <div>{{ $this->content }}</div>
        </div>
    </div>
</x-filament-panels::page>
