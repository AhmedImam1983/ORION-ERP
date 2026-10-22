<x-filament-panels::page>
    @php
        $stats = $this->getInvoiceStats();
        $quick = request()->query('quick');
        $baseUrl = url()->current();
    @endphp

    <style>
        .orion-inv-list{direction:rtl;--iv-panel:#0d1728;--iv-panel2:#111c30;--iv-border:#263853;--iv-soft:#1a2940;--iv-text:#f8fafc;--iv-muted:#7b8ca5;--iv-purple:#8b5cf6;--iv-green:#34d399;--iv-amber:#fbbf24;--iv-red:#f87171;--iv-blue:#60a5fa;--iv-cyan:#22d3ee}.orion-inv-list *{box-sizing:border-box}
        .iv-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}.iv-kpi{position:relative;display:block;overflow:hidden;min-height:106px;padding:15px 17px;border:1px solid var(--iv-border);border-radius:15px;background:linear-gradient(145deg,var(--iv-panel2),#0b1525);text-decoration:none;color:inherit;box-shadow:0 12px 28px rgba(0,0,0,.11);transition:.15s ease}.iv-kpi:hover{transform:translateY(-2px);border-color:color-mix(in srgb,var(--accent) 48%,var(--iv-border))}.iv-kpi.active{border-color:color-mix(in srgb,var(--accent) 72%,var(--iv-border));background:linear-gradient(145deg,color-mix(in srgb,var(--accent) 9%,#111c30),#0b1525)}.iv-kpi::after{content:"";position:absolute;right:0;left:0;bottom:0;height:2px;background:linear-gradient(90deg,transparent,var(--accent),transparent);opacity:.8}.iv-kpi-head{display:flex;justify-content:space-between;align-items:center;gap:10px}.iv-kpi-label{color:#b7c4d5;font-size:10px;font-weight:900}.iv-kpi-code{direction:ltr;padding:3px 8px;border:1px solid #2b3c58;border-radius:999px;color:#72839b;font-size:8px;font-weight:950}.iv-kpi-value{margin-top:13px;color:var(--accent);font-size:21px;font-weight:950;line-height:1;font-variant-numeric:tabular-nums}.iv-kpi-note{margin-top:7px;color:#63758e;font-size:8px;line-height:1.6}
        .iv-workflow{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px;margin-bottom:12px;padding:8px;border:1px solid #22334d;border-radius:13px;background:rgba(13,23,40,.72)}.iv-step{position:relative;padding:10px 12px;border:1px solid #1f3049;border-radius:10px;background:#0e1829}.iv-step-no{position:absolute;left:9px;top:8px;direction:ltr;color:#7259bb;font-size:7px;font-weight:950}.iv-step-title{color:#dce5f1;font-size:9px;font-weight:900}.iv-step-note{margin-top:4px;color:#60728b;font-size:7px;line-height:1.55}
        .iv-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;padding:8px 10px;border:1px solid #22334c;border-radius:12px;background:rgba(13,23,40,.72)}.iv-filters{display:flex;gap:6px;flex-wrap:wrap}.iv-chip{height:31px;display:inline-flex;align-items:center;justify-content:center;padding:0 11px;border:1px solid #2b3b55;border-radius:9px;background:#0d1728;color:#9fb0c5;text-decoration:none;font-size:9px;font-weight:900}.iv-chip:hover{border-color:var(--iv-purple);color:#fff}.iv-chip.active{border-color:var(--iv-purple);background:rgba(139,92,246,.12);color:#d8ccff}.iv-toolbar-note{color:#62748c;font-size:8px}
        .iv-alert{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px;padding:10px 12px;border:1px solid rgba(248,113,113,.18);border-radius:11px;background:rgba(248,113,113,.045);color:#b68c92;font-size:9px}.iv-alert strong{color:#ff9ba5}.iv-alert a{color:#ffb3bb;text-decoration:none;font-weight:900}
        .iv-shell{overflow:hidden;border:1px solid #22334c;border-radius:16px;background:linear-gradient(145deg,#0c1627,#091321);box-shadow:0 16px 38px rgba(0,0,0,.15)}.iv-shell-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 15px;border-bottom:1px solid #1e2d45;background:#0f192b}.iv-shell-title{color:#f8fafc;font-size:15px;font-weight:950}.iv-shell-sub{margin-top:3px;color:#677991;font-size:8px}.iv-status{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border:1px solid rgba(52,211,153,.18);border-radius:999px;background:rgba(52,211,153,.055);color:#6ee7b7;font-size:8px;font-weight:900}.iv-status::before{content:"";width:5px;height:5px;border-radius:50%;background:var(--iv-green)}.iv-shell .fi-ta{border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important}.iv-shell .fi-ta-header,.iv-shell .fi-ta-header-ctn{background:#0d1728!important;border-color:#1e2d45!important}.iv-shell .fi-ta-row{transition:background-color .15s ease}.iv-shell .fi-ta-row:hover{background:rgba(139,92,246,.04)!important}.iv-shell .fi-ta-cell,.iv-shell .fi-ta-header-cell{border-color:#1a2940!important}.iv-shell .fi-ta-header-cell-label{color:#8292aa!important;font-size:9px!important;font-weight:900!important}.iv-shell .fi-ta-text-item-label{font-size:10px}.iv-shell .fi-icon-btn{border:1px solid #2b3b55!important;border-radius:8px!important;background:#0d1728!important}.iv-shell .fi-icon-btn:hover{border-color:#8b5cf6!important;background:rgba(139,92,246,.08)!important}
        @media(max-width:1100px){.iv-kpis{grid-template-columns:repeat(2,1fr)}.iv-workflow{grid-template-columns:repeat(3,1fr)}}@media(max-width:720px){.iv-kpis,.iv-workflow{grid-template-columns:1fr}.iv-toolbar,.iv-shell-head,.iv-alert{align-items:flex-start;flex-direction:column}.iv-toolbar-note{display:none}}
    </style>

    <div class="orion-inv-list">
        <div class="iv-kpis">
            <div class="iv-kpi" style="--accent:var(--iv-blue);cursor:default"><div class="iv-kpi-head"><span class="iv-kpi-label">إجمالي قيمة الفواتير</span><span class="iv-kpi-code">SAR</span></div><div class="iv-kpi-value">{{ number_format($stats['value'], 2) }} ر.س</div><div class="iv-kpi-note">إجمالي الفواتير غير الملغاة</div></div>
            <a href="{{ $baseUrl }}?quick=credit" class="iv-kpi {{ $quick === 'credit' ? 'active' : '' }}" style="--accent:var(--iv-red)"><div class="iv-kpi-head"><span class="iv-kpi-label">إجمالي المتبقي</span><span class="iv-kpi-code">OPEN</span></div><div class="iv-kpi-value">{{ number_format($stats['outstanding'], 2) }} ر.س</div><div class="iv-kpi-note">أرصدة لم يتم سدادها بالكامل</div></a>
            <a href="{{ $baseUrl }}?quick=paid" class="iv-kpi {{ $quick === 'paid' ? 'active' : '' }}" style="--accent:var(--iv-green)"><div class="iv-kpi-head"><span class="iv-kpi-label">مدفوعة بالكامل</span><span class="iv-kpi-code">PAID</span></div><div class="iv-kpi-value">{{ number_format($stats['paid']) }}</div><div class="iv-kpi-note">فاتورة رصيدها المتبقي صفر</div></a>
            <a href="{{ $baseUrl }}?quick=credit" class="iv-kpi {{ $quick === 'credit' ? 'active' : '' }}" style="--accent:var(--iv-amber)"><div class="iv-kpi-head"><span class="iv-kpi-label">فواتير آجلة مفتوحة</span><span class="iv-kpi-code">CREDIT</span></div><div class="iv-kpi-value">{{ number_format($stats['credit_open']) }}</div><div class="iv-kpi-note">آجل أو تقسيط وما زال عليها رصيد</div></a>
        </div>

        <div class="iv-workflow">
            <div class="iv-step"><span class="iv-step-no">01</span><div class="iv-step-title">إنشاء الفاتورة</div><div class="iv-step-note">تحديد النوع والطرف والأصناف والأسعار</div></div>
            <div class="iv-step"><span class="iv-step-no">02</span><div class="iv-step-title">مراجعة الضريبة</div><div class="iv-step-note">الخصم والضريبة والإجمالي النهائي</div></div>
            <div class="iv-step"><span class="iv-step-no">03</span><div class="iv-step-title">التأكيد والترحيل</div><div class="iv-step-note">إنشاء الأثر المحاسبي عند تأكيد الفاتورة</div></div>
            <div class="iv-step"><span class="iv-step-no">04</span><div class="iv-step-title">التحصيل / السداد</div><div class="iv-step-note">متابعة المدفوع والمتبقي والاستحقاق</div></div>
            <div class="iv-step"><span class="iv-step-no">05</span><div class="iv-step-title">الإغلاق والمستندات</div><div class="iv-step-note">طباعة، زاتكا، واتساب أو مرتجع حسب الحالة</div></div>
        </div>

        @if($stats['overdue'] > 0)
            <div class="iv-alert"><div><strong>{{ $stats['overdue'] }} فاتورة متأخرة عن تاريخ الاستحقاق.</strong> ما زال عليها رصيد يحتاج متابعة.</div><a href="{{ $baseUrl }}?quick=overdue">عرض المتأخر</a></div>
        @endif

        <div class="iv-toolbar"><div class="iv-filters">
            <a href="{{ $baseUrl }}" class="iv-chip {{ !$quick ? 'active' : '' }}">الكل</a>
            <a href="{{ $baseUrl }}?quick=sale" class="iv-chip {{ $quick === 'sale' ? 'active' : '' }}">مبيعات</a>
            <a href="{{ $baseUrl }}?quick=purchase" class="iv-chip {{ $quick === 'purchase' ? 'active' : '' }}">مشتريات</a>
            <a href="{{ $baseUrl }}?quick=sale_return" class="iv-chip {{ $quick === 'sale_return' ? 'active' : '' }}">مرتجع بيع</a>
            <a href="{{ $baseUrl }}?quick=purchase_return" class="iv-chip {{ $quick === 'purchase_return' ? 'active' : '' }}">مرتجع شراء</a>
            <a href="{{ $baseUrl }}?quick=credit" class="iv-chip {{ $quick === 'credit' ? 'active' : '' }}">آجل مفتوح</a>
            <a href="{{ $baseUrl }}?quick=paid" class="iv-chip {{ $quick === 'paid' ? 'active' : '' }}">مدفوعة</a>
            <a href="{{ $baseUrl }}?quick=overdue" class="iv-chip {{ $quick === 'overdue' ? 'active' : '' }}">متأخرة ({{ $stats['overdue'] }})</a>
        </div><div class="iv-toolbar-note">يمكنك الجمع بين البحث وفلاتر Filament والفلاتر السريعة</div></div>

        <div class="iv-shell"><div class="iv-shell-head"><div><div class="iv-shell-title">سجل الفواتير</div><div class="iv-shell-sub">بيع وشراء ومرتجعات مع السداد والاستحقاق والحالة في سجل موحد</div></div><span class="iv-status">المركز المالي جاهز</span></div>{{ $this->table }}</div>
    </div>
</x-filament-panels::page>
