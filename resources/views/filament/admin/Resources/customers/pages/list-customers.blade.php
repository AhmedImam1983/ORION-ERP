<x-filament-panels::page>
    @php
        $stats = $this->getCustomerStats();
        $resource = \App\Filament\Admin\Resources\Customers\CustomerResource::class;
        $importUrl = \App\Filament\Admin\Pages\ImportContactsPage::getUrl();
    @endphp

    <style>
        .fi-header{display:none!important}
        .orion-customers-v2,.orion-customers-v2 *{box-sizing:border-box;font-family:'Cairo',sans-serif}
        .orion-customers-v2{--cu-panel:#0c1728;--cu-border:#21324d;--cu-soft:#17263c;--cu-text:#f4f7fb;--cu-muted:#7c8da7;--cu-purple:#8b5cf6;--cu-green:#34d399;--cu-red:#fb7185;--cu-amber:#fbbf24;--cu-blue:#60a5fa;width:100%;direction:rtl;color:var(--cu-text);padding-bottom:24px}
        .cu-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:18px}.cu-breadcrumb{display:flex;align-items:center;gap:7px;color:#60728e;font-size:10px;font-weight:800;margin-bottom:8px}.cu-breadcrumb strong{color:#9c88f5}.cu-title-row{display:flex;align-items:center;gap:12px}.cu-title-icon{width:47px;height:47px;border-radius:13px;display:grid;place-items:center;color:#c4b5fd;border:1px solid rgba(139,92,246,.38);background:linear-gradient(145deg,rgba(124,58,237,.27),rgba(76,29,149,.12));box-shadow:0 12px 30px rgba(76,29,149,.15)}.cu-title-icon svg{width:22px;height:22px}.cu-title{margin:0;color:#f5f7fb;font-size:27px;line-height:1.1;font-weight:950}.cu-subtitle{margin-top:5px;color:var(--cu-muted);font-size:10px;font-weight:650}
        .cu-actions{display:flex;align-items:center;gap:8px}.cu-btn{min-height:40px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 15px;border-radius:10px;text-decoration:none;font-size:10px;font-weight:950;transition:.18s ease}.cu-btn:hover{transform:translateY(-1px)}.cu-btn svg{width:15px;height:15px}.cu-btn.primary{color:#fff;border:1px solid rgba(167,139,250,.45);background:linear-gradient(135deg,#7c3aed,#8b5cf6);box-shadow:0 12px 24px rgba(91,33,182,.2)}.cu-btn.secondary{color:#76e0b8;border:1px solid rgba(52,211,153,.24);background:rgba(52,211,153,.07)}
        .cu-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:13px}.cu-kpi{position:relative;overflow:hidden;min-height:108px;padding:14px 15px;border-radius:13px;border:1px solid var(--cu-border);background:linear-gradient(145deg,#0e192b,#0a1423)}.cu-kpi:after{content:'';position:absolute;width:90px;height:90px;left:-28px;bottom:-40px;border-radius:50%;opacity:.12;background:currentColor}.cu-kpi.total{color:#a78bfa}.cu-kpi.active{color:#34d399}.cu-kpi.business{color:#60a5fa}.cu-kpi.credit{color:#fbbf24}.cu-kpi-top{display:flex;align-items:center;justify-content:space-between;gap:8px}.cu-kpi-label{color:#8b9ab0;font-size:9px;font-weight:850}.cu-kpi-icon{width:29px;height:29px;border-radius:8px;display:grid;place-items:center;color:currentColor;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.025)}.cu-kpi-icon svg{width:14px;height:14px}.cu-kpi-value{margin-top:7px;color:currentColor;font-size:25px;line-height:1;font-weight:950;direction:ltr;text-align:right}.cu-kpi-value.money{font-size:20px}.cu-kpi-note{margin-top:7px;color:#5f708a;font-size:8px;font-weight:700}
        .cu-table-panel{overflow:hidden;border-radius:14px;border:1px solid var(--cu-border);background:linear-gradient(145deg,#0b1524,#091321);box-shadow:0 18px 50px rgba(0,0,0,.12)}.cu-table-head{min-height:56px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 15px;border-bottom:1px solid var(--cu-soft)}.cu-panel-title{color:#e7edf7;font-size:11px;font-weight:950}.cu-panel-sub{margin-top:3px;color:#61738e;font-size:8px;font-weight:650}.cu-count{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:999px;border:1px solid rgba(139,92,246,.22);background:rgba(139,92,246,.07);color:#aa96f8;font-size:8px;font-weight:900}.cu-count span{width:5px;height:5px;border-radius:50%;background:#8b5cf6;box-shadow:0 0 10px rgba(139,92,246,.7)}
        .orion-customers-v2 .fi-ta-ctn,.orion-customers-v2 .fi-section,.orion-customers-v2 .fi-ta{border:0!important;border-radius:0!important;box-shadow:none!important;background:transparent!important}.orion-customers-v2 .fi-ta-header{padding:11px 14px!important;background:rgba(255,255,255,.012)!important;border-bottom:1px solid #18283f!important}.orion-customers-v2 .fi-input-wrp{border-color:#263a59!important;background:#0d192b!important;box-shadow:none!important}.orion-customers-v2 .fi-ta-table thead{background:#0a1422!important}.orion-customers-v2 .fi-ta-header-cell{color:#657895!important;font-size:9px!important;font-weight:900!important;border-bottom-color:#1a2a41!important}.orion-customers-v2 .fi-ta-row{background:#0c1728!important;border-bottom:1px solid #17263c!important}.orion-customers-v2 .fi-ta-row:hover{background:#0f1b2e!important}.orion-customers-v2 .fi-ta-cell{color:#c8d3e1!important;font-size:9px!important}.orion-customers-v2 .fi-ta-text{color:inherit!important}.orion-customers-v2 .fi-ta-footer{border-top:1px solid #192a42!important;background:#0a1422!important}.orion-customers-v2 .fi-pagination{color:#72849d!important}.orion-customers-v2 .fi-badge{font-weight:850!important}
        @media(max-width:900px){.cu-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.cu-head{align-items:flex-start;flex-direction:column}.cu-actions{width:100%}.cu-btn{flex:1}}@media(max-width:560px){.cu-kpis{grid-template-columns:1fr}.cu-title{font-size:23px}.cu-actions{flex-direction:column}.cu-btn{width:100%}}
    </style>

    <div class="orion-customers-v2">
        <div class="cu-head">
            <div>
                <div class="cu-breadcrumb"><span>CRM</span><span>‹</span><strong>العملاء</strong></div>
                <div class="cu-title-row">
                    <div class="cu-title-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg></div>
                    <div><h1 class="cu-title">العملاء</h1><div class="cu-subtitle">دليل موحد لبيانات العملاء، التصنيف، الاتصال والشروط الائتمانية داخل Orion CRM.</div></div>
                </div>
            </div>
            <div class="cu-actions">
                <a href="{{ $importUrl }}" class="cu-btn secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M5 21h14"/></svg>استيراد من Excel</a>
                @if($resource::canCreate())
                    <a href="{{ $resource::getUrl('create') }}" class="cu-btn primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>إضافة عميل جديد</a>
                @endif
            </div>
        </div>

        <div class="cu-kpis">
            <div class="cu-kpi total"><div class="cu-kpi-top"><div class="cu-kpi-label">إجمالي العملاء</div><div class="cu-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div></div><div class="cu-kpi-value">{{ number_format($stats['total']) }}</div><div class="cu-kpi-note">كل ملفات العملاء المسجلة</div></div>
            <div class="cu-kpi active"><div class="cu-kpi-top"><div class="cu-kpi-label">العملاء النشطون</div><div class="cu-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg></div></div><div class="cu-kpi-value">{{ number_format($stats['active']) }}</div><div class="cu-kpi-note">متاحون للاستخدام في العمليات</div></div>
            <div class="cu-kpi business"><div class="cu-kpi-top"><div class="cu-kpi-label">شركات وجهات</div><div class="cu-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V5h10v16M14 9h6v12M8 9h2M8 13h2M8 17h2M17 13h1M17 17h1"/></svg></div></div><div class="cu-kpi-value">{{ number_format($stats['business']) }}</div><div class="cu-kpi-note">شركة أو جهة حكومية</div></div>
            <div class="cu-kpi credit"><div class="cu-kpi-top"><div class="cu-kpi-label">إجمالي حدود الائتمان</div><div class="cu-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M7 10h6M7 14h3M17 11h.01"/></svg></div></div><div class="cu-kpi-value money">{{ number_format($stats['credit_limit'],2) }} <span style="font-size:10px">ر.س</span></div><div class="cu-kpi-note">إجمالي الحدود المسجلة للعملاء</div></div>
        </div>

        <div class="cu-table-panel">
            <div class="cu-table-head"><div><div class="cu-panel-title">دليل العملاء</div><div class="cu-panel-sub">ابحث بالاسم أو الكود أو الهاتف، ثم فلتر حسب النوع والحالة وطبيعة الرصيد.</div></div><div class="cu-count"><span></span>{{ number_format($stats['total']) }} عميل</div></div>
            <div>{{ $this->table }}</div>
        </div>
    </div>
</x-filament-panels::page>
