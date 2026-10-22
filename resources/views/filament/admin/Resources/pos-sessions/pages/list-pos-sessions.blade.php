<x-filament-panels::page>
    @php
        $stats = $this->getSessionStats();
        $quick = request()->query('quick');
        $baseUrl = url()->current();
    @endphp

    <style>
        .ops{direction:rtl;--panel:#0d1728;--panel2:#111c30;--border:#263853;--text:#f8fafc;--muted:#8292aa;--purple:#8b5cf6;--green:#34d399;--amber:#fbbf24;--red:#f87171;--blue:#60a5fa}.ops *{box-sizing:border-box}
        .ops-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:13px}.ops-kpi{position:relative;display:block;overflow:hidden;min-height:104px;padding:15px 17px;border:1px solid var(--border);border-radius:15px;background:linear-gradient(145deg,var(--panel2),#0a1423);text-decoration:none;color:inherit;box-shadow:0 12px 28px rgba(0,0,0,.11);transition:.15s}.ops-kpi:hover{transform:translateY(-2px);border-color:color-mix(in srgb,var(--accent) 50%,var(--border))}.ops-kpi.active{border-color:color-mix(in srgb,var(--accent) 72%,var(--border));background:linear-gradient(145deg,color-mix(in srgb,var(--accent) 9%,#111c30),#0a1423)}.ops-kpi:after{content:"";position:absolute;right:0;left:0;bottom:0;height:2px;background:linear-gradient(90deg,transparent,var(--accent),transparent)}.ops-kpi-top{display:flex;justify-content:space-between;gap:10px}.ops-label{font-size:11px;font-weight:900;color:#bdc8d7}.ops-code{direction:ltr;font-size:8px;font-weight:900;color:#74869e;border:1px solid #2c3d59;border-radius:999px;padding:3px 8px}.ops-value{margin-top:13px;color:var(--accent);font-size:22px;font-weight:950;line-height:1;font-variant-numeric:tabular-nums}.ops-note{margin-top:7px;font-size:9px;color:#66788f}
        .ops-toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px;padding:8px 10px;border:1px solid #22334c;border-radius:12px;background:rgba(13,23,40,.72)}.ops-filters{display:flex;gap:6px;flex-wrap:wrap}.ops-chip{height:31px;display:inline-flex;align-items:center;padding:0 11px;border:1px solid #2b3b55;border-radius:9px;background:#0d1728;color:#9fb0c5;text-decoration:none;font-size:9px;font-weight:900}.ops-chip:hover{border-color:var(--purple);color:#fff}.ops-chip.active{border-color:var(--purple);background:rgba(139,92,246,.12);color:#d8ccff}.ops-helper{font-size:9px;color:#687991}
        .ops-shell{overflow:hidden;border:1px solid #22334c;border-radius:16px;background:linear-gradient(145deg,#0c1627,#091321);box-shadow:0 16px 38px rgba(0,0,0,.15)}.ops-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 15px;border-bottom:1px solid #1e2d45;background:#0f192b}.ops-title{font-size:15px;font-weight:950;color:#fff}.ops-sub{font-size:9px;color:#687991;margin-top:3px}.ops-live{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border:1px solid rgba(52,211,153,.18);border-radius:999px;background:rgba(52,211,153,.055);color:#6ee7b7;font-size:9px;font-weight:900}.ops-live:before{content:"";width:5px;height:5px;border-radius:50%;background:var(--green)}
        .ops .fi-ta{border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important}.ops .fi-ta-header,.ops .fi-ta-header-ctn{background:#0d1728!important;border-color:#1e2d45!important}.ops .fi-ta-row:hover{background:rgba(139,92,246,.04)!important}.ops .fi-ta-cell,.ops .fi-ta-header-cell{border-color:#1a2940!important}.ops .fi-ta-header-cell-label{color:#8292aa!important;font-size:9px!important;font-weight:900!important}.ops .fi-ta-text-item-label{font-size:10px}.ops .fi-icon-btn{border:1px solid #2b3b55!important;border-radius:8px!important;background:#0d1728!important}
        .ops-foot{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-top:10px}.ops-mini{padding:10px 12px;border:1px solid #22334c;border-radius:11px;background:rgba(13,23,40,.62)}.ops-mini strong{display:block;color:#dbe6f4;font-size:12px}.ops-mini span{display:block;margin-top:3px;color:#65778e;font-size:9px}
        @media(max-width:1100px){.ops-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:720px){.ops-kpis,.ops-foot{grid-template-columns:1fr}.ops-toolbar,.ops-head{align-items:flex-start;flex-direction:column}.ops-helper{display:none}}
    </style>

    <div class="ops">
        <div class="ops-kpis">
            <a href="{{ $baseUrl }}" class="ops-kpi {{ !$quick ? 'active' : '' }}" style="--accent:var(--blue)">
                <div class="ops-kpi-top"><span class="ops-label">إجمالي الجلسات</span><span class="ops-code">TOTAL</span></div>
                <div class="ops-value">{{ number_format($stats['total']) }}</div>
                <div class="ops-note">كل جلسات الكاشير المسجلة</div>
            </a>
            <a href="{{ $baseUrl }}?quick=open" class="ops-kpi {{ $quick === 'open' ? 'active' : '' }}" style="--accent:var(--green)">
                <div class="ops-kpi-top"><span class="ops-label">الجلسات المفتوحة</span><span class="ops-code">LIVE</span></div>
                <div class="ops-value">{{ number_format($stats['open']) }}</div>
                <div class="ops-note">جلسات تعمل الآن ولم يتم إغلاقها</div>
            </a>
            <a href="{{ $baseUrl }}?quick=today" class="ops-kpi {{ $quick === 'today' ? 'active' : '' }}" style="--accent:var(--purple)">
                <div class="ops-kpi-top"><span class="ops-label">مبيعات اليوم</span><span class="ops-code">SALES</span></div>
                <div class="ops-value">{{ number_format($stats['today_sales'],2) }} ر.س</div>
                <div class="ops-note">{{ number_format($stats['today_transactions']) }} عملية في {{ number_format($stats['today_sessions']) }} جلسة</div>
            </a>
            <a href="{{ $baseUrl }}?quick=refunds" class="ops-kpi {{ $quick === 'refunds' ? 'active' : '' }}" style="--accent:{{ $stats['today_refunds'] > 0 ? 'var(--red)' : 'var(--amber)' }}">
                <div class="ops-kpi-top"><span class="ops-label">مرتجعات اليوم</span><span class="ops-code">REFUND</span></div>
                <div class="ops-value">{{ number_format($stats['today_refunds'],2) }} ر.س</div>
                <div class="ops-note">راجع الجلسات التي تحتوي على عمليات مرتجع</div>
            </a>
        </div>

        <div class="ops-toolbar">
            <div class="ops-filters">
                <a href="{{ $baseUrl }}" class="ops-chip {{ !$quick ? 'active' : '' }}">الكل</a>
                <a href="{{ $baseUrl }}?quick=open" class="ops-chip {{ $quick === 'open' ? 'active' : '' }}">مفتوحة</a>
                <a href="{{ $baseUrl }}?quick=closed" class="ops-chip {{ $quick === 'closed' ? 'active' : '' }}">مغلقة</a>
                <a href="{{ $baseUrl }}?quick=today" class="ops-chip {{ $quick === 'today' ? 'active' : '' }}">اليوم</a>
                <a href="{{ $baseUrl }}?quick=refunds" class="ops-chip {{ $quick === 'refunds' ? 'active' : '' }}">بها مرتجعات</a>
            </div>
            <div class="ops-helper">فتح الجلسة يتم من شاشة POS للحفاظ على تسلسل التشغيل الصحيح</div>
        </div>

        <div class="ops-shell">
            <div class="ops-head">
                <div><div class="ops-title">سجل جلسات الكاشير</div><div class="ops-sub">الجلسة، الكاشير، الفرع، المبيعات والمرتجعات في شاشة واحدة</div></div>
                <span class="ops-live">تشغيل مباشر</span>
            </div>
            {{ $this->table }}
        </div>

        <div class="ops-foot">
            <div class="ops-mini"><strong>لا إنشاء يدوي للجلسة</strong><span>زر الجلسة الجديدة تم استبداله بفتح شاشة POS.</span></div>
            <div class="ops-mini"><strong>سجل غير قابل للتعديل</strong><span>الجلسات هنا للمراجعة والتدقيق، وليس لتغيير أرقام التشغيل.</span></div>
            <div class="ops-mini"><strong>تفاصيل كل جلسة</strong><span>افتح الجلسة لمراجعة توزيع الدفع وجميع العمليات المرتبطة بها.</span></div>
        </div>
    </div>
</x-filament-panels::page>
