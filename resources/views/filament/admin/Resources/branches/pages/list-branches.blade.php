<x-filament-panels::page>
    @php
        $stats = $this->getBranchStats();
        $resource = \App\Filament\Admin\Resources\Branches\BranchResource::class;
    @endphp

    <style>
        .fi-header{display:none!important}
        .orion-br-list,.orion-br-list *{box-sizing:border-box;font-family:'Cairo',sans-serif}
        .orion-br-list{--ob-panel:#0c1728;--ob-border:#21324d;--ob-border-soft:#17263c;--ob-text:#f4f7fb;--ob-muted:#7c8da7;--ob-purple:#8b5cf6;--ob-green:#34d399;--ob-blue:#60a5fa;--ob-amber:#fbbf24;direction:rtl;width:100%;color:var(--ob-text);padding-bottom:24px}

        .ob-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:18px}.ob-breadcrumb{display:flex;align-items:center;gap:7px;color:#60728e;font-size:10px;font-weight:800;margin-bottom:8px}.ob-breadcrumb strong{color:#9c88f5}.ob-title-row{display:flex;align-items:center;gap:12px}.ob-title-icon{width:47px;height:47px;border-radius:13px;display:grid;place-items:center;color:#c4b5fd;border:1px solid rgba(139,92,246,.38);background:linear-gradient(145deg,rgba(124,58,237,.27),rgba(76,29,149,.12));box-shadow:0 12px 30px rgba(76,29,149,.15)}.ob-title-icon svg{width:22px;height:22px}.ob-title{margin:0;color:#f5f7fb;font-size:27px;line-height:1.1;font-weight:950}.ob-subtitle{margin-top:5px;color:var(--ob-muted);font-size:10px;font-weight:650}.ob-create{min-height:40px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 15px;border-radius:10px;text-decoration:none;color:#fff;font-size:10px;font-weight:950;border:1px solid rgba(167,139,250,.45);background:linear-gradient(135deg,#7c3aed,#8b5cf6);box-shadow:0 12px 24px rgba(91,33,182,.2);transition:.18s ease}.ob-create:hover{transform:translateY(-1px);filter:brightness(1.06)}.ob-create svg{width:15px;height:15px}

        .ob-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:13px}.ob-kpi{position:relative;overflow:hidden;min-height:108px;padding:14px 15px;border-radius:13px;border:1px solid var(--ob-border);background:linear-gradient(145deg,#0e192b,#0a1423)}.ob-kpi::after{content:'';position:absolute;width:90px;height:90px;left:-28px;bottom:-40px;border-radius:50%;opacity:.12;background:currentColor}.ob-kpi.total{color:#a78bfa}.ob-kpi.active{color:#34d399}.ob-kpi.invoices{color:#60a5fa}.ob-kpi.main{color:#fbbf24}.ob-kpi-top{display:flex;align-items:center;justify-content:space-between;gap:8px}.ob-kpi-label{color:#8b9ab0;font-size:9px;font-weight:850}.ob-kpi-icon{width:29px;height:29px;border-radius:8px;display:grid;place-items:center;color:currentColor;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.025)}.ob-kpi-icon svg{width:14px;height:14px}.ob-kpi-value{margin-top:7px;color:currentColor;font-size:25px;line-height:1;font-weight:950;direction:ltr;text-align:right}.ob-kpi-value.name{font-size:15px;line-height:1.4;direction:rtl;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ob-kpi-note{margin-top:7px;color:#5f708a;font-size:8px;font-weight:700}

        .ob-table-panel{overflow:hidden;border-radius:14px;border:1px solid var(--ob-border);background:linear-gradient(145deg,#0b1524,#091321);box-shadow:0 18px 50px rgba(0,0,0,.12)}.ob-table-panel-head{min-height:56px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 15px;border-bottom:1px solid var(--ob-border-soft)}.ob-panel-title{color:#e7edf7;font-size:11px;font-weight:950}.ob-panel-sub{margin-top:3px;color:#61738e;font-size:8px;font-weight:650}.ob-count-chip{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:999px;border:1px solid rgba(139,92,246,.22);background:rgba(139,92,246,.07);color:#aa96f8;font-size:8px;font-weight:900}.ob-count-chip span{width:5px;height:5px;border-radius:50%;background:#8b5cf6;box-shadow:0 0 10px rgba(139,92,246,.7)}

        .orion-br-list .fi-ta-ctn,.orion-br-list .fi-section,.orion-br-list .fi-ta{border:0!important;border-radius:0!important;box-shadow:none!important;background:transparent!important}.orion-br-list .fi-ta-header{padding:11px 14px!important;background:rgba(255,255,255,.015)!important;border-bottom:1px solid #18283f!important}.orion-br-list .fi-ta-table thead{background:#0e192a!important}.orion-br-list .fi-ta-table thead th{color:#657892!important;font-size:9px!important;font-weight:900!important;border-bottom-color:#1b2b43!important}.orion-br-list .fi-ta-row{background:transparent!important;border-bottom:1px solid #15243a!important}.orion-br-list .fi-ta-row:hover{background:rgba(139,92,246,.035)!important}.orion-br-list .fi-ta-cell{color:#c9d3e1!important;font-size:9px!important}.orion-br-list .fi-input-wrp{border-color:#263a59!important;background:#0e1a2d!important;box-shadow:none!important}.orion-br-list .fi-input{color:#dce5ef!important;font-size:9px!important}.orion-br-list .fi-ta-pagination{border-top:1px solid #18283f!important;background:rgba(255,255,255,.01)!important}.orion-br-list .fi-badge{font-weight:850!important}

        @media(max-width:1000px){.ob-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:680px){.ob-head{align-items:stretch;flex-direction:column}.ob-create{width:100%}.ob-kpis{grid-template-columns:1fr}.ob-title{font-size:23px}}
    </style>

    <div class="orion-br-list">
        <div class="ob-head">
            <div>
                <div class="ob-breadcrumb"><span>الإعدادات</span><span>‹</span><strong>الفروع</strong></div>
                <div class="ob-title-row">
                    <div class="ob-title-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/></svg></div>
                    <div><h1 class="ob-title">الفروع</h1><div class="ob-subtitle">إدارة شبكة الفروع، المخازن الافتراضية، حالة التشغيل وحجم الفواتير لكل فرع.</div></div>
                </div>
            </div>
            @if($resource::canCreate())
                <a href="{{ $resource::getUrl('create') }}" class="ob-create"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>إضافة فرع جديد</a>
            @endif
        </div>

        <div class="ob-kpis">
            <div class="ob-kpi total"><div class="ob-kpi-top"><div class="ob-kpi-label">إجمالي الفروع</div><div class="ob-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7l7-4 7 4v14"/></svg></div></div><div class="ob-kpi-value">{{ number_format($stats['total']) }}</div><div class="ob-kpi-note">كل الفروع المسجلة داخل النظام</div></div>
            <div class="ob-kpi active"><div class="ob-kpi-top"><div class="ob-kpi-label">الفروع النشطة</div><div class="ob-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg></div></div><div class="ob-kpi-value">{{ number_format($stats['active']) }}</div><div class="ob-kpi-note">متاحة حاليًا للعمليات اليومية</div></div>
            <div class="ob-kpi invoices"><div class="ob-kpi-top"><div class="ob-kpi-label">إجمالي الفواتير</div><div class="ob-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l4 4v16H6z"/><path d="M14 2v5h5M9 12h6M9 16h6"/></svg></div></div><div class="ob-kpi-value">{{ number_format($stats['invoices']) }}</div><div class="ob-kpi-note">الفواتير المرتبطة بجميع الفروع</div></div>
            <div class="ob-kpi main"><div class="ob-kpi-top"><div class="ob-kpi-label">الفرع الرئيسي</div><div class="ob-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/></svg></div></div><div class="ob-kpi-value name">{{ $stats['main_name'] }}</div><div class="ob-kpi-note">الفرع المحدد كفرع رئيسي للنظام</div></div>
        </div>

        <div class="ob-table-panel">
            <div class="ob-table-panel-head"><div><div class="ob-panel-title">دليل الفروع</div><div class="ob-panel-sub">بحث وفلترة حسب الحالة، نوع الفرع أو المخزن الافتراضي.</div></div><div class="ob-count-chip"><span></span>{{ number_format($stats['total']) }} فرع</div></div>
            <div>{{ $this->table }}</div>
        </div>
    </div>
</x-filament-panels::page>
