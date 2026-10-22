<x-filament-panels::page>
    @php
        $stats = $this->getSupplierStats();
        $quick = request()->query('quick');
        $baseUrl = url()->current();
    @endphp

    <style>
        .orion-suppliers{direction:rtl;--os-panel:#0d1728;--os-panel2:#111c30;--os-border:#263853;--os-soft:#1a2940;--os-text:#f8fafc;--os-muted:#8091aa;--os-purple:#8b5cf6;--os-green:#34d399;--os-amber:#fbbf24;--os-red:#f87171;--os-blue:#60a5fa}.orion-suppliers *{box-sizing:border-box}
        .os-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}.os-kpi{position:relative;display:block;overflow:hidden;min-height:102px;padding:15px 17px;border:1px solid var(--os-border);border-radius:15px;background:linear-gradient(145deg,var(--os-panel2),#0b1525);text-decoration:none;color:inherit;box-shadow:0 12px 28px rgba(0,0,0,.11);transition:.15s ease}.os-kpi:hover{transform:translateY(-2px);border-color:color-mix(in srgb,var(--accent) 45%,var(--os-border))}.os-kpi.active{border-color:color-mix(in srgb,var(--accent) 70%,var(--os-border));background:linear-gradient(145deg,color-mix(in srgb,var(--accent) 9%,#111c30),#0b1525)}.os-kpi::after{content:"";position:absolute;right:0;left:0;bottom:0;height:2px;background:linear-gradient(90deg,transparent,var(--accent),transparent);opacity:.8}.os-kpi-head{display:flex;align-items:center;justify-content:space-between;gap:10px}.os-kpi-label{color:#b8c4d5;font-size:11px;font-weight:850}.os-kpi-code{direction:ltr;padding:3px 8px;border:1px solid #2c3d59;border-radius:999px;color:#72839c;font-size:8px;font-weight:900}.os-kpi-value{margin-top:12px;color:var(--accent);font-size:22px;font-weight:950;line-height:1;font-variant-numeric:tabular-nums}.os-kpi-note{margin-top:7px;color:#65768e;font-size:9px}
        .os-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;padding:8px 10px;border:1px solid #22334c;border-radius:12px;background:rgba(13,23,40,.72)}.os-filters{display:flex;gap:6px;flex-wrap:wrap}.os-chip{display:inline-flex;align-items:center;justify-content:center;height:31px;padding:0 11px;border:1px solid #2b3b55;border-radius:9px;background:#0d1728;color:#9fb0c5;text-decoration:none;font-size:9px;font-weight:900;transition:.15s}.os-chip:hover{border-color:var(--os-purple);color:#fff}.os-chip.active{border-color:#8b5cf6;background:rgba(139,92,246,.12);color:#d8ccff}.os-note{color:#65768e;font-size:9px}
        .os-shell{overflow:hidden;border:1px solid #22334c;border-radius:16px;background:linear-gradient(145deg,#0c1627,#091321);box-shadow:0 16px 38px rgba(0,0,0,.15)}.os-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 15px;border-bottom:1px solid #1e2d45;background:#0f192b}.os-title{color:#f8fafc;font-size:15px;font-weight:950}.os-sub{margin-top:3px;color:#677991;font-size:9px}.os-status{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border:1px solid rgba(52,211,153,.18);border-radius:999px;background:rgba(52,211,153,.055);color:#6ee7b7;font-size:9px;font-weight:900}.os-status::before{content:"";width:5px;height:5px;border-radius:50%;background:var(--os-green)}
        .orion-suppliers .fi-ta{border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important}.orion-suppliers .fi-ta-header,.orion-suppliers .fi-ta-header-ctn{background:#0d1728!important;border-color:#1e2d45!important}.orion-suppliers .fi-ta-row{transition:background-color .15s ease}.orion-suppliers .fi-ta-row:hover{background:rgba(139,92,246,.04)!important}.orion-suppliers .fi-ta-cell,.orion-suppliers .fi-ta-header-cell{border-color:#1a2940!important}.orion-suppliers .fi-ta-header-cell-label{color:#8292aa!important;font-size:9px!important;font-weight:900!important}.orion-suppliers .fi-ta-text-item-label{font-size:10px}.orion-suppliers .fi-ta-actions{gap:4px!important}.orion-suppliers .fi-icon-btn{border:1px solid #2b3b55!important;border-radius:8px!important;background:#0d1728!important}.orion-suppliers .fi-icon-btn:hover{border-color:#8b5cf6!important;background:rgba(139,92,246,.08)!important}
        .os-alerts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:10px}.os-alert{padding:9px 12px;border:1px solid rgba(251,191,36,.16);border-radius:11px;background:rgba(251,191,36,.045);color:#a89b78;font-size:9px}.os-alert strong{color:#f6d988}
        @media(max-width:1100px){.os-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:720px){.os-kpis,.os-alerts{grid-template-columns:1fr}.os-toolbar,.os-head{align-items:flex-start;flex-direction:column}.os-note{display:none}}
    </style>

    <div class="orion-suppliers">
        <div class="os-kpis">
            <a href="{{ $baseUrl }}" class="os-kpi {{ !$quick ? 'active' : '' }}" style="--accent:var(--os-blue)">
                <div class="os-kpi-head"><span class="os-kpi-label">إجمالي الموردين</span><span class="os-kpi-code">TOTAL</span></div>
                <div class="os-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="os-kpi-note">كل الموردين المسجلين في النظام</div>
            </a>
            <a href="{{ $baseUrl }}?quick=active" class="os-kpi {{ $quick === 'active' ? 'active' : '' }}" style="--accent:var(--os-green)">
                <div class="os-kpi-head"><span class="os-kpi-label">الموردين النشطين</span><span class="os-kpi-code">ACTIVE</span></div>
                <div class="os-kpi-value">{{ number_format($stats['active']) }}</div>
                <div class="os-kpi-note">متاحين للاستخدام في العمليات</div>
            </a>
            <a href="{{ $baseUrl }}?quick=credit" class="os-kpi {{ $quick === 'credit' ? 'active' : '' }}" style="--accent:var(--os-green)">
                <div class="os-kpi-head"><span class="os-kpi-label">أرصدة افتتاحية دائنة</span><span class="os-kpi-code">CR</span></div>
                <div class="os-kpi-value">{{ number_format($stats['credit_opening'],2) }} ر.س</div>
                <div class="os-kpi-note">أرصدة افتتاحية مستحقة للموردين</div>
            </a>
            <a href="{{ $baseUrl }}?quick=debit" class="os-kpi {{ $quick === 'debit' ? 'active' : '' }}" style="--accent:var(--os-red)">
                <div class="os-kpi-head"><span class="os-kpi-label">أرصدة افتتاحية مدينة</span><span class="os-kpi-code">DR</span></div>
                <div class="os-kpi-value">{{ number_format($stats['debit_opening'],2) }} ر.س</div>
                <div class="os-kpi-note">دفعات مقدمة أو أرصدة لصالح المنشأة</div>
            </a>
        </div>

        <div class="os-toolbar">
            <div class="os-filters">
                <a href="{{ $baseUrl }}" class="os-chip {{ !$quick ? 'active' : '' }}">الكل</a>
                <a href="{{ $baseUrl }}?quick=active" class="os-chip {{ $quick === 'active' ? 'active' : '' }}">نشط</a>
                <a href="{{ $baseUrl }}?quick=inactive" class="os-chip {{ $quick === 'inactive' ? 'active' : '' }}">غير نشط</a>
                <a href="{{ $baseUrl }}?quick=credit" class="os-chip {{ $quick === 'credit' ? 'active' : '' }}">رصيد دائن</a>
                <a href="{{ $baseUrl }}?quick=debit" class="os-chip {{ $quick === 'debit' ? 'active' : '' }}">رصيد مدين</a>
                <a href="{{ $baseUrl }}?quick=without_bank" class="os-chip {{ $quick === 'without_bank' ? 'active' : '' }}">بدون حساب بنكي</a>
            </div>
            <div class="os-note">استخدم الفلاتر السريعة ثم البحث داخل الجدول للوصول للمورد</div>
        </div>

        <div class="os-shell">
            <div class="os-head">
                <div><div class="os-title">قائمة الموردين</div><div class="os-sub">بيانات التواصل والرصيد الافتتاحي والحالة في شاشة واحدة</div></div>
                <span class="os-status">النظام جاهز</span>
            </div>
            {{ $this->table }}
        </div>

        @if($stats['without_bank'] > 0 || $stats['without_tax'] > 0)
            <div class="os-alerts">
                @if($stats['without_bank'] > 0)<div class="os-alert"><strong>{{ $stats['without_bank'] }} مورد</strong> بدون رقم حساب بنكي. استكمال البيانات يسهل تجهيز المدفوعات والتحويلات.</div>@endif
                @if($stats['without_tax'] > 0)<div class="os-alert"><strong>{{ $stats['without_tax'] }} مورد</strong> بدون رقم ضريبي مسجل. راجع البيانات قبل المعاملات الضريبية.</div>@endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
